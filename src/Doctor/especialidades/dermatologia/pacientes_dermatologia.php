<?php
// Pacientes específicos para Dermatología
session_start();

// Verificar autenticación y especialidad
if (!isset($_SESSION['telefono']) || !isset($_SESSION['especialidades'])) {
    header('location: ../../../Landing/login.html');
    exit();
}

// Verificar que el doctor tiene especialidad en dermatología
$tiene_dermatologia = false;
foreach ($_SESSION['especialidades'] as $esp) {
    if ($esp['id_especialidad'] == 1) { // ID 1 = Dermatología
        $tiene_dermatologia = true;
        break;
    }
}

if (!$tiene_dermatologia) {
    header('location: ../../dashboarddoc.php');
    exit();
}

// Obtener información del doctor
require_once '../../../database_connection.php';
require_once '../../../database_queries.php';

try {
    $db_queries = new PropielEquipoQueries();
    $user = $db_queries->getUserById($_SESSION['user_id']);
    
    if (!$user) {
        session_destroy();
        header('location: ../../../Landing/login.html');
        exit();
    }
    
    $uid = $user['user_id'];
    $nombre = $user["nombre"];
    $apellido = $user["apellido"];
    
    // Obtener pacientes únicos que han tenido citas de dermatología con este doctor
    $pacientes_dermatologia = [];
    
    try {
        $todas_citas = $db_queries->getAllAppointments($uid);
        $pacientes_unicos = [];
        
        foreach ($todas_citas as $cita) {
            if (strtolower($cita['servicio']) === 'dermatología') {
                $user_id = $cita['id_usuario']; // Campo correcto según el SQL
                if (!isset($pacientes_unicos[$user_id])) {
                    $pacientes_unicos[$user_id] = [
                        'user_id' => $cita['id_usuario'],
                        'nombre' => $cita['nombre'],
                        'apellido' => $cita['apellido'],
                        'telefono' => $cita['telefono'],
                        'total_citas' => 0,
                        'ultima_cita' => $cita['fecha']
                    ];
                }
                $pacientes_unicos[$user_id]['total_citas']++;
                
                // Mantener la fecha más reciente
                if (strtotime($cita['fecha']) > strtotime($pacientes_unicos[$user_id]['ultima_cita'])) {
                    $pacientes_unicos[$user_id]['ultima_cita'] = $cita['fecha'];
                }
            }
        }
        
        $pacientes_dermatologia = array_values($pacientes_unicos);
        
        // Ordenar por última cita más reciente
        usort($pacientes_dermatologia, function($a, $b) {
            return strtotime($b['ultima_cita']) - strtotime($a['ultima_cita']);
        });
        
    } catch (Exception $e) {
        error_log("Error obteniendo pacientes de dermatología: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    error_log("Error en pacientes dermatología: " . $e->getMessage());
    header('location: ../../../Landing/login.html');
    exit();
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes Dermatología - PropielEquipo</title>
    <link href="../../../output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
    <!-- Librerías para generar PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-blue-100 flex flex-col min-h-screen">
    
    <?php include '../../shared/navbar_doctor.php'; ?>

    <!-- Header de Pacientes de Dermatología -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Pacientes de Dermatología</h1>
                    <p class="text-blue-100">Dr. <?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-blue-200 text-sm">Gestión de pacientes dermatológicos</p>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="people" class="text-6xl opacity-30"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Pacientes -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Pacientes</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo count($pacientes_dermatologia); ?></p>
                    </div>
                    <ion-icon name="people-outline" class="text-3xl text-blue-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pacientes Activos</p>
                        <p class="text-2xl font-bold text-green-600">
                            <?php 
                            $pacientes_activos = array_filter($pacientes_dermatologia, function($paciente) {
                                $dias = floor((time() - strtotime($paciente['ultima_cita'])) / (24 * 60 * 60));
                                return $dias <= 30; // Activos si tuvieron cita en los últimos 30 días
                            });
                            echo count($pacientes_activos);
                            ?>
                        </p>
                    </div>
                    <ion-icon name="pulse" class="text-3xl text-green-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Promedio Citas</p>
                        <p class="text-2xl font-bold text-purple-600">
                            <?php 
                            if (count($pacientes_dermatologia) > 0) {
                                $total_citas = array_sum(array_column($pacientes_dermatologia, 'total_citas'));
                                echo round($total_citas / count($pacientes_dermatologia), 1);
                            } else {
                                echo '0';
                            }
                            ?>
                        </p>
                    </div>
                    <ion-icon name="stats" class="text-3xl text-purple-500"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Tabla de Pacientes -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-blue-600 text-white p-6">
                <h2 class="text-xl font-bold flex items-center">
                    <ion-icon name="list" class="mr-2"></ion-icon>
                    Todos los Pacientes de Dermatología
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Citas</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Cita</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($pacientes_dermatologia) > 0): ?>
                            <?php foreach ($pacientes_dermatologia as $paciente): ?>
                                <?php 
                                $dias_ultima_cita = floor((time() - strtotime($paciente['ultima_cita'])) / (24 * 60 * 60));
                                $es_activo = $dias_ultima_cita <= 30;
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #<?php echo htmlspecialchars($paciente['user_id']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-10 h-10">
                                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <ion-icon name="person" class="text-blue-600"></ion-icon>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">Paciente dermatológico</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($paciente['telefono']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 text-xs font-semibold rounded-full">
                                            <?php echo $paciente['total_citas']; ?> citas
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($paciente['ultima_cita'])); ?>
                                        <div class="text-xs text-gray-500">
                                            Hace <?php echo $dias_ultima_cita; ?> días
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $es_activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $es_activo ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="generarHistorialPDF(<?php echo $paciente['user_id']; ?>, '<?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido'], ENT_QUOTES); ?>')" 
                                                class="text-blue-600 hover:text-blue-900 mr-3 cursor-pointer">
                                            Ver historial
                                        </button>
                                        <a href="#" class="text-green-600 hover:text-green-900">Nueva cita</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <ion-icon name="people-outline" class="text-6xl text-gray-300 mb-4"></ion-icon>
                                        <p class="text-lg">No hay pacientes de dermatología registrados</p>
                                        <p class="text-sm">Los pacientes aparecerán aquí cuando reserven citas de dermatología</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="dashboard_dermatologia.php" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                Volver al Dashboard
            </a>
            
            <a href="citas_dermatologia.php" 
               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="calendar" class="mr-2"></ion-icon>
                Ver Citas
            </a>
            
            <a href="imagenes_dermatologia.php" 
               class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="images" class="mr-2"></ion-icon>
                Imágenes Médicas
            </a>
        </div>
    </div>

<script>
    async function generarHistorialPDF(userId, nombrePaciente) {
        try {
            // Mostrar indicador de carga
            const loadingHTML = `
                <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded-lg shadow-xl">
                        <div class="flex items-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                            <span class="text-gray-700">Generando historial médico de ${nombrePaciente}...</span>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', loadingHTML);

            console.log('Obteniendo datos del paciente:', userId);

            // Usar el endpoint correcto para obtener datos reales
            const response = await fetch(`../../../php_action/get_patient_history.php?patient_id=${userId}`);
            
            if (!response.ok) {
                throw new Error(`Error del servidor: ${response.status} ${response.statusText}`);
            }
            
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);
            
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                console.error('Respuesta no JSON:', textResponse);
                throw new Error('El servidor devolvió un error: ' + textResponse.substring(0, 200));
            }

            const data = await response.json();
            console.log('Datos recibidos:', data);

            if (!data.success) {
                throw new Error(data.message || 'Error desconocido del servidor');
            }

            // Generar PDF con los datos reales de la base de datos
            await generatePatientHistoryPDF(data.patient, data.appointments);

        } catch (error) {
            console.error('Error completo:', error);
            alert('Error al generar el historial: ' + error.message + '\n\nPor favor, contacte al administrador del sistema.');
        } finally {
            // Remover indicador de carga
            const loading = document.getElementById('loading-overlay');
            if (loading) {
                loading.remove();
            }
        }
    }

    async function generatePatientHistoryPDF(patient, appointments) {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        
        const pageWidth = 210;
        const pageHeight = 297;
        const margin = 20;
        let yPosition = margin;

        // Agregar logo si está disponible
        try {
            const logoImg = new Image();
            await new Promise((resolve, reject) => {
                logoImg.onload = function() {
                    const logoWidth = 40;
                    const logoHeight = (this.height * logoWidth) / this.width;
                    const logoX = (pageWidth - logoWidth) / 2;
                    
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = this.width;
                    canvas.height = this.height;
                    ctx.drawImage(this, 0, 0);
                    
                    const logoDataUrl = canvas.toDataURL('image/png');
                    pdf.addImage(logoDataUrl, 'PNG', logoX, yPosition, logoWidth, logoHeight);
                    resolve();
                };
                logoImg.onerror = () => resolve(); // Continuar sin logo si falla
                logoImg.src = '../../../Images/logopropieel.png';
            });
            yPosition += 50;
        } catch (error) {
            console.log('Logo no disponible, continuando...');
            yPosition += 10;
        }

        // Título principal
        pdf.setFontSize(24);
        pdf.setFont('helvetica', 'bold');
        pdf.setTextColor(51, 51, 51);
        pdf.text('HISTORIAL MÉDICO', pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 15;

        pdf.setFontSize(16);
        pdf.setFont('helvetica', 'normal');
        pdf.setTextColor(70, 130, 180);
        pdf.text('Especialidad: Dermatología', pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 8;
        
        pdf.setFontSize(12);
        pdf.setFont('helvetica', 'italic');
        pdf.setTextColor(100, 100, 100);
        pdf.text('Este historial contiene únicamente citas de dermatología', pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 20;

        // Información del paciente (usando datos reales de la BD)
        pdf.setFontSize(16);
        pdf.setFont('helvetica', 'bold');
        pdf.setTextColor(51, 51, 51);
        pdf.text('INFORMACIÓN DEL PACIENTE', margin, yPosition);
        yPosition += 10;

        pdf.setLineWidth(0.5);
        pdf.setDrawColor(70, 130, 180);
        pdf.line(margin, yPosition, pageWidth - margin, yPosition);
        yPosition += 10;

        // Datos del paciente de la base de datos
        pdf.setFontSize(12);
        pdf.setFont('helvetica', 'normal');
        pdf.setTextColor(51, 51, 51);

        const patientInfo = [
            ['Nombre completo:', `${patient.nombre} ${patient.apellido}`],
            ['Teléfono:', patient.telefono || 'No especificado'],
            ['Género:', patient.genero || 'No especificado']
        ];

        patientInfo.forEach(([label, value]) => {
            pdf.setFont('helvetica', 'bold');
            pdf.text(label, margin, yPosition);
            pdf.setFont('helvetica', 'normal');
            pdf.text(String(value), margin + 45, yPosition);
            yPosition += 8;
        });

        // Fecha de generación (más a la derecha para evitar sobrelapamiento)
        yPosition += 5;
        pdf.setFont('helvetica', 'bold');
        pdf.text('Fecha de generación:', margin, yPosition);
        pdf.setFont('helvetica', 'normal');
        pdf.text(new Date().toLocaleDateString('es-ES'), margin + 45, yPosition);

        yPosition += 15;

        // Resumen estadístico basado en datos reales
        pdf.setFontSize(16);
        pdf.setFont('helvetica', 'bold');
        pdf.text('RESUMEN ESTADÍSTICO', margin, yPosition);
        yPosition += 10;

        pdf.line(margin, yPosition, pageWidth - margin, yPosition);
        yPosition += 10;

        const totalCitas = appointments.length;
        let primeraVisita = 'N/A';
        let ultimaVisita = 'N/A';

        if (totalCitas > 0) {
            // Ordenar citas por fecha para obtener primera y última
            const citasOrdenadas = [...appointments].sort((a, b) => new Date(a.fecha + 'T00:00:00') - new Date(b.fecha + 'T00:00:00'));
            primeraVisita = new Date(citasOrdenadas[0].fecha + 'T00:00:00').toLocaleDateString('es-ES');
            ultimaVisita = new Date(citasOrdenadas[citasOrdenadas.length - 1].fecha + 'T00:00:00').toLocaleDateString('es-ES');
        }

        const stats = [
            ['Total de Citas:', totalCitas.toString()],
            ['Primera Visita:', primeraVisita],
            ['Última Visita:', ultimaVisita]
        ];

        pdf.setFontSize(12);
        stats.forEach(([label, value]) => {
            pdf.setFont('helvetica', 'bold');
            pdf.text(label, margin, yPosition);
            pdf.setFont('helvetica', 'normal');
            pdf.text(value, margin + 45, yPosition);
            yPosition += 8;
        });

        yPosition += 15;

        // Historial de citas de la base de datos
        if (totalCitas > 0) {
            pdf.setFontSize(16);
            pdf.setFont('helvetica', 'bold');
            pdf.text('HISTORIAL DE CITAS DERMATOLÓGICAS', margin, yPosition);
            yPosition += 10;

            pdf.line(margin, yPosition, pageWidth - margin, yPosition);
            yPosition += 15;

            // Ordenar citas por fecha descendente (más reciente primero)
            const citasOrdenadas = [...appointments].sort((a, b) => new Date(b.fecha + 'T00:00:00') - new Date(a.fecha + 'T00:00:00'));

            citasOrdenadas.forEach((appointment, index) => {
                // Verificar espacio para nueva página
                if (yPosition > pageHeight - 50) {
                    pdf.addPage();
                    yPosition = margin;
                }

                // Encabezado de la cita
                pdf.setFontSize(14);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(70, 130, 180);
                pdf.text(`CITA #${appointment.id_cita || (index + 1)} - ${new Date(appointment.fecha + 'T00:00:00').toLocaleDateString('es-ES')}`, margin, yPosition);
                yPosition += 8;

                // Detalles de la cita de la base de datos
                pdf.setFontSize(11);
                pdf.setTextColor(51, 51, 51);

                const citaDetails = [
                    ['Fecha:', new Date(appointment.fecha + 'T00:00:00').toLocaleDateString('es-ES')],
                    ['Hora:', appointment.horario || 'No especificada'],
                    ['Servicio:', appointment.servicio || 'Dermatología'],
                    ['Especialidad:', appointment.especialidad || 'Dermatología'],
                    ['Doctor:', appointment.doctor_nombre || 'No especificado']
                ];

                citaDetails.forEach(([label, value]) => {
                    pdf.setFont('helvetica', 'bold');
                    pdf.text(label, margin + 5, yPosition);
                    pdf.setFont('helvetica', 'normal');
                    pdf.text(value, margin + 35, yPosition);
                    yPosition += 6;
                });

                // Observaciones médicas de la base de datos
                if (appointment.notas && appointment.notas.trim()) {
                    pdf.setFont('helvetica', 'bold');
                    pdf.text('Observaciones Médicas:', margin + 5, yPosition);
                    yPosition += 6;

                    pdf.setFont('helvetica', 'normal');
                    pdf.setTextColor(68, 68, 68);
                    
                    const observaciones = pdf.splitTextToSize(appointment.notas, pageWidth - margin - 15);
                    pdf.text(observaciones, margin + 5, yPosition);
                    yPosition += (observaciones.length * 5);
                } else {
                    pdf.setFont('helvetica', 'italic');
                    pdf.setTextColor(128, 128, 128);
                    pdf.text('Sin observaciones médicas registradas', margin + 5, yPosition);
                    yPosition += 6;
                }

                yPosition += 10;

                // Línea separadora entre citas
                if (index < citasOrdenadas.length - 1) {
                    pdf.setLineWidth(0.2);
                    pdf.setDrawColor(200, 200, 200);
                    pdf.line(margin + 5, yPosition, pageWidth - margin - 5, yPosition);
                    yPosition += 10;
                }
            });
        } else {
            pdf.setFontSize(14);
            pdf.setFont('helvetica', 'italic');
            pdf.setTextColor(128, 128, 128);
            pdf.text('Este paciente no tiene citas de dermatología registradas.', margin, yPosition);
            yPosition += 10;
            
            pdf.setFontSize(11);
            pdf.setFont('helvetica', 'normal');
            pdf.text('• Puede que tenga citas en otras especialidades', margin, yPosition);
            yPosition += 6;
            pdf.text('• Solo se muestran citas específicas de dermatología en este historial', margin, yPosition);
        }

        // Pie de página en todas las páginas
        const totalPages = pdf.internal.getNumberOfPages();
        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            pdf.setFontSize(10);
            pdf.setTextColor(128, 128, 128);
            pdf.text(
                `Historial Médico - ${patient.nombre} ${patient.apellido} - Página ${i} de ${totalPages}`,
                pageWidth / 2,
                pageHeight - 10,
                { align: 'center' }
            );
            pdf.text(
                `PropielEquipo - Generado el ${new Date().toLocaleDateString('es-ES')} a las ${new Date().toLocaleTimeString('es-ES')}`,
                pageWidth / 2,
                pageHeight - 5,
                { align: 'center' }
            );
        }

        // Generar y abrir PDF
        const pdfBlob = pdf.output('blob');
        const pdfUrl = URL.createObjectURL(pdfBlob);
        const newWindow = window.open(pdfUrl, '_blank');
        
        if (!newWindow) {
            alert('Por favor, permita las ventanas emergentes para ver el PDF');
            // Ofrecer descarga como alternativa
            const nombreArchivo = `historial_${patient.nombre}_${patient.apellido}_${new Date().toISOString().slice(0, 10)}.pdf`;
            pdf.save(nombreArchivo);
        }

        // Limpiar URL después de un tiempo
        setTimeout(() => {
            URL.revokeObjectURL(pdfUrl);
        }, 30000);

        console.log('Historial PDF generado exitosamente para:', patient.nombre, patient.apellido);
    }
</script>

</body>
</html>
