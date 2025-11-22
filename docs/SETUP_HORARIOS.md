# 🚀 Quick Setup Guide - Sistema de Horarios

## Pasos de Instalación (5 minutos)

### 1️⃣ Ejecutar Script SQL

Abre **phpMyAdmin** y ejecuta:

```sql
-- Ejecuta todo el contenido de este archivo:
-- docs/horarios_doctor_schema.sql
```

O desde terminal:
```bash
mysql -u root propielequipo < docs/horarios_doctor_schema.sql
```

✅ Esto creará:
- Tabla `horarios_doctor`
- Tabla `bloqueos_horarios`
- Horarios por defecto para doctores existentes (Lun-Vie 9AM-6PM)

### 2️⃣ Verificar Instalación

```sql
-- Ver horarios creados
SELECT * FROM horarios_doctor;

-- Ver estructura de bloqueos
DESCRIBE bloqueos_horarios;
```

### 3️⃣ Acceder al Panel Admin

1. Iniciar sesión como admin:
   ```
   http://localhost/Juan/PropielEquipo/src/Admin/login_admin.php
   ```

2. Ir a "Gestión de Horarios":
   ```
   http://localhost/Juan/PropielEquipo/src/Admin/gestionar_horarios.php
   ```

### 4️⃣ Configurar Horario de un Doctor

1. Seleccionar doctor del dropdown
2. Verificar que aparezcan los horarios por defecto (Lun-Vie 9-18)
3. Opcional: Modificar horarios según necesidad
4. Opcional: Crear bloqueos de fechas

### 5️⃣ Probar como Paciente

1. Iniciar sesión como paciente
2. Ir a "Reservar Cita"
3. Seleccionar servicio → doctor → fecha
4. **Verificar que solo se muestren horarios configurados**

---

## 🔍 Verificación Rápida

### ✅ Todo funciona si:

**En Admin Panel:**
- ✅ Puedo ver lista de horarios del doctor
- ✅ Puedo agregar/eliminar horarios
- ✅ Puedo crear bloqueos de fechas

**En Reserva de Paciente:**
- ✅ Los horarios se cargan dinámicamente
- ✅ Solo veo horarios dentro del rango configurado
- ✅ No veo horarios bloqueados

### ❌ Si algo falla:

**Error: Tabla no existe**
→ Ejecutar `docs/horarios_doctor_schema.sql`

**Error: No hay horarios**
→ Insertar horarios desde admin panel

**Error: 403/No autorizado**
→ Verificar que estás logueado como admin (rol = 4)

---

## 🎯 Ejemplo Práctico

### Configurar Horario Personalizado

**Escenario:** Dr. García atiende solo por las tardes

1. Admin → Gestionar Horarios
2. Seleccionar "Dr. García"
3. Para cada día (Lun-Vie):
   - Hora inicio: `14:00`
   - Hora fin: `20:00`
   - Intervalo: `60` minutos
4. Click "Agregar Horario"

### Bloquear Vacaciones

**Escenario:** Dr. García de vacaciones del 20-31 Dic

1. Tab "Bloqueos de Horarios"
2. Seleccionar "Dr. García"
3. Configurar:
   - Tipo: `Vacaciones`
   - Fecha inicio: `2025-12-20`
   - Fecha fin: `2025-12-31`
   - Hora inicio: *(vacío)*
   - Hora fin: *(vacío)*
   - Motivo: `Vacaciones de fin de año`
4. Click "Crear Bloqueo"

**Resultado:** Los pacientes no verán horarios del Dr. García en esas fechas.

---

## 📋 Checklist Post-Instalación

- [ ] Script SQL ejecutado sin errores
- [ ] Tabla `horarios_doctor` existe
- [ ] Tabla `bloqueos_horarios` existe
- [ ] Horarios por defecto insertados para doctores
- [ ] Panel admin accesible
- [ ] Puedo ver/editar horarios desde admin
- [ ] Reserva de paciente muestra horarios dinámicos
- [ ] Sistema valida disponibilidad correctamente

---

## 🆘 Soporte Rápido

**Consultas SQL útiles:**

```sql
-- Ver todos los horarios
SELECT u.nombre, u.apellido, h.dia_semana, h.hora_inicio, h.hora_fin
FROM horarios_doctor h
JOIN usuarios u ON h.id_doctor = u.user_id
WHERE h.activo = 1;

-- Ver bloqueos activos
SELECT u.nombre, u.apellido, b.fecha_inicio, b.fecha_fin, b.motivo
FROM bloqueos_horarios b
JOIN usuarios u ON b.id_doctor = u.user_id
WHERE b.activo = 1 AND b.fecha_inicio >= CURDATE();

-- Insertar horario manual para doctor ID=1
INSERT INTO horarios_doctor (id_doctor, dia_semana, hora_inicio, hora_fin, intervalo_minutos)
VALUES (1, 'lunes', '09:00:00', '18:00:00', 60);
```

---

**¿Todo listo?** 🎉  
Consulta la documentación completa en: `docs/HORARIOS_SISTEMA.md`
