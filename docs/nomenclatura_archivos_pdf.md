# Sistema de Nomenclatura de Archivos PDF - PropielEquipo
# Consentimientos Informados con Nombres Legibles

## Formato Anterior (ID de usuario):
```
consentimiento_cita_1_usuario_18_2025-07-30_07-43-43.pdf
```

## Formato Nuevo (Nombre del paciente):

### Frontend (JavaScript - descarga directa):
```
consentimiento_Juan_Perez_dermatologia_2025-07-30_09-00_2025-07-30T09-43-25.pdf
consentimiento_Maria_Rodriguez_podologia_2025-07-31_10-30_2025-07-30T09-45-12.pdf
consentimiento_Carlos_Mendoza_tamiz_2025-08-01_14-00_2025-07-30T09-47-33.pdf
```

### Backend (PHP - almacenamiento en servidor):
```
consentimiento_Juan_Perez_cita_1_2025-07-30_09-43-25.pdf
consentimiento_Maria_Rodriguez_cita_2_2025-07-30_09-45-12.pdf
consentimiento_Carlos_Mendoza_cita_3_2025-07-30_09-47-33.pdf
```

## Ventajas del Nuevo Sistema:

### 1. **Legibilidad Mejorada**
- ✅ Nombres comprensibles para personal médico
- ✅ Fácil identificación de pacientes
- ✅ Información del servicio incluida

### 2. **Organización Profesional**
- ✅ Ordenamiento alfabético por apellido
- ✅ Búsqueda rápida por nombre de paciente
- ✅ Identificación inmediata del tipo de consulta

### 3. **Trazabilidad Completa**
- ✅ Fecha y hora de la cita
- ✅ Timestamp de generación del documento
- ✅ ID de cita para referencia en base de datos

### 4. **Compatibilidad y Seguridad**
- ✅ Caracteres especiales eliminados
- ✅ Espacios convertidos a guiones bajos
- ✅ Formato compatible con todos los sistemas de archivos

## Ejemplos de Uso por Especialidad:

### Dermatología:
```
consentimiento_Ana_Garcia_dermatologia_2025-07-30_09-00_2025-07-30T09-43-25.pdf
```

### Podología:
```
consentimiento_Luis_Martinez_podologia_2025-07-30_11-30_2025-07-30T11-15-42.pdf
```

### Tamizaje:
```
consentimiento_Sofia_Lopez_tamiz_2025-07-30_15-00_2025-07-30T14-52-18.pdf
```

## Implementación Técnica:

### JavaScript (Frontend):
- Limpieza de caracteres especiales
- Inclusión de servicio y horario de cita
- Timestamp ISO con formato compatible

### PHP (Backend):
- Obtención de datos del paciente de la base de datos
- Validación y limpieza de nombres
- Fallback a ID de usuario si no hay nombre disponible
- Logging detallado para auditoría

## Beneficios para el Personal Médico:

1. **Identificación Rápida**: Ver inmediatamente de quién es el consentimiento
2. **Organización Eficiente**: Archivos ordenados por nombre de paciente
3. **Búsqueda Simplificada**: Encontrar documentos por nombre sin necesidad de consultar IDs
4. **Profesionalismo**: Nombres de archivo que reflejan un sistema médico organizado
5. **Auditoría Clara**: Trazabilidad completa con información legible

## Compatibilidad:
- ✅ Windows, macOS, Linux
- ✅ Todos los navegadores modernos  
- ✅ Sistemas de gestión de archivos médicos
- ✅ Herramientas de backup y sincronización
