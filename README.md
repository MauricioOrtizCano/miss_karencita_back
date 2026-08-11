# Miss Karencita — Backend

API en Laravel para la captura de _leads_ del landing de **Miss Karencita**, un servicio de planes de estimulación temprana para bebés diseñados por una Licenciada en Educación Infantil.

En esta primera etapa el objetivo es **validar la demanda**: el landing recoge los datos de contacto de las familias interesadas y este backend los almacena de forma segura y con el consentimiento correspondiente. La aplicación completa (caracterización, mallas de actividades, seguimiento) se construirá en una fase posterior.

---

## Tabla de contenido

- [Stack](#stack)
- [Requisitos previos](#requisitos-previos)
- [Instalación](#instalación)
- [Variables de entorno](#variables-de-entorno)
- [Base de datos](#base-de-datos)
- [Modelo de dominio](#modelo-de-dominio)
- [API](#api)
- [Consideraciones legales](#consideraciones-legales)
- [Pruebas](#pruebas)
- [Roadmap](#roadmap)

---

## Stack

- **PHP** 8.2+
- **Laravel** 11+
- **MariaDB** 10.6+
- **Composer** 2.x

El frontend (landing) se desarrolla por separado en **Next.js / React** y consume esta API.

---

## Requisitos previos

Antes de empezar necesitas tener instalado:

- PHP 8.2 o superior con las extensiones habituales de Laravel (`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`)
- Composer
- MariaDB (o MySQL) corriendo localmente o en un servidor accesible

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone [URL_DEL_REPOSITORIO]
cd [NOMBRE_DEL_PROYECTO]

# 2. Instalar dependencias
composer install

# 3. Crear el archivo de entorno
cp .env.example .env

# 4. Generar la clave de la aplicación
php artisan key:generate

# 5. Configurar la base de datos en .env (ver sección siguiente)

# 6. Ejecutar migraciones
php artisan migrate

# 7. Levantar el servidor de desarrollo
php artisan serve
```

La API quedará disponible en `http://localhost:8000`.

---

## Variables de entorno

Los valores más relevantes en el archivo `.env`:

```env
APP_NAME="Miss Karencita"
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=miss_karencita
DB_USERNAME=[TU_USUARIO]
DB_PASSWORD=[TU_CONTRASEÑA]

# Origen permitido para el landing (CORS)
FRONTEND_URL=http://localhost:3000

# Versión vigente de la política de tratamiento de datos.
# Se guarda junto con cada consentimiento para tener trazabilidad.
POLITICA_DATOS_VERSION=v1.0
```

> **CORS:** como el landing (Next.js) corre en un dominio distinto, recuerda configurar `config/cors.php` para permitir el origen definido en `FRONTEND_URL`.

---

## Base de datos

El proyecto usa las migraciones de Laravel. Comandos útiles:

```bash
php artisan migrate           # Aplicar migraciones pendientes
php artisan migrate:fresh     # Recrear la base desde cero (borra datos)
php artisan migrate:rollback  # Revertir el último lote
```

---

## Modelo de dominio

Dos entidades con una relación **uno a muchos**: un acudiente puede tener uno o varios niños a cargo.

### `acudientes`

Quien llena el formulario y queda como contacto del _lead_. Se usa el término _acudiente_ (no _padre_) porque quien diligencia suele ser la madre, un cuidador o un familiar.

| Campo                 | Tipo                                            | Notas                                                        |
| --------------------- | ----------------------------------------------- | ------------------------------------------------------------ |
| `id`                  | bigint                                          | PK                                                           |
| `nombre`              | string                                          |                                                              |
| `correo`              | string, nullable                                | Al menos uno de `correo` o `telefono` debe venir             |
| `telefono`            | string, nullable                                | Al menos uno de `correo` o `telefono` debe venir             |
| `mensaje`             | text, nullable                                  | "¿Qué te gustaría lograr?" (opcional)                        |
| `estado`             | enum                                            | `nuevo` · `contactado` · `convertido` · `descartado` (embudo)|
| `autorizacion_datos`  | boolean                                         | Debe ser `true` para poder registrar el lead                 |
| `fecha_autorizacion`  | timestamp, nullable                             | Momento exacto del consentimiento                            |
| `version_politica`    | string, nullable                                | Versión de la política aceptada (trazabilidad)               |
| `created_at`          | timestamp                                       |                                                              |
| `updated_at`          | timestamp                                       |                                                              |

### `ninos`

Los bebés asociados a un acudiente. Se evita la _ñ_ en el nombre de la tabla para no arrastrar problemas de encoding en migraciones y exports.

| Campo          | Tipo                | Notas                                                      |
| -------------- | ------------------- | ---------------------------------------------------------- |
| `id`           | bigint              | PK                                                         |
| `acudiente_id` | bigint, FK          | Referencia a `acudientes.id`                               |
| `nombre`       | string              |                                                            |
| `rango_edad`   | enum                | `0-6` · `7-12` · `13-24` · `25-36` (meses)                 |
| `created_at`   | timestamp           |                                                            |
| `updated_at`   | timestamp           |                                                            |

> **Sobre la edad:** en el landing se pide un **rango de edad** en lugar de la fecha de nacimiento, para reducir fricción y minimizar el dato sensible de un menor. La fecha de nacimiento exacta se capturará más adelante, cuando la familia sea cliente y firme la autorización completa.

### Relaciones (Eloquent)

- `Acudiente` **hasMany** `Nino`
- `Nino` **belongsTo** `Acudiente`

---

## API

Prefijo base: `/api`

### `POST /api/leads`

Registra un nuevo _lead_ (acudiente + uno o más niños) desde el formulario del landing.

**Cuerpo de la petición (ejemplo):**

```json
{
  "nombre": "Laura Gómez",
  "correo": "laura@example.com",
  "telefono": null,
  "mensaje": "Quiero acompañar mejor el desarrollo de mi bebé.",
  "autorizacion_datos": true,
  "ninos": [
    { "nombre": "Emmanuel", "rango_edad": "13-24" }
  ]
}
```

**Reglas de validación principales:**

- `nombre`: requerido
- `correo` / `telefono`: al menos uno de los dos es obligatorio
- `autorizacion_datos`: requerido y debe ser `true` (sin autorización no se registra)
- `ninos`: arreglo con al menos un elemento; cada niño requiere `nombre` y `rango_edad` válido

Al aceptar el consentimiento, el backend registra automáticamente `fecha_autorizacion` (marca de tiempo del servidor) y `version_politica` (según `POLITICA_DATOS_VERSION`). Estos valores **no** deben confiarse al cliente.

**Respuestas:**

- `201 Created` — lead registrado correctamente
- `422 Unprocessable Entity` — errores de validación

### Endpoints administrativos (fase siguiente)

El listado y la gestión de leads para la Licenciada (por ejemplo `GET /api/admin/leads`, cambio de `estado`) requerirán autenticación y se documentarán al implementarse.

---

## Consideraciones legales

Este proyecto trata **datos personales de menores de edad**, categoría con protección reforzada bajo la **Ley 1581 de 2012** y sus decretos reglamentarios en Colombia. Puntos a respetar en el código y la operación:

- El consentimiento del acudiente es **obligatorio** antes de almacenar cualquier dato. La casilla de autorización en el formulario nace **desmarcada**.
- Se guarda evidencia del consentimiento: `autorizacion_datos`, `fecha_autorizacion` y `version_politica`.
- Debe existir una **política de tratamiento de datos** publicada y enlazada desde el formulario.
- Los datos no se comparten con terceros sin autorización expresa.
- El servicio es un acompañamiento pedagógico: **no diagnostica ni sustituye** la valoración médica o terapéutica.

> Esta sección es una guía técnica, no asesoría jurídica. Conviene validar el cumplimiento con un profesional en protección de datos antes de salir a producción.

---

## Pruebas

```bash
php artisan test
```

Se recomienda cubrir como mínimo:

- Registro exitoso de un lead con uno y con varios niños
- Rechazo cuando falta la autorización de datos
- Rechazo cuando no se envía ni correo ni teléfono
- Registro automático de `fecha_autorizacion` y `version_politica`

---

## Roadmap

Esta API es la base de un producto más amplio. Fases previstas:

1. **Captura de leads** (etapa actual) — validar demanda.
2. **Caracterización** del bebé por áreas del desarrollo (versionada por periodo).
3. **Mallas de actividades** personalizadas creadas por la Licenciada.
4. **Registro y seguimiento** de actividades por parte del acudiente.
5. **Panel administrativo** y asistente de IA para apoyar a la Licenciada.

---

_Proyecto de [TU_NOMBRE] — [AÑO]._
