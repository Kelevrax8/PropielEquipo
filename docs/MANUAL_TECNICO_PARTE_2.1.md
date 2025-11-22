# MANUAL TÉCNICO - SISTEMA PROPIELEQUIPO
## PARTE 2.1: Diagramas UML y Casos de Uso

**Versión:** 1.0  
**Fecha:** Noviembre 2025  
**Continuación de:** MANUAL_TECNICO_PARTE_1.md

---

# ÍNDICE DE CONTENIDOS - PARTE 2.1

4. [Arquitectura del Sistema (Continuación)](#4-arquitectura-del-sistema-continuación)
   - 4.3 Diagrama de Casos de Uso
   - 4.4 Diagramas de Secuencia
   - 4.5 Diagrama de Despliegue

---

# 4. ARQUITECTURA DEL SISTEMA (Continuación)

## 4.3 Diagrama de Casos de Uso

### 4.3.1 Casos de Uso - Vista General

```
┌──────────────────────────────────────────────────────────────────────┐
│                   SISTEMA PROPIELEQUIPO                              │
│                  Diagrama de Casos de Uso                            │
└──────────────────────────────────────────────────────────────────────┘

┌─────────┐                                              ┌──────────┐
│         │                                              │          │
│ Paciente│                                              │  Doctor  │
│         │                                              │          │
└────┬────┘                                              └────┬─────┘
     │                                                        │
     │ UC-01: Registrarse                                    │
     ├──────────────>( Registrarse )                        │
     │                                                        │
     │ UC-02: Iniciar Sesión                                 │ UC-11: Iniciar Sesión
     ├──────────────>( Iniciar Sesión )<────────────────────┤
     │                      ↑                                │
     │                      │                                │
     │                      │                                │
     │                      │                        ┌───────┴────────┐
     │                      │                        │                │
     │                      │                    ┌───┴───┐      ┌─────┴─────┐
     │                      │                    │ Admin │      │Enfermera  │
     │                      │                    │       │      │           │
     │                      │                    └───────┘      └───────────┘
     │                      │
     │ UC-03: Buscar Doctores                                  
     ├──────────────>( Buscar Doctores )
     │                  por Especialidad
     │                      │
     │                      │ <<include>>
     │                      ↓
     │ UC-04: Ver Horarios( Ver Disponibilidad )
     ├──────────────>  Disponibles         
     │                      │
     │                      │ <<include>>
     │                      ↓
     │ UC-05: Reservar    ( Validar Slot )
     ├──────────────>    Cita              
     │                      │
     │                      │ <<extend>>
     │                      ↓
     │ UC-06: Subir      ( Notificar por Email )
     ├──────────────> Comprobante de Pago  
     │                                                        │
     │ UC-07: Ver Mis Citas                                  │ UC-12: Ver Agenda
     ├──────────────>( Ver Citas )<─────────────────────────┤    de Citas
     │                                                        │
     │ UC-08: Cancelar Cita                                  │
     ├──────────────>( Cancelar Cita )                      │
     │                                                        │
     │ UC-09: Firmar Consentimiento                          │ UC-13: Verificar Pagos
     ├──────────────>( Generar Consentimiento)              ├──────────>( Verificar 
     │                   con Firma                           │           Comprobantes)
     │                                                        │              │
     │ UC-10: Subir/Ver Imágenes                             │              │ <<include>>
     ├──────────────>( Gestionar Imágenes  )<───────────────┤              ↓
     │                    Médicas                            │      ( Aprobar/Rechazar )
     │                                                        │
     │                                                        │ UC-14: Registrar
     │                                                        ├──────────>( Registrar
     │                                                        │        Observaciones)
     │                                                        │
     │                                                        │ UC-15: Consultar
     │                                                        ├──────────>( Ver Historial
     │                                                        │          del Paciente)
     │                                                        │              │
     │                                                        │              │ <<include>>
     │                                                        │              ↓
     │                                                        │      ( Generar PDF )
     │                                                        │
     │                                                        │ UC-16: Cambiar
     │                                                        ├──────────>( Cambiar
                                                             │        Especialidad)


                             ┌───────┐
                             │ Admin │
                             └───┬───┘
                                 │
                                 │ UC-17: Gestionar Horarios
                                 ├──────────>( Configurar Horarios )
                                 │                  de Doctores
                                 │                       │
                                 │                       │ <<include>>
                                 │                       ↓
                                 │              ( Definir Intervalos )
                                 │                       │
                                 │                       │ <<extend>>
                                 │                       ↓
                                 │              ( Aplicar a Semana )
                                 │
                                 │ UC-18: Bloquear Fechas
                                 ├──────────>( Gestionar Bloqueos )
                                 │               Vacaciones/Eventos
                                 │
                                 │ UC-19: Configurar Pagos
                                 ├──────────>( Actualizar Datos )
                                 │                Bancarios
                                 │
                                 │ UC-20: Ver Estadísticas
                                 └──────────>( Consultar Dashboard )
                                                 del Sistema
```

### 4.3.2 Especificación de Casos de Uso

#### UC-01: Registrarse en el Sistema

**Actor Principal:** Paciente (nuevo usuario)

**Precondiciones:**
- Usuario no tiene cuenta en el sistema
- Tiene un teléfono único no registrado
- Tiene un email único (opcional pero recomendado)

**Flujo Principal:**
1. Usuario accede a la página de registro (`registrar.html`)
2. Sistema muestra formulario de registro
3. Usuario ingresa:
   - Nombre y apellido
   - Edad
   - Teléfono (único, usado como username)
   - Email (único, opcional)
   - Contraseña (mínimo 6 caracteres)
   - Confirmar contraseña
   - Género
4. Usuario hace clic en "Registrarse"
5. Sistema valida datos:
   - Campos obligatorios completos
   - Contraseñas coinciden
   - Teléfono no existe en BD
   - Email no existe en BD (si proporcionado)
   - Edad válida (mayor a 18, menor a 120)
6. Sistema hashea contraseña con bcrypt
7. Sistema inserta registro en tabla `usuarios` con `rol=3` (Paciente)
8. Sistema crea sesión automática
9. Sistema redirige a dashboard del paciente

**Flujos Alternativos:**

**5a. Datos inválidos:**
- Sistema muestra mensaje de error específico
- Usuario corrige datos
- Retorna al paso 4

**5b. Teléfono duplicado:**
- Sistema muestra: "Este teléfono ya está registrado"
- Usuario puede intentar login o usar otro teléfono

**5c. Email duplicado:**
- Sistema muestra: "Este email ya está registrado"
- Usuario puede usar otro email o dejarlo vacío

**Postcondiciones:**
- Nuevo usuario creado en BD
- Usuario autenticado con sesión activa
- Usuario puede acceder a funcionalidades de paciente

**Archivos involucrados:**
- `src/Landing/registrar.html`
- `src/Landing/php_action/registrar.php`

---

#### UC-02: Iniciar Sesión (Paciente)

**Actor Principal:** Paciente registrado

**Precondiciones:**
- Usuario tiene cuenta creada
- Conoce su teléfono y contraseña

**Flujo Principal:**
1. Usuario accede a `login.html`
2. Sistema muestra formulario de login
3. Usuario ingresa teléfono y contraseña
4. Usuario hace clic en "Iniciar Sesión"
5. Sistema busca usuario por teléfono en tabla `usuarios`
6. Sistema verifica hash de contraseña con `password_verify()`
7. Sistema valida que `rol=3` (Paciente)
8. Sistema crea variables de sesión:
   - `$_SESSION['telefono']`
   - `$_SESSION['user_id']`
   - `$_SESSION['rol']`
9. Sistema actualiza `ultimo_acceso` en BD
10. Sistema redirige a `dashboardpaciente.php`

**Flujos Alternativos:**

**5a. Usuario no encontrado:**
- Sistema muestra: "Credenciales incorrectas"
- Retorna al paso 3

**6a. Contraseña incorrecta:**
- Sistema muestra: "Credenciales incorrectas"
- Incrementa contador de intentos fallidos (futuro)
- Retorna al paso 3

**7a. Rol incorrecto:**
- Usuario es doctor/admin intentando entrar como paciente
- Sistema muestra: "Use el login correspondiente a su rol"

**Postcondiciones:**
- Sesión PHP activa con datos del usuario
- Usuario redirigido a su dashboard
- Último acceso registrado

**Archivos involucrados:**
- `src/Landing/login.html`
- `src/Landing/php_action/login.php`

---

#### UC-05: Reservar Cita Médica

**Actor Principal:** Paciente

**Precondiciones:**
- Usuario autenticado como paciente
- Existe al menos un doctor con horarios configurados
- Usuario no tiene más de 3 citas pendientes (regla de negocio)

**Flujo Principal:**
1. Paciente accede a `reservar.php`
2. Sistema muestra selector de especialidad
3. Paciente selecciona especialidad (Dermatología/Podología/Tamizaje)
4. Sistema consulta doctores con esa especialidad (tabla `doctor_especialidades`)
5. Sistema muestra lista de doctores disponibles
6. Paciente selecciona un doctor
7. Sistema muestra calendario de fechas
8. Paciente selecciona fecha futura
9. Sistema ejecuta `get_available_hours.php`:
   - Consulta tabla `horarios` para el doctor y día de semana
   - Consulta tabla `citas` para ver slots ocupados
   - Consulta tabla `bloqueos_horarios` para fechas bloqueadas
   - Calcula slots disponibles
10. Sistema muestra botones de horarios disponibles
11. Paciente selecciona hora
12. Sistema muestra resumen de la cita
13. Paciente confirma reserva
14. Sistema valida disponibilidad nuevamente (race condition check)
15. Sistema inserta registro en tabla `citas`:
    ```sql
    INSERT INTO citas (
        id_usuario, id_doctor, fecha, horario, 
        servicio, estado, requiere_pago, monto
    ) VALUES (?, ?, ?, ?, ?, 'pendiente_pago', 1, 500.00)
    ```
16. Sistema muestra mensaje de éxito con instrucciones de pago
17. Sistema redirige a `subir_comprobante.php`

**Flujos Alternativos:**

**4a. No hay doctores con esa especialidad:**
- Sistema muestra: "No hay doctores disponibles para esta especialidad"
- Retorna al paso 2

**9a. Doctor no tiene horarios configurados:**
- Sistema muestra: "Este doctor no tiene horarios configurados"
- Retorna al paso 6

**10a. No hay horarios disponibles para la fecha:**
- Sistema muestra: "No hay horarios disponibles. Intente otra fecha"
- Retorna al paso 8

**14a. Slot ya fue reservado (race condition):**
- Sistema muestra: "Este horario acaba de ser reservado. Por favor elija otro"
- Retorna al paso 10

**Postcondiciones:**
- Nueva cita creada con estado `pendiente_pago`
- Slot de horario marcado como ocupado
- Paciente debe subir comprobante de pago

**Archivos involucrados:**
- `src/Paciente/reservar.php`
- `src/Paciente/php_action/get_available_hours.php`
- `src/Paciente/php_action/check_availability.php`
- `src/Paciente/php_action/reservar.php`

---

#### UC-13: Verificar Comprobantes de Pago

**Actor Principal:** Doctor

**Precondiciones:**
- Doctor autenticado
- Doctor tiene especialidad configurada
- Existen citas con estado `pendiente_pago` y comprobantes subidos

**Flujo Principal:**
1. Doctor accede a `verificar_pagos_*.php` de su especialidad
2. Sistema consulta:
   ```sql
   SELECT c.*, u.nombre, u.apellido 
   FROM citas c
   JOIN usuarios u ON c.id_usuario = u.user_id
   WHERE c.id_doctor = ? 
     AND c.estado = 'pendiente_pago'
     AND c.comprobante_pago IS NOT NULL
     AND LOWER(c.servicio) = 'especialidad'
   ORDER BY c.fecha_pago DESC
   ```
3. Sistema muestra galería de comprobantes con datos de citas
4. Doctor visualiza cada comprobante de pago
5. Doctor analiza la imagen del comprobante
6. Doctor decide aprobar o rechazar

**6a. APROBAR pago:**
7. Doctor hace clic en "Verificar Pago"
8. Sistema ejecuta `verificar_pago.php`:
   ```sql
   UPDATE citas 
   SET estado = 'pendiente',
       verificado_por = ?,
       fecha_verificacion = NOW(),
       notas_pago = ?
   WHERE id_cita = ?
   ```
9. Sistema muestra mensaje: "Pago verificado correctamente"
10. Sistema actualiza vista (cita desaparece de lista)
11. Sistema notifica al paciente (futuro: email/SMS)

**6b. RECHAZAR pago:**
7. Doctor hace clic en "Rechazar"
8. Sistema solicita motivo del rechazo
9. Doctor ingresa notas explicativas
10. Sistema ejecuta:
    ```sql
    UPDATE citas 
    SET estado = 'rechazada',
        verificado_por = ?,
        fecha_verificacion = NOW(),
        notas_pago = ?
    WHERE id_cita = ?
    ```
11. Sistema notifica al paciente con motivo (futuro)

**Flujos Alternativos:**

**3a. No hay comprobantes pendientes:**
- Sistema muestra: "No hay pagos pendientes de verificación"
- Doctor puede ver otras secciones

**4a. Imagen de comprobante no carga:**
- Sistema muestra placeholder con error
- Doctor puede reportar problema técnico

**Postcondiciones:**
- Estado de cita actualizado (`pendiente` o `rechazada`)
- Registro de doctor verificador y fecha
- Paciente notificado del resultado (futuro)

**Archivos involucrados:**
- `src/Doctor/especialidades/dermatologia/verificar_pagos_dermatologia.php`
- `src/Doctor/especialidades/podologia/verificar_pagos_podologia.php`
- `src/Doctor/especialidades/tamizaje/verificar_pagos_tamizaje.php`
- `src/Doctor/php_action/verificar_pago.php`

---

#### UC-17: Gestionar Horarios de Doctores

**Actor Principal:** Administrador

**Precondiciones:**
- Usuario autenticado como admin (`rol=4`)
- Existen doctores registrados en el sistema

**Flujo Principal:**
1. Admin accede a `gestionar_horarios.php`
2. Sistema muestra interfaz con 2 tabs:
   - Tab 1: Horarios Semanales
   - Tab 2: Bloqueos de Horarios
3. Admin selecciona Tab 1 (Horarios Semanales)
4. Sistema muestra selector de doctores
5. Admin selecciona un doctor
6. Sistema ejecuta `get_doctor_schedule.php`:
   ```sql
   SELECT * FROM horarios 
   WHERE id_doctor = ?
   ORDER BY FIELD(dia_semana, 'lunes', 'martes', ...)
   ```
7. Sistema muestra horarios actuales del doctor (si existen)
8. Admin llena formulario de nuevo horario:
   - Día de la semana (lunes-domingo)
   - Hora inicio (HH:MM)
   - Hora fin (HH:MM)
   - Intervalo en minutos (30/45/60/90/120)
9. Admin hace clic en "Guardar Horario"
10. Sistema valida:
    - Hora fin > hora inicio
    - No solapamiento con horarios existentes del mismo día
    - Intervalo válido
11. Sistema ejecuta `save_schedule.php` (lógica upsert):
    ```sql
    -- Si existe para ese día, UPDATE; sino INSERT
    INSERT INTO horarios (id_doctor, dia_semana, hora_inicio, hora_fin, intervalo_minutos)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        hora_inicio=VALUES(hora_inicio),
        hora_fin=VALUES(hora_fin),
        intervalo_minutos=VALUES(intervalo_minutos),
        activo=1
    ```
12. Sistema actualiza lista de horarios mostrados
13. Sistema muestra mensaje: "Horario guardado correctamente"

**Flujos Alternativos:**

**9a. Aplicar a toda la semana:**
- Admin marca checkbox "Aplicar a toda la semana"
- Sistema replica el horario para lunes-viernes
- Ejecuta 5 inserts/updates simultáneos

**10a. Validación falla:**
- Sistema muestra error específico
- Admin corrige datos
- Retorna al paso 8

**Acciones adicionales:**

**Activar/Desactivar horario:**
- Admin hace clic en toggle de un horario existente
- Sistema ejecuta `toggle_schedule.php`:
  ```sql
  UPDATE horarios SET activo = ? WHERE id_horario = ?
  ```
- Horario se mantiene en BD pero no se usa para reservas

**Eliminar horario:**
- Admin hace clic en botón eliminar
- Sistema solicita confirmación
- Sistema ejecuta `delete_schedule.php`:
  ```sql
  DELETE FROM horarios WHERE id_horario = ?
  ```

**Postcondiciones:**
- Horarios del doctor actualizados en BD
- Cambios reflejados inmediatamente en sistema de reservas
- Pacientes ven nuevos horarios al intentar reservar

**Archivos involucrados:**
- `src/Admin/gestionar_horarios.php`
- `src/Admin/php_action/get_doctor_schedule.php`
- `src/Admin/php_action/save_schedule.php`
- `src/Admin/php_action/toggle_schedule.php`
- `src/Admin/php_action/delete_schedule.php`

---

## 4.4 Diagramas de Secuencia

### 4.4.1 Secuencia: Reserva de Cita Completa

```
Paciente    Browser     reservar.php    get_available_hours.php    BD      doctor
   │            │              │                   │                │         │
   │   Accede   │              │                   │                │         │
   │───────────>│              │                   │                │         │
   │            │  GET         │                   │                │         │
   │            │─────────────>│                   │                │         │
   │            │              │                   │                │         │
   │            │              │ Validar sesión    │                │         │
   │            │              │───┐               │                │         │
   │            │              │<──┘               │                │         │
   │            │              │                   │                │         │
   │            │              │ SELECT doctores   │                │         │
   │            │              │──────────────────────────────────>│         │
   │            │              │                   │                │         │
   │            │              │<───────────────────────────────────│         │
   │            │<─────────────│                   │                │         │
   │            │ (HTML form)  │                   │                │         │
   │            │              │                   │                │         │
   │ Selecciona │              │                   │                │         │
   │  doctor y  │              │                   │                │         │
   │   fecha    │              │                   │                │         │
   │───────────>│              │                   │                │         │
   │            │              │                   │                │         │
   │            │  AJAX GET doctor_id=X&fecha=Y    │                │         │
   │            │──────────────────────────────────>│                │         │
   │            │              │                   │                │         │
   │            │              │                   │ SELECT horarios│         │
   │            │              │                   │───────────────>│         │
   │            │              │                   │<───────────────│         │
   │            │              │                   │                │         │
   │            │              │                   │ SELECT citas   │         │
   │            │              │                   │───────────────>│         │
   │            │              │                   │<───────────────│         │
   │            │              │                   │                │         │
   │            │              │                   │ SELECT bloqueos│         │
   │            │              │                   │───────────────>│         │
   │            │              │                   │<───────────────│         │
   │            │              │                   │                │         │
   │            │              │                   │ Calcular slots │         │
   │            │              │                   │ disponibles    │         │
   │            │              │                   │────┐           │         │
   │            │              │                   │<───┘           │         │
   │            │              │                   │                │         │
   │            │<─────────────────────────────────│                │         │
   │            │  JSON: [{hora: "09:00"}, ...]    │                │         │
   │            │              │                   │                │         │
   │ Ve horarios│              │                   │                │         │
   │ disponibles│              │                   │                │         │
   │            │              │                   │                │         │
   │ Selecciona │              │                   │                │         │
   │  horario   │              │                   │                │         │
   │───────────>│              │                   │                │         │
   │            │              │                   │                │         │
   │            │  POST reservar.php (datos cita)  │                │         │
   │            │─────────────>│                   │                │         │
   │            │              │                   │                │         │
   │            │              │ Validar slot nuevamente             │         │
   │            │              │──────────────────────────────────>│         │
   │            │              │<───────────────────────────────────│         │
   │            │              │                   │                │         │
   │            │              │ INSERT cita (estado: pendiente_pago)         │
   │            │              │──────────────────────────────────>│         │
   │            │              │<───────────────────────────────────│         │
   │            │              │                   │                │         │
   │            │<─────────────│                   │                │         │
   │            │  (Redirect a subir_comprobante)  │                │         │
   │<───────────│              │                   │                │         │
   │            │              │                   │                │         │
   │ Sube       │              │                   │                │         │
   │comprobante │              │                   │                │         │
   │───────────>│              │                   │                │         │
   │            │              │                   │                │         │
   │            │  POST upload (imagen)            │                │         │
   │            │──────────────────────────────────────────────────>│         │
   │            │              │                   │                │         │
   │            │              │ UPDATE cita.comprobante_pago       │         │
   │            │              │──────────────────────────────────>│         │
   │            │              │                   │                │         │
   │            │<─────────────────────────────────────────────────│         │
   │            │  "Comprobante subido. Espere verificación"        │         │
   │            │              │                   │                │         │
   │            │              │                   │                │         │
   │ Espera...  │              │                   │                │         │
   │            │              │                   │                │         │
   │            │              │                   │                │  Doctor │
   │            │              │                   │                │  accede │
   │            │              │                   │                │  a verif│
   │            │              │                   │                │<────────│
   │            │              │                   │                │         │
   │            │              │                   │ SELECT citas   │         │
   │            │              │                   │ pendiente_pago │         │
   │            │              │                   │<───────────────┤         │
   │            │              │                   │                │         │
   │            │              │                   │                │ Verifica│
   │            │              │                   │                │  imagen │
   │            │              │                   │                │         │
   │            │              │                   │                │ Aprueba │
   │            │              │                   │                │────────>│
   │            │              │                   │                │         │
   │            │              │ UPDATE cita       │                │         │
   │            │              │ estado='pendiente'│                │         │
   │            │              │ verificado_por=X  │                │         │
   │            │              │<──────────────────────────────────┤         │
   │            │              │                   │                │         │
   │  [Email]   │<─────────────────────────────────────────────────│         │
   │ "Su cita   │              │                   │                │         │
   │  ha sido   │              │                   │                │         │
   │ confirmada"│              │                   │                │         │
   │            │              │                   │                │         │
```

### 4.4.2 Secuencia: Generación de Historial Médico (PDF)

```
Doctor/Paciente  Browser   pacientes_*.php   get_patient_history_*.php   BD    jsPDF
      │              │              │                    │                │       │
      │  Clic en     │              │                    │                │       │
      │ "Ver historial"             │                    │                │       │
      │─────────────>│              │                    │                │       │
      │              │              │                    │                │       │
      │              │ generarHistorialPDF(userId)       │                │       │
      │              │──────────────┐                    │                │       │
      │              │              │                    │                │       │
      │              │ Mostrar "Generando..."             │                │       │
      │              │<─────────────┘                    │                │       │
      │              │              │                    │                │       │
      │              │   AJAX GET patient_id=X           │                │       │
      │              │───────────────────────────────────>│                │       │
      │              │              │                    │                │       │
      │              │              │ Validar sesión     │                │       │
      │              │              │ y especialidad     │                │       │
      │              │              │                    │───┐            │       │
      │              │              │                    │<──┘            │       │
      │              │              │                    │                │       │
      │              │              │ SELECT usuario     │                │       │
      │              │              │                    │───────────────>│       │
      │              │              │                    │<───────────────│       │
      │              │              │                    │                │       │
      │              │              │ SELECT citas WHERE │                │       │
      │              │              │ id_usuario=X AND   │                │       │
      │              │              │ servicio='especial'│                │       │
      │              │              │                    │───────────────>│       │
      │              │              │                    │<───────────────│       │
      │              │              │                    │ [{cita1},{...}]│       │
      │              │              │                    │                │       │
      │              │<──────────────────────────────────│                │       │
      │              │  JSON: {success:true,             │                │       │
      │              │         patient:{...},            │                │       │
      │              │         appointments:[...]}       │                │       │
      │              │              │                    │                │       │
      │              │ Procesar JSON│                    │                │       │
      │              │──────────────┐                    │                │       │
      │              │<─────────────┘                    │                │       │
      │              │              │                    │                │       │
      │              │ new jsPDF()  │                    │                │       │
      │              │──────────────────────────────────────────────────>│       │
      │              │              │                    │                │       │
      │              │              │                    │                │ Crear │
      │              │              │                    │                │  PDF  │
      │              │              │                    │                │──────┐│
      │              │              │                    │                │<─────┘│
      │              │              │                    │                │       │
      │              │ Agregar logo │                    │                │       │
      │              │──────────────────────────────────────────────────>│       │
      │              │              │                    │                │       │
      │              │ Agregar datos│                    │                │       │
      │              │ del paciente │                    │                │       │
      │              │──────────────────────────────────────────────────>│       │
      │              │              │                    │                │       │
      │              │ FOR cada cita│                    │                │       │
      │              │──────────────┐                    │                │       │
      │              │              │ Agregar sección    │                │       │
      │              │              │ con datos de cita  │                │       │
      │              │              │───────────────────────────────────>│       │
      │              │              │                    │                │       │
      │              │              │ Agregar observaciones               │       │
      │              │              │───────────────────────────────────>│       │
      │              │<─────────────┘                    │                │       │
      │              │              │                    │                │       │
      │              │ Agregar footer                    │                │       │
      │              │──────────────────────────────────────────────────>│       │
      │              │              │                    │                │       │
      │              │ pdf.output('blob')                │                │       │
      │              │──────────────────────────────────────────────────>│       │
      │              │<───────────────────────────────────────────────────│       │
      │              │              │  Blob PDF          │                │       │
      │              │              │                    │                │       │
      │              │ Crear URL    │                    │                │       │
      │              │ temporal     │                    │                │       │
      │              │──────────────┐                    │                │       │
      │              │<─────────────┘                    │                │       │
      │              │              │                    │                │       │
      │              │ window.open(pdfUrl)               │                │       │
      │              │──────────────┐                    │                │       │
      │<──────────────────────────────────────────────────────────────────       │
      │              │              │                    │                │       │
      │  Ve PDF en   │              │                    │                │       │
      │ nueva pestaña│              │                    │                │       │
      │              │              │                    │                │       │
```

### 4.4.3 Secuencia: Configuración de Horarios (Admin)

```
Admin        Browser    gestionar_horarios.php   save_schedule.php   BD
  │              │                │                       │            │
  │  Accede      │                │                       │            │
  │─────────────>│                │                       │            │
  │              │  GET           │                       │            │
  │              │───────────────>│                       │            │
  │              │                │                       │            │
  │              │                │ Validar sesión admin  │            │
  │              │                │ (rol=4)               │            │
  │              │                │───────┐               │            │
  │              │                │<──────┘               │            │
  │              │                │                       │            │
  │              │                │ SELECT doctores       │            │
  │              │                │──────────────────────────────────>│
  │              │                │<───────────────────────────────────│
  │              │<───────────────│                       │            │
  │              │  (HTML + lista de doctores)            │            │
  │              │                │                       │            │
  │ Selecciona   │                │                       │            │
  │   doctor     │                │                       │            │
  │─────────────>│                │                       │            │
  │              │                │                       │            │
  │              │  AJAX GET doctor_id=X                  │            │
  │              │──────────────────────────────────────────────────>│
  │              │                │                       │            │
  │              │                │ SELECT horarios WHERE │            │
  │              │                │ id_doctor=X           │            │
  │              │                │──────────────────────────────────>│
  │              │                │<───────────────────────────────────│
  │              │<───────────────│                       │            │
  │              │  JSON: [{dia,hora_inicio,...}, ...]    │            │
  │              │                │                       │            │
  │ Ve horarios  │                │                       │            │
  │  actuales    │                │                       │            │
  │              │                │                       │            │
  │ Configura    │                │                       │            │
  │ nuevo horario│                │                       │            │
  │ - día: lunes │                │                       │            │
  │ - inicio:09:00                │                       │            │
  │ - fin: 18:00 │                │                       │            │
  │ - intervalo:60                │                       │            │
  │─────────────>│                │                       │            │
  │              │                │                       │            │
  │              │  POST save_schedule.php                │            │
  │              │───────────────────────────────────────>│            │
  │              │                │                       │            │
  │              │                │ Validar sesión admin  │            │
  │              │                │                       │───┐        │
  │              │                │                       │<──┘        │
  │              │                │                       │            │
  │              │                │ Validar datos         │            │
  │              │                │ - hora_fin > inicio   │            │
  │              │                │ - intervalo válido    │            │
  │              │                │                       │───┐        │
  │              │                │                       │<──┘        │
  │              │                │                       │            │
  │              │                │ CHECK si existe horario            │
  │              │                │ para ese doctor+día   │            │
  │              │                │                       │            │
  │              │                │ SELECT id FROM horarios            │
  │              │                │ WHERE id_doctor=X     │            │
  │              │                │ AND dia_semana='lunes'│            │
  │              │                │──────────────────────────────────>│
  │              │                │<───────────────────────────────────│
  │              │                │                       │            │
  │              │                │ ¿Existe?              │            │
  │              │                │ ───┐                  │            │
  │              │                │    │ SÍ → UPDATE      │            │
  │              │                │    │ NO → INSERT      │            │
  │              │                │<───┘                  │            │
  │              │                │                       │            │
  │              │                │ INSERT INTO horarios  │            │
  │              │                │ (id_doctor, dia_semana,            │
  │              │                │  hora_inicio, hora_fin,            │
  │              │                │  intervalo_minutos)   │            │
  │              │                │ VALUES (?,?,?,?,?)    │            │
  │              │                │ ON DUPLICATE KEY UPDATE            │
  │              │                │──────────────────────────────────>│
  │              │                │<───────────────────────────────────│
  │              │                │                       │            │
  │              │<───────────────────────────────────────│            │
  │              │  JSON: {success:true, message:"..."}   │            │
  │<─────────────│                │                       │            │
  │              │                │                       │            │
  │ Ve mensaje   │                │                       │            │
  │ "Horario     │                │                       │            │
  │  guardado"   │                │                       │            │
  │              │                │                       │            │
  │              │  Recarga lista de horarios             │            │
  │              │───────────────────────────────────────────────────>│
  │              │<───────────────────────────────────────────────────│
  │              │                │                       │            │
  │ Ve horario   │                │                       │            │
  │ actualizado  │                │                       │            │
  │              │                │                       │            │
```

---

## 4.5 Diagrama de Despliegue

```
┌──────────────────────────────────────────────────────────────────────┐
│                     DIAGRAMA DE DESPLIEGUE                           │
│                    Arquitectura de 3 Capas                           │
└──────────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│                        CAPA DE CLIENTE                              │
│                      (Presentation Tier)                            │
└─────────────────────────────────────────────────────────────────────┘

    ┌──────────────┐       ┌──────────────┐       ┌──────────────┐
    │   Desktop    │       │   Mobile     │       │   Tablet     │
    │   Browser    │       │   Browser    │       │   Browser    │
    │              │       │              │       │              │
    │  Chrome 90+  │       │ iOS Safari   │       │  iPad        │
    │  Firefox 85+ │       │ Chrome And.  │       │  Android Tab │
    │  Safari 14+  │       │              │       │              │
    │  Edge 90+    │       │              │       │              │
    └──────┬───────┘       └──────┬───────┘       └──────┬───────┘
           │                      │                      │
           │     HTTPS            │     HTTPS            │     HTTPS
           │     (Port 443)       │     (Port 443)       │     (Port 443)
           │                      │                      │
           └──────────────────────┼──────────────────────┘
                                  │
                                  ↓
┌─────────────────────────────────────────────────────────────────────┐
│                      FIREWALL / LOAD BALANCER                       │
│                         (Seguridad + SSL)                           │
│                                                                     │
│  • Certificado SSL (Let's Encrypt / Certbot)                       │
│  • Protección DDoS                                                  │
│  • Rate limiting                                                    │
│  • IP whitelisting (opcional)                                      │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ↓
┌─────────────────────────────────────────────────────────────────────┐
│                      CAPA DE APLICACIÓN                             │
│                      (Application Tier)                             │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │              SERVIDOR WEB - Apache 2.4.x                    │   │
│  │                  o Nginx 1.18+                              │   │
│  │                                                             │   │
│  │  Document Root: /home/juanequipo/www/Propiel/              │   │
│  │                                                             │   │
│  │  Configuración:                                             │   │
│  │  • Virtual Host para dominio                                │   │
│  │  • mod_rewrite habilitado                                   │   │
│  │  • mod_ssl para HTTPS                                       │   │
│  │  • DirectoryIndex: index.html, index.php                    │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                  │                                  │
│                                  ↓                                  │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │              INTÉRPRETE PHP 7.4+ / 8.0+                     │   │
│  │                                                             │   │
│  │  Módulos PHP:                                               │   │
│  │  • php-fpm (FastCGI Process Manager)                        │   │
│  │  • php-mysql (PDO + mysqli)                                 │   │
│  │  • php-gd (manipulación de imágenes)                        │   │
│  │  • php-mbstring                                             │   │
│  │  • php-json                                                 │   │
│  │  • php-session                                              │   │
│  │                                                             │   │
│  │  Configuración php.ini:                                     │   │
│  │  • upload_max_filesize = 10M                                │   │
│  │  • post_max_size = 12M                                      │   │
│  │  • max_execution_time = 300                                 │   │
│  │  • memory_limit = 256M                                      │   │
│  │  • session.gc_maxlifetime = 3600                            │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                  │                                  │
│                                  ↓                                  │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │         APLICACIÓN PROPIELEQUIPO (PHP)                      │   │
│  │                                                             │   │
│  │  Estructura de archivos:                                    │   │
│  │  /src/                                                      │   │
│  │    ├── Landing/         (Login, Registro público)          │   │
│  │    ├── Paciente/        (Dashboard, Reservas, Imágenes)    │   │
│  │    ├── Doctor/          (Verificar pagos, Citas)           │   │
│  │    │   └── especialidades/                                  │   │
│  │    │       ├── dermatologia/                                │   │
│  │    │       ├── podologia/                                   │   │
│  │    │       └── tamizaje/                                    │   │
│  │    ├── Admin/           (Horarios, Configuración)          │   │
│  │    ├── php_action/      (APIs, Endpoints)                  │   │
│  │    ├── consentimientos/ (PDFs de consentimientos)          │   │
│  │    ├── comprobantes_pago/ (Imágenes de transferencias)     │   │
│  │    └── Images/                                              │   │
│  │        └── ImgMedicas/  (Imágenes médicas por paciente)    │   │
│  │                                                             │   │
│  │  Archivos de configuración:                                 │   │
│  │  • database_connection.php (Credenciales BD)                │   │
│  │  • database_queries.php (Clase de consultas)                │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  Sistema Operativo: Ubuntu Server 20.04 LTS / CentOS 8+           │
│  CPU: 2-4 cores | RAM: 4-8 GB | Storage: 50-100 GB SSD            │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  │ MySQL Protocol
                                  │ (Port 3306 - interno)
                                  ↓
┌─────────────────────────────────────────────────────────────────────┐
│                       CAPA DE DATOS                                 │
│                       (Data Tier)                                   │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │          SERVIDOR DE BASE DE DATOS                          │   │
│  │          MySQL 8.0+ / MariaDB 10.6+                         │   │
│  │                                                             │   │
│  │  Base de Datos: propielequipo2                              │   │
│  │                                                             │   │
│  │  Tablas principales (13):                                   │   │
│  │  • usuarios                  • citas                        │   │
│  │  • horarios                  • bloqueos_horarios            │   │
│  │  • especialidades            • doctor_especialidades        │   │
│  │  • servicios                 • imagenes_medicas             │   │
│  │  • configuracion_pagos       • genero                       │   │
│  │  • user_type                                                │   │
│  │                                                             │   │
│  │  Storage Engine: InnoDB (transacciones ACID)                │   │
│  │  Character Set: utf8mb4                                     │   │
│  │  Collation: utf8mb4_general_ci                              │   │
│  │                                                             │   │
│  │  Configuración my.cnf:                                      │   │
│  │  • max_connections = 150                                    │   │
│  │  • innodb_buffer_pool_size = 1G                             │   │
│  │  • query_cache_size = 256M                                  │   │
│  │                                                             │   │
│  │  Backups:                                                   │   │
│  │  • Backup diario automático (cron mysqldump)                │   │
│  │  • Retención: 7 días                                        │   │
│  │  • Almacenamiento en /backups/                              │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  Sistema Operativo: Mismo servidor o dedicado                      │
│  CPU: 2 cores | RAM: 2-4 GB | Storage: 20-50 GB SSD               │
└─────────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│                    SERVICIOS EXTERNOS (CDN)                         │
└─────────────────────────────────────────────────────────────────────┘

     ┌──────────────────┐        ┌──────────────────┐
     │   Ionicons CDN   │        │  jsPDF / html2   │
     │                  │        │    canvas CDN    │
     │  unpkg.com       │        │  cdnjs.cloud...  │
     └──────────────────┘        └──────────────────┘
              ↑                           ↑
              │                           │
              └───────────┬───────────────┘
                          │
                     (Cargados por cliente)


┌─────────────────────────────────────────────────────────────────────┐
│                       SERVICIOS FUTUROS                             │
└─────────────────────────────────────────────────────────────────────┘

     ┌──────────────────┐        ┌──────────────────┐
     │  Servicio Email  │        │  Servicio SMS    │
     │  (SMTP/SendGrid) │        │  (Twilio/Nexmo)  │
     │                  │        │                  │
     │  Para notificar  │        │  Para recordar   │
     │  verificaciones  │        │  citas           │
     └──────────────────┘        └──────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│                    CONEXIONES Y PROTOCOLOS                          │
└─────────────────────────────────────────────────────────────────────┘

Cliente ←→ Servidor Web
  • Protocolo: HTTPS (TLS 1.2+)
  • Puerto: 443
  • Formato: HTTP Request/Response
  • Métodos: GET, POST
  • Formatos de datos: HTML, JSON, multipart/form-data

Servidor Web ←→ PHP-FPM
  • Protocolo: FastCGI
  • Socket: Unix socket o TCP (127.0.0.1:9000)
  • Comunicación: Interna del servidor

PHP ←→ MySQL
  • Protocolo: MySQL Protocol
  • Puerto: 3306 (interno, no expuesto públicamente)
  • Driver: PDO con prepared statements
  • Connection pooling: Habilitado

Cliente ←→ CDN
  • Protocolo: HTTPS
  • Recursos: Librerías JavaScript, iconos
  • Caching: Habilitado en navegador
```

### 4.5.1 Notas de Despliegue

**Entorno de Producción:**
- **Dominio:** (A configurar por el cliente)
- **Hosting:** Servidor compartido o VPS con cPanel/Plesk
- **IP:** Estática recomendada
- **DNS:** Registro A apuntando al servidor

**Consideraciones de Seguridad:**
1. SSL/TLS obligatorio en producción
2. Firewall configurado (UFW/iptables)
3. MySQL accesible solo desde localhost
4. Archivos sensibles fuera de webroot
5. Permisos de archivos: 644 (archivos), 755 (directorios)
6. Carpeta de uploads con protección de ejecución PHP

**Monitoreo (Recomendado):**
- Logs de Apache/Nginx: `/var/log/apache2/` o `/var/log/nginx/`
- Logs de PHP: `/var/log/php-fpm/`
- Logs de MySQL: `/var/log/mysql/`
- Herramientas: Monit, Nagios, Uptime Robot

**Backup Strategy:**
- Base de datos: Backup diario con mysqldump
- Archivos: Backup semanal de carpetas críticas
- Retención: 7 días online, 30 días offline
- Procedimiento de restauración documentado

---

**FIN DE PARTE 2.1**

---

*Este documento continúa en:*
- **MANUAL_TECNICO_PARTE_2.2.md** (Diagrama de Componentes, Modelo de Datos Completo)
- **MANUAL_TECNICO_PARTE_3.md** (Código fuente, Módulos críticos, Pruebas)

---

**Elaborado por:** Equipo de Desarrollo PropielEquipo  
**Revisión:** v1.0 - Noviembre 2025  
**Contacto Técnico:** desarrollo@propielequipo.com
