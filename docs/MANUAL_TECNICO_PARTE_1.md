# MANUAL TÉCNICO - SISTEMA PROPIELEQUIPO
## Sistema de Gestión de Citas Médicas y Consentimientos Informados

**Versión:** 1.0  
**Fecha:** Noviembre 2025  
**Organización:** PropielEquipo  

---

# ÍNDICE DE CONTENIDOS - PARTE 1

1. [Introducción](#1-introducción)
   - 1.1 Objetivo del Manual Técnico
   - 1.2 Alcance
   - 1.3 Público Objetivo

2. [Descripción General del Sistema](#2-descripción-general-del-sistema)
   - 2.1 Resumen del Sistema Web
   - 2.2 Módulos Principales

3. [Requerimientos Técnicos](#3-requerimientos-técnicos)
   - 3.1 Requerimientos Funcionales
   - 3.2 Requerimientos No Funcionales
   - 3.3 Requerimientos de Hardware
   - 3.4 Requerimientos de Software
   - 3.5 Dependencias y Librerías

4. [Arquitectura del Sistema](#4-arquitectura-del-sistema)
   - 4.1 Diagrama de Flujo General
   - 4.2 Diagramas de Flujo por Rol
   - 4.3 Diagrama de Casos de Uso
   - 4.4 Diagramas de Secuencia
   - 4.5 Diagrama de Despliegue
   - 4.6 Diagrama de Componentes

---

# 1. INTRODUCCIÓN

## 1.1 Objetivo del Manual Técnico

Este manual técnico tiene como objetivo proporcionar una guía completa y detallada sobre la arquitectura, implementación, configuración y mantenimiento del **Sistema PropielEquipo**, una aplicación web diseñada para la gestión integral de citas médicas, pagos, consentimientos informados y registros médicos.

El documento está dirigido a desarrolladores, administradores de sistemas, y personal técnico responsable de:
- Implementar y desplegar el sistema
- Realizar mantenimiento y actualizaciones
- Diagnosticar y resolver problemas técnicos
- Extender funcionalidades existentes
- Integrar con sistemas externos

## 1.2 Alcance

### Lo que CUBRE este manual:

✅ **Arquitectura técnica completa del sistema**
- Estructura de base de datos (tablas, relaciones, índices)
- Organización del código fuente (PHP, JavaScript, CSS)
- Flujos de datos y procesos internos
- APIs y endpoints disponibles

✅ **Configuración y despliegue**
- Requisitos de hardware y software
- Procedimientos de instalación
- Configuración de entornos (desarrollo, producción)
- Seguridad y permisos

✅ **Módulos funcionales detallados**
- Sistema de autenticación y roles
- Gestión de citas médicas por especialidad
- Verificación de pagos y comprobantes
- Consentimientos informados con firma digital
- Visualización de imágenes médicas
- Generación de PDFs e historiales
- Administración de horarios de doctores

✅ **Integración y APIs**
- Endpoints REST disponibles
- Formato de peticiones y respuestas
- Manejo de sesiones y autenticación

### Lo que NO CUBRE este manual:

❌ **Manual de usuario final** (pacientes sin conocimientos técnicos)
❌ **Procedimientos médicos** o protocolos clínicos
❌ **Políticas de negocio** no relacionadas con la implementación técnica
❌ **Marketing** o estrategias de crecimiento
❌ **Aspectos legales** o de cumplimiento normativo (HIPAA, GDPR, etc.)

## 1.3 Público Objetivo

Este manual está diseñado para:

### 👨‍💻 Desarrolladores
- Desarrolladores PHP/MySQL que necesitan entender, modificar o extender el código
- Front-end developers trabajando en la interfaz de usuario
- Desarrolladores full-stack integrando nuevas funcionalidades

**Conocimientos requeridos:**
- PHP 7.4+ (programación orientada a objetos, PDO, sesiones)
- MySQL/MariaDB (consultas SQL, diseño de bases de datos)
- HTML5, CSS3, JavaScript (ES6+)
- Git para control de versiones
- Conceptos de arquitectura web (MVC, REST APIs)

### 🔧 Administradores de Sistemas
- SysAdmins responsables del despliegue y mantenimiento
- DevOps configurando entornos y CI/CD
- Administradores de bases de datos

**Conocimientos requeridos:**
- Administración de servidores Linux/Windows
- Apache o Nginx
- MySQL server administration
- Gestión de backups y recuperación
- Seguridad web (SSL/TLS, firewalls)

### 🏥 Personal Técnico de Soporte
- Soporte IT resolviendo problemas de usuarios
- Administradores del sistema médico

**Conocimientos requeridos:**
- Conceptos básicos de bases de datos
- Navegación de logs y debugging
- Configuración de aplicaciones web

---

# 2. DESCRIPCIÓN GENERAL DEL SISTEMA

## 2.1 Resumen del Sistema Web

**PropielEquipo** es una aplicación web desarrollada en PHP para la gestión integral de servicios médicos especializados. El sistema facilita la interacción entre pacientes, médicos y administradores a través de una plataforma centralizada que automatiza procesos críticos como:

- **Reserva de citas médicas** con verificación de disponibilidad en tiempo real
- **Gestión de pagos** mediante comprobantes bancarios digitales
- **Consentimientos informados** con captura de firma digital
- **Historiales médicos** digitalizados y consultables
- **Imágenes médicas** seguras y organizadas por especialidad
- **Administración de horarios** flexible por doctor y día de la semana

### Características Principales:

🏥 **Multi-especialidad**
- Dermatología (ID: 1)
- Podología (ID: 2)
- Tamizaje (ID: 3)

👥 **Sistema de Roles**
- **Paciente (rol=3):** Reserva citas, sube comprobantes, visualiza su historial
- **Doctor (rol=1):** Gestiona citas por especialidad, verifica pagos, registra observaciones
- **Enfermera (rol=2):** Soporte administrativo (funcionalidad limitada)
- **Admin (rol=4):** Configuración global, gestión de horarios y pagos

💳 **Flujo de Pago Verificado**
- Reserva → Subir comprobante → Verificación del doctor → Confirmación de cita

📄 **Generación de Documentos**
- PDFs de consentimiento con firma digital
- Historiales médicos completos por especialidad
- Reportes de citas con filtros avanzados

🔒 **Seguridad**
- Autenticación con contraseñas hasheadas (bcrypt)
- Control de acceso basado en roles (RBAC)
- Validación de sesiones en cada endpoint
- Protección contra SQL injection (prepared statements)
- Segregación de imágenes médicas por paciente

## 2.2 Módulos Principales

### 2.2.1 Módulo de Autenticación y Gestión de Usuarios

**Ubicación:** `src/Landing/php_action/`, `src/Admin/php_action/`

**Funcionalidades:**
- Registro de pacientes con validación de datos
- Login diferenciado por rol (landing, admin, doctor)
- Gestión de sesiones PHP con variables específicas por rol
- Recuperación de contraseñas (en desarrollo)
- Último acceso registrado

**Flujo:**
```
Usuario → login.php → Validación BD → Crear sesión → Redirigir dashboard
```

**Tablas involucradas:**
- `usuarios` (datos de login y perfil)
- `genero` (catálogo)
- `user_type` (catálogo de roles)

### 2.2.2 Módulo de Citas Médicas

**Ubicación:** `src/Paciente/`, `src/Doctor/especialidades/`

**Funcionalidades:**
- Búsqueda de doctores por especialidad
- Verificación de disponibilidad horaria
- Reserva de citas con selección de fecha/hora
- Estados de cita: `pendiente_pago` → `pendiente` → `confirmada` → `completada`
- Cancelación de citas por paciente
- Visualización de agenda por doctor
- Filtros por fecha, estado, y especialidad

**Flujo del Paciente:**
```
Seleccionar especialidad → Elegir doctor → Ver horarios disponibles → 
Reservar → Subir comprobante → Esperar verificación → Cita confirmada
```

**Flujo del Doctor:**
```
Ver citas pendientes → Verificar pago → Confirmar cita → 
Atender paciente → Registrar observaciones → Marcar completada
```

**Tablas involucradas:**
- `citas` (registro de citas)
- `horarios` (disponibilidad de doctores)
- `bloqueos_horarios` (vacaciones, conferencias)
- `servicios` (catálogo de servicios médicos)
- `especialidades` (catálogo de especialidades)
- `doctor_especialidades` (relación muchos a muchos)

**APIs principales:**
- `get_doctors.php` - Lista doctores por especialidad
- `get_available_hours.php` - Horarios disponibles por fecha/doctor
- `check_availability.php` - Verificar disponibilidad de slot
- `reservar.php` - Crear nueva cita
- `cancel_appointment.php` - Cancelar cita

### 2.2.3 Módulo de Pagos y Verificación

**Ubicación:** `src/Paciente/subir_comprobante.php`, `src/Doctor/especialidades/*/verificar_pagos_*.php`

**Funcionalidades:**
- Subida de comprobantes de pago (imágenes JPG/PNG)
- Configuración de datos bancarios por admin
- Verificación visual de comprobantes por doctores
- Estados de pago: `pendiente_pago` → `pendiente` (verificado)
- Registro de fecha y doctor verificador
- Notas sobre el pago

**Flujo:**
```
Paciente reserva → Estado: pendiente_pago → Sube comprobante → 
Doctor verifica imagen → Aprueba/Rechaza → Estado: pendiente/rechazada
```

**Tablas involucradas:**
- `citas` (campo `comprobante_pago`, `verificado_por`, `fecha_verificacion`)
- `configuracion_pagos` (datos bancarios para transferencias)

**Validaciones:**
- Formato de archivo (MIME type)
- Tamaño máximo (2MB)
- Nombres únicos con timestamp
- Permisos de carpeta `comprobantes_pago/`

### 2.2.4 Módulo de Consentimientos Informados

**Ubicación:** `src/Paciente/consentimiento.php`, `src/consentimientos/`

**Funcionalidades:**
- Formularios de consentimiento por especialidad
- Captura de firma digital con canvas HTML5
- Generación de PDF con datos del paciente y firma
- Almacenamiento seguro de PDFs
- Visualización de consentimientos previos

**Flujo:**
```
Paciente accede → Llena formulario → Firma en canvas → 
Genera PDF → Guarda en servidor → Puede visualizar/descargar
```

**Tecnologías:**
- **Front-end:** HTML5 Canvas para captura de firma
- **Back-end:** jsPDF para generación de PDF en cliente
- **Almacenamiento:** `src/consentimientos/` con PDFs nombrados por ID

**Datos capturados:**
- Nombre completo, edad, fecha
- Descripción del procedimiento
- Riesgos aceptados
- Firma digitalizada (imagen base64)
- Timestamp de generación

### 2.2.5 Módulo de Imágenes Médicas

**Ubicación:** `src/Images/ImgMedicas/`, `src/Paciente/imagenes_medicas.php`

**Funcionalidades:**
- Subida de imágenes médicas (rayos X, fotos dermatológicas, etc.)
- Organización por paciente en carpetas
- Tipos de imagen: `dermatologia`, `podologia`, `tamiz`, `rayos_x`, `laboratorio`, `general`
- Visualización segura con validación de permisos
- Eliminación de imágenes por paciente
- Galería con descripción y fecha

**Seguridad:**
- Carpetas nominadas por `user_id` del paciente
- Validación de sesión antes de servir imágenes
- Script `secure_image_viewer.php` que verifica ownership
- Imágenes fuera del document root (en desarrollo)

**Flujo:**
```
Paciente sube imagen → Validar formato/tamaño → 
Crear carpeta user_id → Guardar archivo → 
Registrar en BD → Mostrar en galería
```

**Tablas involucradas:**
- `imagenes_medicas` (metadata de imágenes)

**APIs principales:**
- `upload_medical_image.php` - Subir nueva imagen
- `delete_medical_image.php` - Eliminar imagen
- `secure_image_viewer.php` - Servir imagen con validación

### 2.2.6 Módulo de Historiales Médicos

**Ubicación:** `src/php_action/get_patient_history*.php`, Código JavaScript en páginas de pacientes

**Funcionalidades:**
- Consulta de todas las citas por paciente
- Filtrado por especialidad
- Generación de PDF con historial completo
- Inclusión de observaciones médicas
- Datos del doctor tratante
- Estadísticas (total citas, primera/última visita)

**Generación de PDF:**
```javascript
// Cliente: jsPDF + html2canvas
fetch(`get_patient_history_podologia.php?patient_id=${id}`)
→ Recibir JSON con citas
→ Generar PDF con jsPDF
→ Agregar logo, datos paciente, tabla de citas
→ Abrir en nueva ventana / Descargar
```

**APIs por especialidad:**
- `get_patient_history.php` - Dermatología (genérico)
- `get_patient_history_podologia.php` - Podología
- `get_patient_history_tamizaje.php` - Tamizaje

### 2.2.7 Módulo de Administración de Horarios

**Ubicación:** `src/Admin/gestionar_horarios.php`, `src/Admin/php_action/`

**Funcionalidades:**
- Configuración de horarios por doctor
- Definición de horarios por día de la semana
- Intervalos personalizables (30, 45, 60, 90, 120 minutos)
- Activar/desactivar horarios sin eliminar
- Bloqueos de fechas (vacaciones, conferencias, personal)
- Aplicar horarios a toda la semana
- Vista consolidada de disponibilidad

**Interfaz:**
- Tab 1: Horarios Semanales
- Tab 2: Bloqueos de Horarios

**Flujo:**
```
Admin selecciona doctor → Configura día/horario/intervalo → 
Guarda → Sistema genera slots disponibles → 
Pacientes ven solo horarios activos al reservar
```

**Tablas involucradas:**
- `horarios` (horarios base por doctor/día)
- `bloqueos_horarios` (excepciones de fechas)

**APIs:**
- `get_doctor_schedule.php` - Obtener horarios de un doctor
- `save_schedule.php` - Crear/actualizar horario (upsert)
- `toggle_schedule.php` - Activar/desactivar
- `delete_schedule.php` - Eliminar horario
- `get_doctor_blocks.php` - Obtener bloqueos
- `save_block.php` - Crear bloqueo
- `delete_block.php` - Eliminar bloqueo

### 2.2.8 Módulo de Dashboards

**Por Rol:**

**Paciente:** `src/Paciente/dashboardpaciente.php`
- Próximas citas
- Acceso rápido a reservar
- Ver comprobantes pendientes
- Acceso a imágenes médicas

**Doctor:** `src/Doctor/especialidades/*/dashboard_*.php`
- Estadísticas de citas del día/mes
- Citas pendientes de verificación de pago
- Citas confirmadas del día
- Acceso a pacientes y historiales
- Diferenciado por especialidad (dermatología, podología, tamizaje)

**Admin:** `src/Admin/dashboard_admin.php`
- Total doctores/pacientes
- Estadísticas de citas
- Pagos pendientes/completados del mes
- Últimos pacientes y citas
- Acceso a configuración

---

# 3. REQUERIMIENTOS TÉCNICOS

## 3.1 Requerimientos Funcionales

### RF-01: Gestión de Usuarios
**Descripción:** El sistema debe permitir registro, autenticación y gestión de perfiles de usuarios con diferentes roles.

**Criterios de aceptación:**
- ✅ Registro de pacientes con validación de campos (nombre, teléfono único, email único, edad)
- ✅ Login con teléfono y contraseña
- ✅ Contraseñas hasheadas con bcrypt
- ✅ Sesiones PHP persistentes con timeout
- ✅ Roles: Paciente, Doctor, Enfermera, Admin
- ✅ Validación de rol en cada endpoint
- ✅ Registro de último acceso

**Prioridad:** ALTA  
**Estado:** IMPLEMENTADO

### RF-02: Reserva de Citas
**Descripción:** Los pacientes deben poder reservar citas médicas con doctores según especialidad y disponibilidad.

**Criterios de aceptación:**
- ✅ Selección de especialidad (dermatología, podología, tamizaje)
- ✅ Lista de doctores filtrada por especialidad
- ✅ Calendario de disponibilidad por doctor
- ✅ Validación de horarios no ocupados
- ✅ Confirmación de reserva
- ✅ Estado inicial: `pendiente_pago`
- ✅ Email de confirmación (pendiente implementación)

**Prioridad:** ALTA  
**Estado:** IMPLEMENTADO (email pendiente)

### RF-03: Verificación de Pagos
**Descripción:** Sistema de carga y verificación de comprobantes de pago previo a confirmación de citas.

**Criterios de aceptación:**
- ✅ Paciente sube imagen de comprobante (JPG/PNG)
- ✅ Doctor visualiza comprobante en dashboard
- ✅ Doctor puede aprobar/rechazar pago
- ✅ Registro de verificador y fecha
- ✅ Cambio de estado: `pendiente_pago` → `pendiente`
- ✅ Notas opcionales del doctor

**Prioridad:** ALTA  
**Estado:** IMPLEMENTADO

### RF-04: Consentimientos Informados
**Descripción:** Generación y almacenamiento de consentimientos informados con firma digital del paciente.

**Criterios de aceptación:**
- ✅ Formulario por especialidad
- ✅ Captura de firma en canvas HTML5
- ✅ Generación de PDF con jsPDF
- ✅ Almacenamiento seguro de PDFs
- ✅ Visualización de consentimientos previos
- ✅ Descarga de PDFs

**Prioridad:** MEDIA  
**Estado:** IMPLEMENTADO

### RF-05: Historiales Médicos
**Descripción:** Consulta y generación de historiales médicos completos por paciente y especialidad.

**Criterios de aceptación:**
- ✅ Consulta de citas históricas por paciente
- ✅ Filtrado por especialidad
- ✅ Visualización de observaciones médicas
- ✅ Generación de PDF con logo y datos completos
- ✅ Estadísticas (total citas, fechas primera/última)
- ✅ Restricción por permisos de especialidad

**Prioridad:** MEDIA  
**Estado:** IMPLEMENTADO

### RF-06: Gestión de Imágenes Médicas
**Descripción:** Carga, organización y visualización segura de imágenes médicas.

**Criterios de aceptación:**
- ✅ Carga de imágenes (JPG, PNG, PDF)
- ✅ Tipos: dermatologia, podologia, tamiz, rayos_x, laboratorio
- ✅ Organización por paciente (carpetas)
- ✅ Descripción y fecha de subida
- ✅ Visualización con control de acceso
- ✅ Eliminación por paciente
- ✅ Galería responsive

**Prioridad:** MEDIA  
**Estado:** IMPLEMENTADO

### RF-07: Administración de Horarios
**Descripción:** Configuración flexible de horarios de atención por doctor, día y especialidad.

**Criterios de aceptación:**
- ✅ Definición de horarios por día de semana
- ✅ Intervalos configurables (30-120 minutos)
- ✅ Activar/desactivar sin eliminar
- ✅ Bloqueos de fechas (vacaciones, conferencias)
- ✅ Aplicación masiva de horarios
- ✅ Integración con sistema de reservas
- ✅ Validación de solapamientos

**Prioridad:** ALTA  
**Estado:** IMPLEMENTADO

### RF-08: Dashboards por Rol
**Descripción:** Interfaces diferenciadas con información relevante para cada tipo de usuario.

**Criterios de aceptación:**
- ✅ Dashboard paciente: próximas citas, accesos rápidos
- ✅ Dashboard doctor: citas del día, pagos pendientes, por especialidad
- ✅ Dashboard admin: estadísticas globales, configuración
- ✅ Gráficos y métricas en tiempo real
- ✅ Acciones rápidas contextuales

**Prioridad:** MEDIA  
**Estado:** IMPLEMENTADO

### RF-09: Cancelación de Citas
**Descripción:** Pacientes pueden cancelar citas reservadas.

**Criterios de aceptación:**
- ✅ Botón de cancelación en citas pendientes
- ✅ Confirmación antes de cancelar
- ✅ Estado cambia a: `cancelada`
- ✅ Horario liberado para otros pacientes
- ✅ Restricción temporal (no cancelar el mismo día)

**Prioridad:** MEDIA  
**Estado:** IMPLEMENTADO (restricciones pendientes)

### RF-10: Registro de Observaciones Médicas
**Descripción:** Doctores registran notas y observaciones durante consultas.

**Criterios de aceptación:**
- ✅ Campo de texto para observaciones
- ✅ Guardado en cita completada
- ✅ Edición posterior de observaciones
- ✅ Visualización en historial del paciente
- ✅ Privacidad (solo doctor y paciente)

**Prioridad:** ALTA  
**Estado:** IMPLEMENTADO

## 3.2 Requerimientos No Funcionales

### RNF-01: Seguridad
**Descripción:** El sistema debe proteger datos sensibles de pacientes y cumplir con buenas prácticas de seguridad.

**Criterios:**
- ✅ Contraseñas hasheadas con bcrypt (cost 10)
- ✅ Prepared statements en todas las consultas SQL
- ✅ Validación de sesión en cada endpoint
- ✅ Control de acceso basado en roles (RBAC)
- ✅ Sanitización de inputs de usuario
- ✅ Protección contra SQL injection
- ✅ Archivos sensibles fuera de webroot (parcial)
- ⚠️ HTTPS obligatorio (configuración de servidor)
- ⚠️ Headers de seguridad (CSP, X-Frame-Options)

**Nivel:** CRÍTICO  
**Estado:** PARCIALMENTE IMPLEMENTADO

### RNF-02: Rendimiento
**Descripción:** El sistema debe responder de manera eficiente bajo carga normal.

**Criterios:**
- ✅ Consultas SQL optimizadas con índices
- ✅ Tiempo de respuesta < 2 segundos para operaciones comunes
- ✅ Carga de imágenes optimizada (lazy loading)
- ✅ Caché de sesiones PHP
- ⚠️ Compresión de assets (CSS/JS)
- ⚠️ CDN para librerías externas

**Métricas objetivo:**
- Login: < 1 segundo
- Carga de dashboard: < 2 segundos
- Reserva de cita: < 3 segundos
- Generación de PDF: < 5 segundos

**Estado:** ACEPTABLE (optimizaciones pendientes)

### RNF-03: Disponibilidad
**Descripción:** El sistema debe estar disponible 24/7 con mínimo downtime.

**Criterios:**
- 🎯 Uptime objetivo: 99.5% (43 horas downtime/año)
- ⚠️ Backups automáticos diarios
- ⚠️ Plan de recuperación ante desastres
- ⚠️ Monitoreo de servidor
- ⚠️ Logs centralizados

**Estado:** DEPENDE DE INFRAESTRUCTURA

### RNF-04: Usabilidad
**Descripción:** Interfaces intuitivas y responsivas para todos los tipos de usuario.

**Criterios:**
- ✅ Diseño responsive (mobile-first)
- ✅ Navegación clara con menús contextuales
- ✅ Mensajes de error descriptivos
- ✅ Feedback visual de acciones (spinners, confirmaciones)
- ✅ Iconos intuitivos (Ionicons)
- ✅ Paleta de colores diferenciada por especialidad

**Estado:** IMPLEMENTADO

### RNF-05: Escalabilidad
**Descripción:** El sistema debe soportar crecimiento de usuarios y datos.

**Criterios:**
- ✅ Diseño modular por especialidades
- ✅ Separación de concerns (BD, lógica, presentación)
- ⚠️ Posibilidad de sharding de BD
- ⚠️ Migración a arquitectura distribuida

**Capacidad actual:**
- Hasta 1,000 usuarios concurrentes
- 100,000+ citas almacenadas
- 50GB de imágenes médicas

**Estado:** SUFICIENTE PARA INICIO

### RNF-06: Mantenibilidad
**Descripción:** Código legible, documentado y fácil de mantener.

**Criterios:**
- ✅ Estructura de carpetas organizada
- ✅ Nomenclatura consistente
- ✅ Comentarios en código complejo
- ✅ Separación de configuración (database_connection.php)
- ⚠️ Documentación inline de APIs
- ⚠️ Tests unitarios

**Estado:** BUENO (tests pendientes)

### RNF-07: Compatibilidad
**Descripción:** Funcionamiento en múltiples navegadores y dispositivos.

**Criterios:**
- ✅ Chrome/Edge 90+
- ✅ Firefox 85+
- ✅ Safari 14+
- ✅ iOS Safari
- ✅ Chrome Android
- ✅ Tablets y desktops

**Estado:** IMPLEMENTADO

## 3.3 Requerimientos de Hardware

### Servidor de Producción (Mínimo)

**Procesador:**
- 2 cores x 2.4 GHz (Intel Xeon o AMD EPYC)
- Recomendado: 4 cores para mejor concurrencia

**Memoria RAM:**
- Mínimo: 4 GB
- Recomendado: 8 GB
- Distribución:
  - 2 GB para MySQL
  - 1 GB para Apache/PHP
  - 1 GB para sistema operativo
  - Resto para caché y buffers

**Almacenamiento:**
- Mínimo: 50 GB SSD
- Recomendado: 100 GB SSD
- Distribución estimada:
  - 10 GB sistema operativo
  - 5 GB aplicación y logs
  - 20 GB base de datos (crecimiento 500MB/mes)
  - 15 GB imágenes médicas (crecimiento 2GB/mes)
  - Resto para backups locales

**Red:**
- Ancho de banda: 100 Mbps simétrico mínimo
- Latencia: < 50ms a usuarios principales
- IP estática para DNS

### Servidor de Desarrollo (Local)

**Características mínimas:**
- Procesador: 2 cores x 2.0 GHz
- RAM: 4 GB
- Almacenamiento: 20 GB disponibles
- XAMPP o similar

### Cliente (Usuario Final)

**Dispositivos compatibles:**
- **Desktop:** Windows 10+, macOS 10.14+, Linux (Ubuntu 18.04+)
- **Mobile:** iOS 13+, Android 8+
- **Tablets:** iPad, Android tablets

**Navegadores:**
- Chrome 90+
- Firefox 85+
- Safari 14+
- Edge 90+

**Conectividad:**
- Mínimo: 2 Mbps (3G/4G)
- Recomendado: 10 Mbps (Wi-Fi, 4G LTE)

## 3.4 Requerimientos de Software

### Servidor de Producción

**Sistema Operativo:**
- Linux (recomendado): Ubuntu Server 20.04 LTS o superior, CentOS 8+, Debian 11+
- Windows Server 2019+ (alternativa)

**Servidor Web:**
- Apache 2.4.x (recomendado)
  - Módulos requeridos: mod_rewrite, mod_ssl
- Nginx 1.18+ (alternativa)
- Configuración HTTPS obligatoria

**PHP:**
- Versión: **PHP 7.4** o superior (recomendado PHP 8.0+)
- Extensiones requeridas:
  ```
  php-mysql (PDO, mysqli)
  php-gd (manipulación de imágenes)
  php-mbstring (multibyte strings)
  php-json
  php-session
  php-fileinfo (MIME type detection)
  php-curl (para integraciones futuras)
  ```
- Configuración php.ini:
  ```ini
  upload_max_filesize = 10M
  post_max_size = 12M
  max_execution_time = 300
  memory_limit = 256M
  session.gc_maxlifetime = 3600
  ```

**Base de Datos:**
- MySQL 5.7+ o MariaDB 10.3+
- Recomendado: MySQL 8.0+ o MariaDB 10.6+
- Configuración:
  ```ini
  max_connections = 150
  innodb_buffer_pool_size = 1G
  character_set_server = utf8mb4
  collation_server = utf8mb4_general_ci
  ```

**Herramientas Adicionales:**
- Git (control de versiones)
- Composer (gestor de dependencias PHP - opcional)
- Certbot (Let's Encrypt para SSL)

### Servidor de Desarrollo

**Stack recomendado:**
- **XAMPP 8.0+** (Apache, MySQL, PHP todo-en-uno)
  - Descarga: https://www.apachefriends.org/
- **Alternativas:**
  - WAMP (Windows)
  - MAMP (macOS)
  - Laravel Valet (macOS)
  - Docker con LAMP stack

**Herramientas de desarrollo:**
- Visual Studio Code (editor recomendado)
  - Extensiones: PHP Intelephense, MySQL, GitLens
- HeidiSQL / MySQL Workbench (gestión de BD)
- Postman (testing de APIs)
- Git Bash / Terminal

## 3.5 Dependencias y Librerías

### Back-end (PHP)

**Librerías incluidas en el proyecto:**

Ninguna librería externa de PHP es requerida actualmente. El proyecto utiliza:
- PDO nativo de PHP para base de datos
- Funciones nativas de PHP para hashing (password_hash, password_verify)
- $_SESSION nativo para manejo de sesiones

**Futuras dependencias recomendadas:**
```json
{
  "require": {
    "phpmailer/phpmailer": "^6.5",
    "dompdf/dompdf": "^2.0",
    "monolog/monolog": "^2.3"
  }
}
```

### Front-end (JavaScript / CSS)

**Librerías cargadas vía CDN:**

1. **Tailwind CSS** (Framework CSS)
   - Versión: Compilado localmente (input.css → output.css)
   - Uso: Todo el diseño responsive y utilidades
   - Configuración: `tailwind.config.js` (si existe)

2. **Ionicons** (Iconos)
   - Versión: 4.5.10-0
   - CDN: `https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js`
   - Uso: Iconos en toda la interfaz

3. **jsPDF** (Generación de PDFs)
   - Versión: 2.5.1
   - CDN: `https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js`
   - Uso: Generación de consentimientos e historiales médicos

4. **html2canvas** (Captura de elementos HTML)
   - Versión: 1.4.1
   - CDN: `https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js`
   - Uso: Captura de firmas y elementos para PDFs

5. **Signature Pad** (Captura de firmas)
   - Implementación: Canvas HTML5 nativo
   - No requiere librería externa (código personalizado)

### Base de Datos

**Motor:**
- MySQL 5.7+ / MariaDB 10.3+
- Storage Engine: InnoDB (transacciones ACID)
- Character Set: utf8mb4 (soporte completo de emojis y caracteres especiales)
- Collation: utf8mb4_general_ci

**Esquema:**
- Archivo: `schema.sql`
- Tablas: 13 tablas principales
- Índices: 25+ índices para optimización
- Foreign Keys: 10 relaciones definidas
- Triggers: Ninguno actualmente
- Stored Procedures: Ninguno actualmente

### Archivos Estáticos

**Imágenes:**
- Logo: `src/Images/logopropieel.png`
- Placeholders: Varios en `src/Images/`

**CSS Compilado:**
- `src/output.css` (Tailwind compilado, ~3MB sin purge)
- `src/input.css` (Tailwind source)

**Fuentes:**
- Sistema: Sans-serif nativa del navegador
- No se cargan fuentes externas (Google Fonts, etc.)

---

# 4. ARQUITECTURA DEL SISTEMA

## 4.1 Diagrama de Flujo General del Sistema

```
┌─────────────────────────────────────────────────────────────────┐
│                      SISTEMA PROPIELEQUIPO                      │
│                   Arquitectura General (MVC)                     │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐          ┌──────────────┐          ┌──────────────┐
│   CLIENTES   │          │   SERVIDOR   │          │  BASE DATOS  │
│              │          │  WEB (PHP)   │          │   (MySQL)    │
└──────────────┘          └──────────────┘          └──────────────┘
      │                          │                          │
      │  HTTP Request            │                          │
      │  (GET/POST)             │                          │
      ├─────────────────────────>│                          │
      │                          │                          │
      │                          │  1. Validar Sesión       │
      │                          │                          │
      │                          │  2. Query SQL            │
      │                          ├─────────────────────────>│
      │                          │                          │
      │                          │  3. Resultados           │
      │                          │<─────────────────────────┤
      │                          │                          │
      │                          │  4. Procesar Lógica      │
      │                          │     (PHP)                │
      │                          │                          │
      │  HTTP Response           │  5. Generar HTML         │
      │  (HTML/JSON)            │     o JSON                │
      │<─────────────────────────┤                          │
      │                          │                          │
      │  6. Renderizar UI        │                          │
      │     (JavaScript)         │                          │
      │                          │                          │
```

### Flujo de Datos Detallado:

**1. Autenticación (Login)**
```
Usuario ingresa credenciales
    ↓
POST → login.php
    ↓
Validar usuario en tabla `usuarios`
    ↓
Hash password vs BD (password_verify)
    ↓
¿Válido? → Crear $_SESSION con rol
    ↓
Redirigir a dashboard correspondiente
```

**2. Reserva de Cita (Paciente)**
```
Paciente selecciona especialidad
    ↓
GET → get_doctors.php?especialidad=X
    ↓
Lista de doctores con especialidad X
    ↓
Paciente selecciona doctor y fecha
    ↓
GET → get_available_hours.php?doctor_id=Y&fecha=Z
    ↓
Consulta tabla `horarios` + `citas` + `bloqueos_horarios`
    ↓
Retorna slots disponibles (JSON)
    ↓
Paciente elige hora y confirma
    ↓
POST → reservar.php (datos de cita)
    ↓
INSERT en tabla `citas` (estado: pendiente_pago)
    ↓
Redirigir a subir comprobante
```

**3. Verificación de Pago (Doctor)**
```
Doctor accede a verificar_pagos_*.php
    ↓
SELECT citas WHERE estado='pendiente_pago' AND id_doctor=X
    ↓
Muestra galería de comprobantes
    ↓
Doctor hace clic en "Verificar"
    ↓
POST → verificar_pago.php
    ↓
UPDATE citas SET estado='pendiente', verificado_por=X, fecha_verificacion=NOW()
    ↓
Cita confirmada, paciente notificado (futuro: email)
```

**4. Generación de PDF (Historial)**
```
Doctor/Paciente solicita historial
    ↓
JavaScript: generarHistorialPDF(paciente_id)
    ↓
GET → get_patient_history_*.php?patient_id=X
    ↓
SELECT citas WHERE id_usuario=X AND servicio='especialidad'
    ↓
Retorna JSON con array de citas
    ↓
JavaScript (cliente): jsPDF genera PDF
    ↓
Abre en nueva ventana / Descarga
```

## 4.2 Diagramas de Flujo por Rol

### 4.2.1 Flujo de Paciente

```
┌─────────────────────────────────────────────────────────────────┐
│                      FLUJO DE PACIENTE                          │
└─────────────────────────────────────────────────────────────────┘

INICIO
  │
  ├──> ¿Tiene cuenta?
  │         │
  │         ├─ NO ──> Registro (registrar.php)
  │         │              │
  │         │              ├─ Validar datos
  │         │              ├─ INSERT en usuarios (rol=3)
  │         │              └─ Hash contraseña
  │         │                      │
  │         └─ SÍ ─────────────────┘
  │                                │
  ├──> Login (login.php)           │
  │         │                      │
  │         ├─ Validar credenciales
  │         ├─ Crear sesión
  │         └─ Redirigir
  │                │
  ├──> Dashboard Paciente
  │         │
  │         ├──> [1] Reservar Cita
  │         │         │
  │         │         ├─ Seleccionar especialidad
  │         │         ├─ Elegir doctor
  │         │         ├─ Ver horarios disponibles
  │         │         ├─ Confirmar reserva
  │         │         │      │
  │         │         │      └──> Estado: pendiente_pago
  │         │         │
  │         │         └─ Subir Comprobante
  │         │                   │
  │         │                   ├─ Upload imagen
  │         │                   └─ Esperar verificación
  │         │
  │         ├──> [2] Ver Mis Citas
  │         │         │
  │         │         ├─ Próximas citas
  │         │         ├─ Historial
  │         │         └─ Cancelar cita
  │         │
  │         ├──> [3] Consentimiento Informado
  │         │         │
  │         │         ├─ Llenar formulario
  │         │         ├─ Firmar en canvas
  │         │         ├─ Generar PDF
  │         │         └─ Guardar
  │         │
  │         ├──> [4] Imágenes Médicas
  │         │         │
  │         │         ├─ Subir imagen
  │         │         ├─ Ver galería
  │         │         └─ Eliminar imagen
  │         │
  │         └──> [5] Cerrar Sesión
  │
FIN
```

### 4.2.2 Flujo de Doctor

```
┌─────────────────────────────────────────────────────────────────┐
│                       FLUJO DE DOCTOR                           │
└─────────────────────────────────────────────────────────────────┘

INICIO
  │
  ├──> Login (login.php)
  │         │
  │         ├─ Validar credenciales (rol=1)
  │         ├─ Cargar especialidades de doctor_especialidades
  │         └─ Crear sesión con array de especialidades
  │                │
  ├──> Seleccionar Especialidad
  │         │
  │         ├─ Dermatología (especialidad_id=1)
  │         ├─ Podología (especialidad_id=2)
  │         └─ Tamizaje (especialidad_id=3)
  │                │
  ├──> Dashboard Especialidad
  │         │
  │         ├──> [1] Verificar Pagos
  │         │         │
  │         │         ├─ Ver citas con comprobantes
  │         │         │      (estado: pendiente_pago)
  │         │         │
  │         │         ├─ Visualizar comprobante
  │         │         │
  │         │         ├──> ¿Aprobar?
  │         │         │       │
  │         │         │       ├─ SÍ ──> UPDATE estado='pendiente'
  │         │         │       │         Registrar verificador
  │         │         │       │
  │         │         │       └─ NO ──> UPDATE estado='rechazada'
  │         │         │                 Notas del rechazo
  │         │         │
  │         │         └─ Notificar paciente (futuro: email)
  │         │
  │         ├──> [2] Ver Citas
  │         │         │
  │         │         ├─ Citas del día
  │         │         ├─ Citas de la semana
  │         │         ├─ Filtros: fecha, estado, paciente
  │         │         │
  │         │         ├─ Marcar como completada
  │         │         │
  │         │         └─ Registrar observaciones médicas
  │         │
  │         ├──> [3] Pacientes
  │         │         │
  │         │         ├─ Lista de pacientes atendidos
  │         │         │      (filtrados por especialidad)
  │         │         │
  │         │         ├─ Ver historial completo
  │         │         │
  │         │         └─ Generar PDF de historial
  │         │
  │         ├──> [4] Imágenes Médicas
  │         │         │
  │         │         └─ Ver galería de todos los pacientes
  │         │
  │         ├──> [5] Cambiar Especialidad
  │         │         │
  │         │         └─ Switch a otra especialidad
  │         │                (si doctor tiene múltiples)
  │         │
  │         └──> [6] Cerrar Sesión
  │
FIN
```

### 4.2.3 Flujo de Administrador

```
┌─────────────────────────────────────────────────────────────────┐
│                    FLUJO DE ADMINISTRADOR                       │
└─────────────────────────────────────────────────────────────────┘

INICIO
  │
  ├──> Login Admin (login_admin.php)
  │         │
  │         ├─ Validar credenciales (rol=4)
  │         ├─ Crear sesión admin
  │         └─ Redirigir
  │                │
  ├──> Dashboard Admin
  │         │
  │         ├──> [1] Gestionar Horarios
  │         │         │
  │         │         ├─ TAB 1: Horarios Semanales
  │         │         │      │
  │         │         │      ├─ Seleccionar doctor
  │         │         │      ├─ Configurar día de semana
  │         │         │      ├─ Definir hora_inicio, hora_fin
  │         │         │      ├─ Establecer intervalo_minutos
  │         │         │      ├─ Guardar (upsert en tabla horarios)
  │         │         │      │
  │         │         │      ├─ Aplicar a toda la semana
  │         │         │      ├─ Activar/Desactivar horario
  │         │         │      └─ Eliminar horario
  │         │         │
  │         │         └─ TAB 2: Bloqueos de Horarios
  │         │                │
  │         │                ├─ Seleccionar doctor
  │         │                ├─ Definir tipo_bloqueo
  │         │                │    (vacaciones, conferencia, personal)
  │         │                ├─ Establecer fecha_inicio, fecha_fin
  │         │                ├─ Hora inicio/fin (opcional)
  │         │                ├─ Motivo del bloqueo
  │         │                ├─ Guardar bloqueo
  │         │                └─ Eliminar bloqueo
  │         │
  │         ├──> [2] Configuración de Pagos
  │         │         │
  │         │         ├─ Actualizar datos bancarios
  │         │         │    (banco, titular, CLABE)
  │         │         │
  │         │         └─ Guardar en configuracion_pagos
  │         │
  │         ├──> [3] Ver Estadísticas
  │         │         │
  │         │         ├─ Total doctores/pacientes
  │         │         ├─ Citas del mes
  │         │         ├─ Pagos pendientes/verificados
  │         │         └─ Gráficos (futuro)
  │         │
  │         ├──> [4] Gestionar Usuarios (futuro)
  │         │         │
  │         │         ├─ Activar/Desactivar usuarios
  │         │         ├─ Asignar especialidades a doctores
  │         │         └─ Resetear contraseñas
  │         │
  │         └──> [5] Cerrar Sesión
  │
FIN
```

---

**FIN DE PARTE 1**

---

*Este documento continúa en:*
- **MANUAL_TECNICO_PARTE_2.md** (Diagramas UML, Secuencia, Despliegue, Modelo de Datos)
- **MANUAL_TECNICO_PARTE_3.md** (Código fuente, Módulos críticos, Pruebas)

---

**Elaborado por:** Equipo de Desarrollo PropielEquipo  
**Revisión:** v1.0 - Noviembre 2025  
**Contacto Técnico:** desarrollo@propielequipo.com
