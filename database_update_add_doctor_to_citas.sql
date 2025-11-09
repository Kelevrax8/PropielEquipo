-- Add id_doctor column to citas table to track which doctor is assigned to each appointment
-- This allows the system to:
-- 1. Track which physician is assigned to each appointment
-- 2. Filter appointments by doctor in their respective dashboards
-- 3. Ensure doctors only see their own appointments

-- Step 1: Add the id_doctor column
ALTER TABLE `citas` 
ADD COLUMN `id_doctor` int(10) DEFAULT NULL AFTER `id_usuario`,
ADD INDEX `idx_citas_doctor` (`id_doctor`);

-- Step 2: Add foreign key constraint to ensure referential integrity
ALTER TABLE `citas`
ADD CONSTRAINT `fk_citas_doctor` 
FOREIGN KEY (`id_doctor`) REFERENCES `usuarios`(`user_id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Optional: Update existing appointments to assign them to a doctor
-- This is commented out - you can run it manually if you want to assign existing appointments
-- 
-- For Dermatología appointments (id_especialidad = 1), assign to doctor ID 19
-- UPDATE `citas` SET `id_doctor` = 19 WHERE `servicio` = 'dermatología' AND `id_doctor` IS NULL;
--
-- For Podología appointments (id_especialidad = 2), assign to doctor ID 21
-- UPDATE `citas` SET `id_doctor` = 21 WHERE `servicio` = 'podología' AND `id_doctor` IS NULL;
--
-- For Tamizaje appointments (id_especialidad = 3), assign to doctor ID 22 (or 23)
-- UPDATE `citas` SET `id_doctor` = 22 WHERE `servicio` = 'tamiz' AND `id_doctor` IS NULL;

-- Verify the changes
SELECT 'Column added successfully!' as status;
