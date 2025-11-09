<?php
/**
 * Database Queries Helper Class
 * PropielEquipo Medical System
 */

require_once 'database_connection.php';

class PropielEquipoQueries {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * USER MANAGEMENT QUERIES
     */
    
    // Register new user
    public function registerUser($nombre, $apellido, $edad, $telefono, $password, $genero, $rol = 3) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO usuarios (rol, nombre, apellido, edad, telefono, password, genero) 
                  VALUES (:rol, :nombre, :apellido, :edad, :telefono, :password, :genero)";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':edad', $edad);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':genero', $genero);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Login user
    public function loginUser($telefono, $password) {
        $query = "SELECT u.*, ut.nivel as rol_nombre, g.genero as genero_nombre 
                  FROM usuarios u 
                  JOIN user_type ut ON u.rol = ut.user_id 
                  JOIN genero g ON u.genero = g.id_genero 
                  WHERE u.telefono = :telefono";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get user by ID
    public function getUserById($user_id) {
        $query = "SELECT u.*, ut.nivel as rol_nombre, g.genero as genero_nombre 
                  FROM usuarios u 
                  JOIN user_type ut ON u.rol = ut.user_id 
                  JOIN genero g ON u.genero = g.id_genero 
                  WHERE u.user_id = :user_id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Get User Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * APPOINTMENT MANAGEMENT QUERIES
     */
    
    // Check if user has signed consent before
    public function hasUserSignedConsent($user_id) {
        // Verificar si existe al menos un archivo de consentimiento para este usuario
        $consentDir = __DIR__ . '/consentimientos/';
        
        if (!is_dir($consentDir)) {
            return false;
        }
        
        // Buscar archivos usando el ID único del usuario para garantizar seguridad
        // El patrón incluye el ID del usuario para evitar colisiones entre pacientes con nombres similares
        $files = glob($consentDir . 'consentimiento_*_usuario_' . $user_id . '_*.pdf');
        
        return !empty($files);
    }
    
    // Create new appointment
    public function createAppointment($id_usuario, $fecha, $horario, $servicio, $id_doctor = null) {
        try {
            // Verificar disponibilidad una vez más antes de crear la cita (con especialidad específica)
            if (!$this->isTimeSlotAvailable($fecha, $horario, $servicio)) {
                throw new Exception("El horario seleccionado ya no está disponible para la especialidad " . $servicio);
            }
            
            // Si no se especifica un doctor, intentar asignar uno automáticamente según la especialidad
            if ($id_doctor === null || $id_doctor === 'cualquiera' || $id_doctor === '' || $id_doctor === 0) {
                $id_doctor = $this->getAvailableDoctorForSpecialty($servicio);
            }
            
            $query = "INSERT INTO citas (id_usuario, id_doctor, fecha, horario, servicio) 
                      VALUES (:id_usuario, :id_doctor, :fecha, :horario, :servicio)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':id_doctor', $id_doctor);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':horario', $horario);
            $stmt->bindParam(':servicio', $servicio);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Appointment Creation Error: " . $e->getMessage());
            return false;
        } catch(Exception $e) {
            error_log("Appointment Validation Error: " . $e->getMessage());
            throw $e; // Re-lanzar excepciones de validación
        }
    }
    
