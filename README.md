# PropielEquipo - Sistema de Gestión Médica

Sistema de gestión de citas médicas para especialidades de Dermatología, Podología y Tamizaje.

## 🚀 Características

- **Sistema de Citas**: Reserva y gestión de citas médicas
- **Perfiles de Usuario**: Pacientes y Médicos con roles diferenciados
- **Especialidades Médicas**: Dermatología, Podología, Tamizaje
- **Consentimientos Informados**: Firma digital y almacenamiento de PDFs
- **Imágenes Médicas**: Carga y gestión de imágenes diagnósticas
- **Panel de Doctor**: Dashboard especializado por especialidad con búsqueda en tiempo real
- **Asignación de Doctores**: Sistema automático/manual de asignación de doctores a citas

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior / MariaDB 10.3+
- Apache Web Server (XAMPP, WAMP, LAMP, etc.)
- Extensiones PHP requeridas:
  - PDO
  - pdo_mysql
  - gd (para manejo de imágenes)
  - mbstring

## 🛠️ Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/YOUR_USERNAME/PropielEquipo.git
cd PropielEquipo
```

### 2. Configurar Base de Datos

1. Crear base de datos en MySQL:
```sql
CREATE DATABASE propielequipo CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

2. Importar el schema:
```bash
mysql -u root -p propielequipo < currentdb.sql
```

3. Configurar conexión:
```bash
# Copiar plantilla de configuración
cp src/database_connection.template.php src/database_connection.php

# Editar con tus credenciales
# En Windows: notepad src/database_connection.php
# En Linux/Mac: nano src/database_connection.php
```

Actualizar las credenciales en `database_connection.php`:
```php
private $host = "localhost";
private $db_name = "propielequipo";
private $username = "root";
private $password = ""; // Tu contraseña de MySQL
```

### 3. Permisos de Carpetas (Linux/Mac)

```bash
chmod 755 src/Images/ImgMedicas
chmod 755 src/consentimientos
chmod 755 src/logs
```

### 4. Configurar Tailwind CSS (Opcional)

Si necesitas recompilar los estilos:

```bash
npm install
npm run build
```

### 5. Acceder al Sistema

Abrir en navegador:
```
http://localhost/PropielEquipo/
```

## 👥 Usuarios de Prueba

Después de importar la base de datos, puedes usar estos usuarios:

### Doctores:
- **Dermatología**: Dr. Hugo Alarcón - Tel: `7581000101` / Pass: (define tu password)
- **Podología**: Dra. Maria Castro - Tel: `7581000202` / Pass: (define tu password)
- **Tamizaje**: Dr. Mario Alarcon - Tel: `7581000303` / Pass: (define tu password)
- **Tamizaje**: Dr. Pablo Cortez - Tel: `7581000404` / Pass: (define tu password)

### Pacientes:
- Juan Tellez - Tel: `1231231234` / Pass: (define tu password)

## 📁 Estructura del Proyecto

```
PropielEquipo/
├── index.html                    # Página principal
├── currentdb.sql                 # Schema de base de datos
├── src/
│   ├── database_connection.php   # Configuración de BD (no en Git)
│   ├── database_queries.php      # Clase de consultas
│   ├── input.css                 # Tailwind source
│   ├── output.css                # CSS compilado
│   ├── Landing/                  # Páginas de landing
│   │   ├── login.html
│   │   ├── registrar.html
│   │   └── php_action/
│   ├── Paciente/                 # Dashboard de pacientes
│   │   ├── dashboardpaciente.php
│   │   ├── reservar.php
│   │   ├── Citas.php
│   │   └── php_action/
│   ├── Doctor/                   # Dashboard de doctores
│   │   ├── dashboarddoc.php
│   │   ├── especialidades/
│   │   │   ├── dermatologia/
│   │   │   ├── podologia/
│   │   │   └── tamizaje/
│   │   └── shared/
│   │       └── navbar_doctor.php
│   ├── consentimientos/          # PDFs firmados
│   │   ├── view_pdf.php          # Visor seguro
│   │   └── .htaccess             # Protección
│   └── Images/
│       └── ImgMedicas/           # Imágenes médicas
└── docs/                         # Documentación
```

## 🔒 Seguridad

- Contraseñas hasheadas con `password_hash()` (bcrypt)
- Sesiones PHP para autenticación
- Prepared statements PDO (protección SQL injection)
- Validación de roles en cada página
- `.htaccess` protege archivos sensibles
- Verificación de permisos en acceso a archivos

## 🚀 Deployment

### Para Hosting Compartido:

1. Subir archivos vía FTP
2. Crear base de datos en cPanel/Plesk
3. Importar `currentdb.sql`
4. Actualizar `database_connection.php` con credenciales del hosting
5. Verificar permisos de carpetas (755 para directorios, 644 para archivos)

### Variables a Actualizar:

- Rutas absolutas si el proyecto no está en la raíz
- Host de base de datos (puede ser diferente a `localhost`)
- URLs en archivos de redirección

## 🐛 Troubleshooting

### Error: "Connection error"
- Verificar credenciales en `database_connection.php`
- Comprobar que MySQL esté corriendo
- Revisar que la base de datos existe

### Imágenes no se cargan
- Verificar permisos en `src/Images/ImgMedicas/`
- Comprobar espacio en disco
- Revisar límites de PHP: `upload_max_filesize`, `post_max_size`

### PDFs no se muestran
- Verificar permisos en `src/consentimientos/`
- Comprobar configuración de `.htaccess`
- Revisar logs de Apache/PHP

## 📝 Desarrollo

### Branches Recomendados:
- `main` - Producción estable
- `develop` - Desarrollo activo
- `feature/*` - Nuevas características
- `hotfix/*` - Correcciones urgentes

### Workflow:
```bash
# Crear nueva característica
git checkout -b feature/nueva-funcionalidad

# Hacer commits
git add .
git commit -m "feat: descripción de la funcionalidad"

# Subir a GitHub
git push origin feature/nueva-funcionalidad

# Crear Pull Request en GitHub
```

## 📄 Licencia

[Definir licencia]

## 👨‍💻 Autores

- Juan Tellez - Desarrollo inicial

## 🤝 Contribuir

1. Fork el proyecto
2. Crear rama feature (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add: nueva característica'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir Pull Request

---

**Nota**: Este proyecto fue desarrollado como sistema de gestión médica. Asegurar cumplimiento con regulaciones locales de protección de datos médicos antes de usar en producción.
