# Sistema de Gestión de Horarios para Médicos

## 📋 Descripción

Este sistema permite configurar horarios dinámicos desde la base de datos para cada doctor, reemplazando los horarios fijos (hardcoded) que existían previamente. Los administradores pueden definir horarios personalizados por día de la semana, establecer la duración de las citas y bloquear fechas específicas.

## 🚀 Características

### 1. Horarios Personalizados por Doctor
- Configuración individual para cada médico
- Horarios diferentes para cada día de la semana
- Duración de citas configurable (30, 45, 60, 90, 120 minutos)
- Activar/desactivar horarios sin eliminarlos

### 2. Bloqueos de Horarios
- Bloquear fechas completas (vacaciones, días festivos)
- Bloquear franjas horarias específicas (conferencias, reuniones)
- Categorías de bloqueos: vacaciones, conferencia, urgencia, personal, otro
- Descripción opcional del motivo del bloqueo

### 3. Sistema de Reservas Inteligente
- Los pacientes solo ven horarios disponibles reales
- Considera horarios configurados, citas existentes y bloqueos
- Previene reservas en horarios no laborables
- Interfaz visual con indicadores de disponibilidad

## 📦 Archivos Creados/Modificados

### Nuevas Tablas de Base de Datos
```
docs/horarios_doctor_schema.sql
```
- `horarios_doctor`: Almacena horarios semanales por doctor
- `bloqueos_horarios`: Almacena bloqueos de fechas/horas

### Backend (PHP)
```
src/Paciente/php_action/get_available_hours.php
src/Admin/php_action/get_doctor_schedule.php
src/Admin/php_action/save_schedule.php
src/Admin/php_action/toggle_schedule.php
src/Admin/php_action/delete_schedule.php
src/Admin/php_action/get_doctor_blocks.php
src/Admin/php_action/save_block.php
src/Admin/php_action/delete_block.php
```

### Frontend
```
src/Admin/gestionar_horarios.php - Panel de administración
src/Paciente/reservar.php - Modificado para usar horarios de BD
```

## 🗄️ Esquema de Base de Datos

### Tabla: `horarios_doctor`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_horario_doctor | INT | ID único del horario |
| id_doctor | INT | FK a usuarios (doctor) |
| dia_semana | ENUM | lunes-domingo |
| hora_inicio | TIME | Hora de inicio del turno |
| hora_fin | TIME | Hora de fin del turno |
| intervalo_minutos | INT | Duración de cada cita |
| activo | BOOLEAN | Si el horario está activo |

### Tabla: `bloqueos_horarios`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_bloqueo | INT | ID único del bloqueo |
| id_doctor | INT | FK a usuarios (doctor) |
| fecha_inicio | DATE | Inicio del bloqueo |
| fecha_fin | DATE | Fin del bloqueo |
| hora_inicio | TIME | Hora inicio (NULL = todo el día) |
| hora_fin | TIME | Hora fin (NULL = todo el día) |
| motivo | VARCHAR | Razón del bloqueo |
| tipo_bloqueo | ENUM | vacaciones, conferencia, etc. |
| activo | BOOLEAN | Si el bloqueo está activo |

## 🔧 Instalación

### 1. Ejecutar Script de Base de Datos
```sql
-- Ejecutar en phpMyAdmin o MySQL CLI
SOURCE docs/horarios_doctor_schema.sql;
```

Este script:
- ✅ Crea las tablas `horarios_doctor` y `bloqueos_horarios`
- ✅ Establece claves foráneas y índices
- ✅ Inserta horarios por defecto (Lun-Vie, 9AM-6PM) para doctores existentes

### 2. Verificar Estructura
```sql
-- Verificar que las tablas se crearon correctamente
SHOW TABLES LIKE '%horarios%';

-- Ver horarios creados
SELECT * FROM horarios_doctor;
```

### 3. Configurar Acceso Admin
El panel de administración está en:
```
http://localhost/Juan/PropielEquipo/src/Admin/gestionar_horarios.php
```

Solo accesible para usuarios con `rol = 4` (Admin).