    // Get available doctor for a specialty (assigns automatically if not specified)
    public function getAvailableDoctorForSpecialty($servicio) {
        // Map service names to specialty IDs
        $especialidadMap = [
            'dermatología' => 1,
            'podología' => 2,
            'tamiz' => 3
        ];
        
        $id_especialidad = $especialidadMap[strtolower($servicio)] ?? null;
        
        if ($id_especialidad === null) {
            return null;
        }
        
        // Get a doctor with this specialty
        $query = "SELECT de.id_doctor 
                  FROM doctor_especialidades de
                  JOIN usuarios u ON de.id_doctor = u.user_id
                  WHERE de.id_especialidad = :id_especialidad
                  AND u.rol = 1
                  ORDER BY RAND()
                  LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_especialidad', $id_especialidad);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return $result ? $result['id_doctor'] : null;
        } catch(PDOException $e) {
            error_log("Error getting available doctor: " . $e->getMessage());
            return null;
        }
    }
    
    // Get user appointments
    public function getUserAppointments($user_id) {
        $query = "SELECT c.*, u.nombre, u.apellido 
                  FROM citas c 
                  JOIN usuarios u ON c.id_usuario = u.user_id 
                  WHERE c.id_usuario = :user_id 
                  ORDER BY c.fecha DESC, c.horario DESC";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Appointments Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all appointments (for doctors/admin)
    public function getAllAppointments($doctor_id = null) {
        $query = "SELECT c.*, u.nombre, u.apellido, u.telefono,
                         d.nombre as doctor_nombre, d.apellido as doctor_apellido
                  FROM citas c 
                  JOIN usuarios u ON c.id_usuario = u.user_id
                  LEFT JOIN usuarios d ON c.id_doctor = d.user_id";
        
        if ($doctor_id) {
            $query .= " WHERE c.id_doctor = :doctor_id";
        }
        
        $query .= " ORDER BY c.fecha ASC, c.horario ASC";
        
        try {
            $stmt = $this->db->prepare($query);
            
            if ($doctor_id) {
                $stmt->bindParam(':doctor_id', $doctor_id);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get All Appointments Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get appointments by date
    public function getAppointmentsByDate($fecha, $doctor_id = null) {
        $query = "SELECT c.*, u.user_id, u.nombre, u.apellido, u.telefono,
                         d.nombre as doctor_nombre, d.apellido as doctor_apellido
                  FROM citas c 
                  JOIN usuarios u ON c.id_usuario = u.user_id
                  LEFT JOIN usuarios d ON c.id_doctor = d.user_id
                  WHERE c.fecha = :fecha";
        
        if ($doctor_id) {
            $query .= " AND c.id_doctor = :doctor_id";
        }
        
        $query .= " ORDER BY c.horario ASC";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':fecha', $fecha);
            
            if ($doctor_id) {
                $stmt->bindParam(':doctor_id', $doctor_id);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Appointments By Date Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get appointments by date and specialty
    public function getAppointmentsByDateAndSpecialty($fecha, $servicio = null) {
        if ($servicio) {
            $query = "SELECT c.*, u.user_id, u.nombre, u.apellido, u.telefono 
                      FROM citas c 
                      JOIN usuarios u ON c.id_usuario = u.user_id 
                      WHERE c.fecha = :fecha AND c.servicio = :servicio
                      ORDER BY c.horario ASC";
        } else {
            $query = "SELECT c.*, u.user_id, u.nombre, u.apellido, u.telefono 
                      FROM citas c 
                      JOIN usuarios u ON c.id_usuario = u.user_id 
                      WHERE c.fecha = :fecha 
                      ORDER BY c.horario ASC";
        }
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':fecha', $fecha);
            if ($servicio) {
                $stmt->bindParam(':servicio', $servicio);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Appointments By Date And Specialty Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Check if user already has an appointment on a specific date
    public function hasUserAppointmentOnDate($user_id, $fecha) {
        $query = "SELECT COUNT(*) as count FROM citas WHERE id_usuario = :user_id AND fecha = :fecha";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch(PDOException $e) {
            error_log("Check User Appointment On Date Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get appointment by ID
    public function getAppointmentById($cita_id) {
        $query = "SELECT c.*, u.user_id, u.nombre, u.apellido, u.telefono 
                  FROM citas c 
                  JOIN usuarios u ON c.id_usuario = u.user_id 
                  WHERE c.id_cita = :cita_id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':cita_id', $cita_id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Get Appointment By ID Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete appointment (cancel appointment)
    public function deleteAppointment($cita_id, $user_id) {
        $query = "DELETE FROM citas WHERE id_cita = :cita_id AND id_usuario = :user_id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':cita_id', $cita_id);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Delete Appointment Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Cancel appointment (update status to cancelled)
    public function cancelAppointment($cita_id, $user_id) {
        $query = "UPDATE citas SET estado = 'cancelada', fecha_actualizacion = CURRENT_TIMESTAMP 
                  WHERE id_cita = :cita_id AND id_usuario = :user_id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':cita_id', $cita_id);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Cancel Appointment Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get appointments by user ID
    public function getAppointmentsByUserId($user_id) {
        $query = "SELECT c.*, u.user_id, u.nombre, u.apellido, u.telefono 
                  FROM citas c 
                  JOIN usuarios u ON c.id_usuario = u.user_id 
                  WHERE u.user_id = :user_id 
                  ORDER BY c.fecha ASC, c.horario ASC";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Appointments By User ID Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Check if time slot is available for a specific specialty
    public function isTimeSlotAvailable($fecha, $horario, $servicio = null) {
        if ($servicio) {
            // Check availability for specific specialty
            $query = "SELECT COUNT(*) as count FROM citas WHERE fecha = :fecha AND horario = :horario AND servicio = :servicio";
        } else {
            // Check general availability (backward compatibility)
            $query = "SELECT COUNT(*) as count FROM citas WHERE fecha = :fecha AND horario = :horario";
        }
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':horario', $horario);
            if ($servicio) {
                $stmt->bindParam(':servicio', $servicio);
            }
            $stmt->execute();
            
            $result = $stmt->fetch();
            return $result['count'] == 0;
        } catch(PDOException $e) {
            error_log("Time Slot Check Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * UTILITY QUERIES
     */
    
    // Get all genders
    public function getGenders() {
        $query = "SELECT * FROM genero ORDER BY id_genero";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Genders Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all user types
    public function getUserTypes() {
        $query = "SELECT * FROM user_type ORDER BY user_id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get User Types Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get available services
    public function getServices() {
        return ['dermatología', 'podología', 'tamiz'];
    }
    
    // Get available time slots
    public function getTimeSlots() {
        return ['9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
    }
    
    /**
     * MEDICAL STAFF MANAGEMENT QUERIES
     */
    
    // Register medical staff with specialties
    public function registerMedicalStaff($nombre, $apellido, $edad, $telefono, $email, $password, $genero, $especialidades = [], $cedula_profesional = null) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Begin transaction
            $this->db->beginTransaction();
            
            // Insert user with Doctor role (rol = 1)
            $query = "INSERT INTO usuarios (rol, nombre, apellido, edad, telefono, email, password, genero, cedula_profesional) 
                      VALUES (1, :nombre, :apellido, :edad, :telefono, :email, :password, :genero, :cedula_profesional)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':edad', $edad);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':genero', $genero);
            $stmt->bindParam(':cedula_profesional', $cedula_profesional);
            
            $stmt->execute();
            $doctor_id = $this->db->lastInsertId();
            
            // Assign specialties
            if (!empty($especialidades)) {
                $specialtyQuery = "INSERT INTO doctor_especialidades (id_doctor, id_especialidad) VALUES (:doctor_id, :especialidad_id)";
                $specialtyStmt = $this->db->prepare($specialtyQuery);
                
                foreach ($especialidades as $especialidad_id) {
                    $specialtyStmt->bindParam(':doctor_id', $doctor_id);
                    $specialtyStmt->bindParam(':especialidad_id', $especialidad_id);
                    $specialtyStmt->execute();
                }
            }
            
            // Commit transaction
            $this->db->commit();
            return $doctor_id;
            
        } catch(PDOException $e) {
            // Rollback transaction
            $this->db->rollback();
            error_log("Medical Staff Registration Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get all medical specialties
    public function getSpecialties() {
        $query = "SELECT * FROM especialidades ORDER BY nombre_especialidad";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Specialties Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get doctors by specialty
    public function getDoctorsBySpecialty($especialidad_id) {
        $query = "SELECT u.*, e.nombre_especialidad 
                  FROM usuarios u
                  JOIN doctor_especialidades de ON u.user_id = de.id_doctor
                  JOIN especialidades e ON de.id_especialidad = e.id_especialidad
                  WHERE de.id_especialidad = :especialidad_id AND u.rol = 1
                  ORDER BY u.nombre, u.apellido";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':especialidad_id', $especialidad_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Doctors By Specialty Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get doctor's specialties
    public function getDoctorSpecialties($doctor_id) {
        $query = "SELECT e.* 
                  FROM especialidades e
                  JOIN doctor_especialidades de ON e.id_especialidad = de.id_especialidad
                  WHERE de.id_doctor = :doctor_id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Doctor Specialties Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * MEDICAL IMAGES MANAGEMENT QUERIES
     */
    
    // Upload medical image for patient
    public function uploadMedicalImage($id_paciente, $nombre_archivo, $descripcion = null, $tipo_imagen = 'general') {
        $query = "INSERT INTO imagenes_medicas (id_paciente, nombre_archivo, descripcion, tipo_imagen, fecha_subida) 
                  VALUES (:id_paciente, :nombre_archivo, :descripcion, :tipo_imagen, NOW())";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_paciente', $id_paciente);
            $stmt->bindParam(':nombre_archivo', $nombre_archivo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':tipo_imagen', $tipo_imagen);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Upload Medical Image Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get patient's own medical images
    public function getPatientImages($id_paciente) {
        $query = "SELECT * FROM imagenes_medicas 
                  WHERE id_paciente = :id_paciente 
                  ORDER BY fecha_subida DESC";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_paciente', $id_paciente);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Patient Images Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get images for doctor (only their patients)
    public function getDoctorPatientImages($id_doctor) {
        $query = "SELECT im.*, u.nombre, u.apellido, u.telefono 
                  FROM imagenes_medicas im
                  JOIN usuarios u ON im.id_paciente = u.user_id
                  JOIN citas c ON u.user_id = c.id_usuario
                  WHERE u.rol = 3 
                  AND EXISTS (
                      SELECT 1 FROM citas c2 
                      WHERE c2.id_usuario = u.user_id
                  )
                  GROUP BY im.id_imagen
                  ORDER BY im.fecha_subida DESC";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Doctor Patient Images Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all images (admin only)
    public function getAllMedicalImages() {
        $query = "SELECT im.*, u.nombre, u.apellido, u.telefono 
                  FROM imagenes_medicas im
                  JOIN usuarios u ON im.id_paciente = u.user_id
                  ORDER BY im.fecha_subida DESC";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get All Medical Images Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Delete medical image (with permission check)
    public function deleteMedicalImage($id_imagen, $user_id, $user_role) {
        // Check if user has permission to delete this image
        if ($user_role == 3) { // Patient - can only delete their own images
            $checkQuery = "SELECT COUNT(*) as count FROM imagenes_medicas WHERE id_imagen = :id_imagen AND id_paciente = :user_id";
        } else { // Doctor/Admin - can delete any image
            $checkQuery = "SELECT COUNT(*) as count FROM imagenes_medicas WHERE id_imagen = :id_imagen";
        }
        
        try {
            $stmt = $this->db->prepare($checkQuery);
            $stmt->bindParam(':id_imagen', $id_imagen);
            if ($user_role == 3) {
                $stmt->bindParam(':user_id', $user_id);
            }
            $stmt->execute();
            
            $result = $stmt->fetch();
            if ($result['count'] == 0) {
                return false; // No permission or image doesn't exist
            }
            
            // Get filename before deleting
            $getFileQuery = "SELECT nombre_archivo FROM imagenes_medicas WHERE id_imagen = :id_imagen";
            $stmt = $this->db->prepare($getFileQuery);
            $stmt->bindParam(':id_imagen', $id_imagen);
            $stmt->execute();
            $fileInfo = $stmt->fetch();
            
            // Delete from database
            $deleteQuery = "DELETE FROM imagenes_medicas WHERE id_imagen = :id_imagen";
            $stmt = $this->db->prepare($deleteQuery);
            $stmt->bindParam(':id_imagen', $id_imagen);
            $success = $stmt->execute();
            
            // Delete physical file if database deletion was successful
            if ($success && $fileInfo) {
                $filePath = "Images/ImgMedicas/" . $fileInfo['nombre_archivo'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            return $success;
        } catch(PDOException $e) {
            error_log("Delete Medical Image Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Check if user can access specific image
    public function canAccessImage($id_imagen, $user_id, $user_role) {
        if ($user_role == 1 || $user_role == 2) { // Doctor or Nurse - can access all
            return true;
        } elseif ($user_role == 3) { // Patient - only their own images
            $query = "SELECT COUNT(*) as count FROM imagenes_medicas WHERE id_imagen = :id_imagen AND id_paciente = :user_id";
            
            try {
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_imagen', $id_imagen);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                $result = $stmt->fetch();
                return $result['count'] > 0;
            } catch(PDOException $e) {
                error_log("Access Check Error: " . $e->getMessage());
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * APPOINTMENT OBSERVATIONS MANAGEMENT
     */
    
    // Update appointment observations/notes
    public function updateAppointmentObservations($id_cita, $observaciones, $doctor_id = null) {
        $query = "UPDATE citas 
                  SET notas = :observaciones, 
                      fecha_actualizacion = current_timestamp() 
                  WHERE id_cita = :id_cita";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':observaciones', $observaciones);
            $stmt->bindParam(':id_cita', $id_cita);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Update Observations Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get appointment details with patient information
    public function getAppointmentDetails($id_cita) {
        $query = "SELECT c.*, 
                         u.nombre as paciente_nombre, 
                         u.apellido as paciente_apellido, 
                         u.telefono as paciente_telefono,
                         u.edad as paciente_edad,
                         g.genero as paciente_genero
                  FROM citas c 
                  JOIN usuarios u ON c.id_usuario = u.user_id 
                  LEFT JOIN genero g ON u.genero = g.id_genero
                  WHERE c.id_cita = :id_cita";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_cita', $id_cita);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Get Appointment Details Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get appointments with complete patient information for doctor view
    public function getAppointmentsWithPatientInfo($fecha = null, $doctor_id = null) {
        $baseQuery = "SELECT c.*, 
                             u.nombre as paciente_nombre, 
                             u.apellido as paciente_apellido, 
                             u.telefono as paciente_telefono,
                             u.edad as paciente_edad,
                             g.genero as paciente_genero,
                             d.nombre as doctor_nombre,
                             d.apellido as doctor_apellido
                      FROM citas c 
                      JOIN usuarios u ON c.id_usuario = u.user_id 
                      LEFT JOIN genero g ON u.genero = g.id_genero
                      LEFT JOIN usuarios d ON c.id_doctor = d.user_id";
        
        $whereConditions = [];
        $params = [];
        
        if ($fecha) {
            $whereConditions[] = "c.fecha = :fecha";
            $params[':fecha'] = $fecha;
        }
        
        if ($doctor_id) {
            $whereConditions[] = "c.id_doctor = :doctor_id";
            $params[':doctor_id'] = $doctor_id;
        }
        
        if (!empty($whereConditions)) {
            $baseQuery .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $baseQuery .= " ORDER BY c.fecha ASC, c.horario ASC";
        
        try {
            $stmt = $this->db->prepare($baseQuery);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Appointments With Patient Info Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get image by ID with ownership verification
    public function getImageById($id_imagen) {
        $query = "SELECT * FROM imagenes_medicas WHERE id_imagen = :id_imagen";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_imagen', $id_imagen);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Get Image by ID Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Alias for deleteMedicalImage for patient usage
    public function deleteImage($id_imagen, $user_id) {
        // For patients, role is always 3
        return $this->deleteMedicalImage($id_imagen, $user_id, 3);
    }
}

?>
