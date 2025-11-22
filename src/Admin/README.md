# Panel de Administración - PropielEquipo

## 📋 Descripción
Dashboard administrativo para gestionar el sistema de pagos, usuarios y configuraciones del sistema PropielEquipo.

## 🚀 Configuración Inicial

### 1. Ejecutar Migraciones
Primero, ejecuta la migración principal que incluye:
- Creación del rol Admin (rol = 4)
- Usuario administrador por defecto
- Sistema de pagos SPEI
- Tablas de configuración

```bash
mysql -u root -p propielequipo < migration_payment_system.sql
```

### 2. Credenciales de Acceso

#### Usuario Administrador por Defecto:
- **Usuario:** `admin`
- **Contraseña:** `admin123`

⚠️ **IMPORTANTE:** Cambia esta contraseña inmediatamente después del primer acceso.

#### Para cambiar la contraseña del admin:
```sql
-- Genera un nuevo hash con PHP
php -r "echo password_hash('tu_nueva_contraseña', PASSWORD_DEFAULT);"

-- Actualiza en la base de datos
UPDATE usuarios 
SET password = 'HASH_GENERADO_ARRIBA' 
WHERE telefono = 'admin';
```

### 3. Configurar Datos Bancarios

Una vez que inicies sesión:
1. Ve a **Configuración de Pagos** en el menú
2. Actualiza los siguientes campos:
   - Nombre del Banco
   - Titular de la Cuenta
   - CLABE Interbancaria (18 dígitos)
   - Número de Cuenta
   - Información de Referencia (opcional)

Los pacientes verán esta información al subir sus comprobantes de pago.

### 4. Crear Directorio de Comprobantes

```bash
# En Windows (CMD)
mkdir src\comprobantes_pago

# En Windows (PowerShell)
New-Item -ItemType Directory -Path "src\comprobantes_pago"

# En Linux/Mac
mkdir src/comprobantes_pago
chmod 755 src/comprobantes_pago
```

## 🔗 Acceso al Panel

### URL Local:
```
http://localhost/PropielEquipo/src/Admin/login_admin.php
```

### URL Producción:
```
https://tudominio.com/src/Admin/login_admin.php
```

## 📊 Funcionalidades Principales

### ✅ Dashboard Principal
- Estadísticas del sistema (doctores, pacientes, citas)
- Pagos pendientes de verificación
- Ingresos del mes
- Últimos registros de pacientes y citas
- Accesos rápidos a todas las funciones

### ✅ Configuración de Pagos
- Editar datos bancarios para SPEI
- Vista previa en tiempo real
- Validación de CLABE (18 dígitos)
- Información de referencia personalizable

### 🔜 Gestión de Doctores (Próximamente)
- Listar todos los doctores
- Activar/Desactivar cuentas
- Asignar especialidades
- Ver estadísticas individuales

### 🔜 Gestión de Pacientes (Próximamente)
- Listar todos los pacientes
- Ver historial de citas
- Activar/Desactivar cuentas
- Estadísticas de uso

### 🔜 Reportes de Pagos (Próximamente)
- Tabla de pagos completa
- Filtros por fecha, doctor, estado
- Exportar reportes
- Totales y estadísticas

## 🔐 Seguridad

### Verificación de Sesión
Todas las páginas verifican:
- Sesión activa del administrador
- Rol correcto (rol = 4)
- Protección contra acceso no autorizado

### Validaciones
- CLABE: 18 dígitos numéricos obligatorios
- Campos bancarios: validación de campos requeridos
- Sesiones: timeout y destrucción segura

### Recomendaciones
1. ⚠️ Cambia la contraseña de admin inmediatamente
2. 🔒 Usa HTTPS en producción
3. 📝 Mantén logs de cambios importantes
4. 🔑 No compartas las credenciales de admin
5. 💾 Realiza backups regulares de la base de datos

## 🛠️ Estructura del Sistema

### Roles en la Base de Datos
```sql
-- user_type table
1 = Doctor
2 = Enfermera
3 = Paciente
4 = Admin (Nuevo)
```

### Archivos Principales
```
src/Admin/
├── login_admin.php              # Página de login
├── dashboard_admin.php          # Dashboard principal
├── configuracion_pagos.php      # Configurar datos bancarios
├── shared/
│   └── navbar_admin.php         # Navegación compartida
└── php_action/
    ├── login_admin.php          # Autenticación
    ├── actualizar_datos_bancarios.php  # Actualizar banco
    └── logout_admin.php         # Cerrar sesión
```

## 📞 Soporte

Si encuentras problemas:
1. Verifica que la migración se ejecutó correctamente
2. Revisa los logs de PHP (`php_error.log`)
3. Confirma que el usuario admin existe en la BD:
   ```sql
   SELECT * FROM usuarios WHERE rol = 4;
   ```

## 📝 Changelog

### v1.0.0 (2025-11-15)
- ✅ Sistema de login administrativo
- ✅ Dashboard con estadísticas
- ✅ Configuración de pagos SPEI
- ✅ Navegación responsive
- ✅ Validación de CLABE
- ✅ Vista previa en tiempo real
- ✅ Manejo de sesiones seguras

### Próximas Versiones
- ⏳ Gestión completa de doctores
- ⏳ Gestión completa de pacientes
- ⏳ Reportes y exportación
- ⏳ Logs de auditoría
- ⏳ Cambio de contraseña desde panel