## 📖 Guía de Uso

### Para Administradores

#### Configurar Horarios de un Doctor
1. Ir a "Gestión de Horarios" en el panel admin
2. Seleccionar un doctor del dropdown
3. Configurar horarios por día:
   - Día de la semana
   - Hora de inicio y fin
   - Duración de cada cita
4. Click en "Agregar Horario"
5. Opcional: "Aplicar a Toda la Semana" para usar el mismo horario Lun-Vie

#### Crear Bloqueos
1. Ir a la pestaña "Bloqueos de Horarios"
2. Seleccionar doctor
3. Configurar bloqueo:
   - Tipo (vacaciones, conferencia, etc.)
   - Fechas de inicio y fin
   - Horas específicas (opcional)
   - Motivo descriptivo
4. Click en "Crear Bloqueo"

#### Ejemplos de Bloqueos

**Vacaciones de fin de año:**
```
Tipo: Vacaciones
Fecha inicio: 2025-12-20
Fecha fin: 2025-12-31
Hora inicio: (vacío)
Hora fin: (vacío)
Motivo: Vacaciones de fin de año
```

**Conferencia médica por la tarde:**
```
Tipo: Conferencia
Fecha inicio: 2025-11-25
Fecha fin: 2025-11-25
Hora inicio: 14:00
Hora fin: 18:00
Motivo: Conferencia de dermatología
```

### Para Pacientes

El proceso de reserva es automático:

1. Seleccionar servicio (dermatología, podología, tamizaje)
2. Seleccionar doctor
3. Seleccionar fecha
4. **El sistema automáticamente muestra solo horarios disponibles**
   - ✅ Horarios configurados para ese doctor
   - ✅ Sin citas existentes
   - ✅ Sin bloqueos activos
   - ✅ Solo días laborables

## 🔄 Flujo de Disponibilidad

```
Usuario selecciona fecha + doctor
        ↓
Consulta: get_available_hours.php
        ↓
1. Obtiene horarios_doctor (día de semana)
2. Verifica bloqueos_horarios (fecha)
3. Consulta citas existentes (fecha + doctor)
        ↓
Calcula horarios disponibles
        ↓
Devuelve lista filtrada al frontend
        ↓
Usuario ve solo horarios realmente disponibles
```

## 🎨 Interfaz de Usuario

### Panel de Admin
- 📅 **Tab Horarios:** Gestión de horarios semanales
  - Lista visual por día de semana
  - Indicadores de estado (Activo/Inactivo)
  - Acciones: Activar/Desactivar, Eliminar

- 🔒 **Tab Bloqueos:** Gestión de bloqueos
  - Cards con código de colores por tipo
  - Rango de fechas y horas
  - Motivo del bloqueo
  - Acción: Eliminar

### Reserva de Pacientes
- 🟢 **Horarios Disponibles:** ✅ Verde
- ⚪ **Información Contextual:**
  - Total de horarios disponibles
  - Horario de atención del doctor
  - Horarios ya ocupados
  - Notificación de bloqueos

## 🛡️ Validaciones

### Backend
- ✅ Verificación de rol de admin
- ✅ Validación de días de la semana
- ✅ Validación de rangos de fechas/horas
- ✅ Prevención de conflictos de horarios
- ✅ Sanitización de inputs

### Frontend
- ✅ Solo horarios dentro del rango configurado
- ✅ Exclusión de fines de semana (configurable)
- ✅ Prevención de fechas pasadas
- ✅ Verificación de disponibilidad en tiempo real
- ✅ Doble confirmación antes de reservar

## 🔧 Configuración Avanzada

### Cambiar Horarios Predeterminados
Editar en `horarios_doctor_schema.sql`:
```sql
INSERT INTO `horarios_doctor` (...) VALUES
    ..., '08:00:00', '20:00:00', 45, 1  -- 8AM-8PM, citas de 45 min
```

### Agregar Nuevos Tipos de Bloqueo
1. Modificar ENUM en tabla:
```sql
ALTER TABLE bloqueos_horarios 
MODIFY tipo_bloqueo ENUM('vacaciones','conferencia','urgencia','personal','emergencia','otro');
```

