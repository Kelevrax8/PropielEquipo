# Doctor Assignment Feature Implementation

## Overview
This update implements physician tracking for appointments. Now each appointment is assigned to a specific doctor, and doctors can only see appointments assigned to them in their dashboards.

## Database Changes

### 1. Run the SQL Migration
Execute the file: `database_update_add_doctor_to_citas.sql`

This will:
- Add `id_doctor` column to the `citas` table
- Add an index for better query performance
- Add a foreign key constraint to ensure data integrity

```sql
-- Run this in phpMyAdmin or MySQL command line:
SOURCE database_update_add_doctor_to_citas.sql;
```

## How It Works

### Patient Reservation Flow

1. **Patient selects a service** (Dermatología, Podología, or Tamizaje)
2. **Doctor selector appears** with available doctors for that specialty
   - Option: "Cualquier médico disponible" (Recommended - automatic assignment)
   - Option: Specific doctor by name
3. **Patient completes the reservation**
4. **System assigns the doctor**:
   - If "cualquiera" selected → System automatically assigns an available doctor with that specialty
   - If specific doctor selected → That doctor is assigned to the appointment

### Doctor Dashboard Filtering

Each doctor now only sees:
- ✅ Appointments **assigned to them** (`id_doctor` = their user_id)
- ✅ Their own patients (patients who have appointments with them)
- ✅ Their appointment statistics (only counting their assignments)

### Updated Files

#### Database Layer (`src/database_queries.php`)
- `createAppointment()` - Now accepts `$id_doctor` parameter
- `getAvailableDoctorForSpecialty()` - NEW: Automatically assigns a doctor
- `getAllAppointments()` - Now filters by doctor_id (optional)
- `getAppointmentsByDate()` - Now filters by doctor_id (optional)
- `getAppointmentsWithPatientInfo()` - Now properly uses doctor_id filter

#### Patient Interface
- `src/Paciente/reservar.php` - Form sends doctor selection
- `src/Paciente/php_action/reservar.php` - Processes doctor assignment

#### Doctor Dashboards (All updated to filter by doctor_id)

**Dermatología:**
- `src/Doctor/especialidades/dermatologia/citas_dermatologia.php`
- `src/Doctor/especialidades/dermatologia/dashboard_dermatologia.php`
- `src/Doctor/especialidades/dermatologia/pacientes_dermatologia.php`

**Podología:**
- `src/Doctor/especialidades/podologia/citas_podologia.php`
- `src/Doctor/especialidades/podologia/dashboard_podologia.php`
- `src/Doctor/especialidades/podologia/pacientes_podologia.php`

**Tamizaje:**
- `src/Doctor/especialidades/tamizaje/citas_tamizaje.php`
- `src/Doctor/especialidades/tamizaje/dashboard_tamizaje.php`
- `src/Doctor/especialidades/tamizaje/pacientes_tamizaje.php`

## Testing Instructions

### 1. Run Database Migration
```sql
-- In phpMyAdmin, go to SQL tab and run:
ALTER TABLE `citas` 
ADD COLUMN `id_doctor` int(10) DEFAULT NULL AFTER `id_usuario`,
ADD INDEX `idx_citas_doctor` (`id_doctor`);

ALTER TABLE `citas`
ADD CONSTRAINT `fk_citas_doctor` 
FOREIGN KEY (`id_doctor`) REFERENCES `usuarios`(`user_id`) 
ON DELETE SET NULL ON UPDATE CASCADE;
```

### 2. Test Patient Reservation
1. Log in as a patient
2. Go to "Reservar Cita"
3. Select a service (e.g., Dermatología)
4. Notice the "Seleccionar Médico" dropdown appears
5. Try both options:
   - Select "Cualquier médico disponible"
   - Select a specific doctor
6. Complete the reservation
7. Verify in database that `id_doctor` is populated

### 3. Test Doctor Dashboard
1. Log in as a doctor (e.g., Dr. Hugo for Dermatología - ID: 19)
2. Go to their specialty dashboard
3. Verify you ONLY see appointments assigned to that specific doctor
4. Check "Mis Citas" - should only show their appointments
5. Check "Pacientes" - should only show patients who have appointments with them

### 4. Test with Multiple Doctors
1. Create appointments with different doctors in the same specialty
2. Log in as Doctor A → Should see only their appointments
3. Log in as Doctor B → Should see only their appointments
4. Verify they don't see each other's appointments

## Optional: Assign Existing Appointments

If you have existing appointments without assigned doctors, you can run:

```sql
-- Assign dermatología appointments to Dr. Hugo (ID 19)
UPDATE `citas` SET `id_doctor` = 19 
WHERE `servicio` = 'dermatología' AND `id_doctor` IS NULL;

-- Assign podología appointments to Dra. Maria (ID 21)
UPDATE `citas` SET `id_doctor` = 21 
WHERE `servicio` = 'podología' AND `id_doctor` IS NULL;

-- Assign tamiz appointments to Dr. Mario (ID 22)
UPDATE `citas` SET `id_doctor` = 22 
WHERE `servicio` = 'tamiz' AND `id_doctor` IS NULL;
```

## Key Benefits

✅ **Doctor Privacy**: Each doctor only sees their own appointments
✅ **Accurate Statistics**: Dashboard numbers reflect only that doctor's workload
✅ **Patient Choice**: Patients can choose their preferred doctor
✅ **Automatic Assignment**: System can assign doctors automatically if patient doesn't have a preference
✅ **Data Integrity**: Foreign key constraints ensure all assignments are valid
✅ **Performance**: Indexed `id_doctor` column for fast queries

## Troubleshooting

### Issue: Appointments not showing in doctor dashboard
- Check database: `SELECT id_doctor FROM citas WHERE id_cita = X`
- Verify doctor's user_id matches the id_doctor in appointment
- Make sure doctor has the correct specialty assigned in `doctor_especialidades`

### Issue: "cualquiera" option not assigning doctor
- Check `getAvailableDoctorForSpecialty()` method
- Verify doctors exist for that specialty in `doctor_especialidades`
- Check error logs for SQL errors

### Issue: Can still see all appointments
- Verify the dashboard file is updated (check for `$uid` parameter in queries)
- Clear browser cache
- Check that you're using the updated `database_queries.php`

## Next Steps (Optional Enhancements)

1. **Doctor Availability Management**: Allow doctors to set their available hours
2. **Appointment Reassignment**: Admin feature to reassign appointments between doctors
3. **Doctor Statistics**: Show each doctor their performance metrics
4. **Patient History**: Show complete patient history with this specific doctor
5. **Doctor Ratings**: Allow patients to rate their experience with each doctor