2. Actualizar select en `gestionar_horarios.php`:
```html
<option value="emergencia">Emergencia</option>
```

### Cambiar Días Laborables
Modificar en `get_available_hours.php`:
```php
// Permitir sábados
if ($dia_semana === 'domingo') {  // Solo excluir domingos
    ...
}
```

## 📊 Consultas Útiles

### Ver horarios de un doctor específico
```sql
SELECT u.nombre, u.apellido, h.dia_semana, h.hora_inicio, h.hora_fin, h.intervalo_minutos
FROM horarios_doctor h
JOIN usuarios u ON h.id_doctor = u.user_id
WHERE u.user_id = 1
ORDER BY FIELD(h.dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes');
```

### Ver próximos bloqueos
```sql
SELECT u.nombre, u.apellido, b.fecha_inicio, b.fecha_fin, b.tipo_bloqueo, b.motivo
FROM bloqueos_horarios b
JOIN usuarios u ON b.id_doctor = u.user_id
WHERE b.fecha_inicio >= CURDATE() AND b.activo = 1
ORDER BY b.fecha_inicio;
```

### Estadísticas de disponibilidad
```sql
SELECT 
    u.nombre,
    u.apellido,
    COUNT(h.id_horario_doctor) as dias_configurados,
    SUM(h.activo) as dias_activos
FROM usuarios u
LEFT JOIN horarios_doctor h ON u.user_id = h.id_doctor
WHERE u.rol = 1
GROUP BY u.user_id;
```

## 🐛 Solución de Problemas

### Problema: "No hay horarios configurados"
**Causa:** El doctor no tiene horarios en la BD  
**Solución:**
```sql
-- Insertar horarios de lunes a viernes
INSERT INTO horarios_doctor (id_doctor, dia_semana, hora_inicio, hora_fin, intervalo_minutos)
SELECT 1, dia, '09:00:00', '18:00:00', 60
FROM (
    SELECT 'lunes' as dia UNION ALL
    SELECT 'martes' UNION ALL
    SELECT 'miercoles' UNION ALL
    SELECT 'jueves' UNION ALL
    SELECT 'viernes'
) AS dias;
```

### Problema: Horarios no se muestran al paciente
**Verificar:**
1. ✅ Horarios están activos (`activo = 1`)
2. ✅ Doctor tiene especialidad asignada
3. ✅ No hay bloqueos para esa fecha
4. ✅ Console del navegador (F12) para errores JS

### Problema: Error 500 en get_available_hours.php
**Verificar:**
1. ✅ Tablas existen: `SHOW TABLES LIKE '%horarios%'`
2. ✅ Permisos de base de datos
3. ✅ Logs de error: `error_log` en PHP

## 🔐 Seguridad

- ✅ Validación de sesión en todas las APIs admin
- ✅ Verificación de rol = 4 (Admin)
- ✅ Prepared statements para prevenir SQL injection
- ✅ Sanitización de inputs
- ✅ HTTPS recomendado para producción

## 📈 Futuras Mejoras

- [ ] Importar/Exportar horarios en CSV
- [ ] Copiar horarios de un doctor a otro
- [ ] Notificaciones cuando se bloquean horarios
- [ ] Vista calendario para admin
- [ ] Reportes de ocupación por doctor
- [ ] API REST completa
- [ ] Soporte para excepciones (días festivos)
- [ ] Horarios rotativos/mensuales

## 📞 Soporte

Para problemas o preguntas:
1. Revisar esta documentación
2. Verificar logs de error PHP
3. Consultar console del navegador (F12)
4. Verificar estructura de base de datos

## 📝 Changelog

### v1.0.0 (2025-11-17)
- ✅ Sistema de horarios dinámicos implementado
- ✅ Bloqueos de horarios
- ✅ Panel de administración completo
- ✅ Integración con sistema de reservas
- ✅ Documentación completa

---

**Desarrollado para:** PropielEquipo  
**Fecha:** Noviembre 2025  
**Versión:** 1.0.0
