# Laravel + Vue.js Geolocation Reminder App

Este repositorio contiene el setup inicial para una prueba técnica que involucra el desarrollo de una aplicación con Laravel (API) y Vue.js (frontend) con Vuetify (versión 2). La aplicación permitirá registrar recordatorios basados en ubicaciones geográficas y notificará cuando el usuario entre en un radio específico de una ubicación.

* Para simular la ubicación actual de un usuario podemos crear un modelo que nos permita establecer el valor manualmente.
* La aplicación debe ser reactiva.

## Requisitos

- Docker y Docker Compose (opcional pero recomendado).
- PHP >= 8.0.
- Composer.
- Node.js >= 16.x.
- NPM o Yarn.
- MySQL.

## Instrucciones de Instalación

### Backend (Laravel)

1. Clona el repositorio:
   ```bash
   git clone <repo-url> laravel-vue-geolocation
   cd laravel-vue-geolocation
   ```

2. Configura el backend:
   ```bash
   cd backend
   cp .env.example .env
   composer install
   php artisan key:generate
   ```

3. Configura la base de datos en el archivo `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=laravel_vue_geolocation
   DB_USERNAME=root
   DB_PASSWORD=root
   ```

4. Ejecuta las migraciones:
   ```bash
   php artisan migrate
   ```

5. Inicia el servidor:
   ```bash
   php artisan serve
   ```

El backend estará disponible en `http://127.0.0.1:8000`.

### Frontend (Vue.js)

1. Ve a la carpeta del frontend:
   ```bash
   cd ../frontend
   ```

2. Instala las dependencias:
   ```bash
   npm install
   ```

3. Inicia el servidor de desarrollo:
   ```bash
   npm run serve
   ```

El frontend estará disponible en `http://localhost:8080`.

## Instalación de Extensiones PHP Necesarias

Durante la instalación, puedes encontrar errores relacionados con extensiones faltantes como `ext-xml` o `ext-dom`. Sigue estos pasos para habilitarlas:

### **Linux (Ubuntu/Debian):**
1. Instala las extensiones XML y DOM:
   ```bash
   sudo apt update
   sudo apt install php-xml
   ```

2. Verifica que las extensiones estén habilitadas ejecutando:
   ```bash
   php -m | grep -E 'xml|dom'
   ```

3. Reinicia tu servidor web (si estás utilizando Apache o Nginx):
   ```bash
   sudo systemctl restart apache2
   sudo systemctl restart nginx
   ```

### **Windows:**
1. Edita el archivo `php.ini`. Por lo general, se encuentra en la carpeta donde instalaste PHP (por ejemplo, `C:\xampp\php\php.ini` o `C:\php\php.ini`).

2. Busca las siguientes líneas y asegúrate de que no tengan un punto y coma `;` al inicio:
   ```ini
   extension=xml
   extension=dom
   ```

3. Guarda los cambios y reinicia tu servidor web.

### **macOS (usando Homebrew):**
1. Instala la extensión de PHP XML:
   ```bash
   brew install php
   ```

2. Verifica que las extensiones estén habilitadas:
   ```bash
   php -m | grep -E 'xml|dom'
   ```

3. Reinicia cualquier servidor web que estés utilizando.

### Limpiar y reinstalar dependencias con Composer
Una vez habilitadas las extensiones, limpia el caché de Composer y reinstala las dependencias:
```bash
composer clear-cache
composer install
```

### Verificar la instalación
Puedes verificar si todo está funcionando ejecutando:
```bash
php artisan
```

## Estructura del Proyecto

```
laravel-vue-geolocation/
├── backend/          # API construida con Laravel
├── frontend/         # Aplicación frontend con Vue.js + Vuetify
```

### Backend (Laravel)

#### Rutas
Las rutas iniciales se definen en `routes/api.php`.

- `POST /reminders`: Crea un recordatorio con un título, ubicación (lat, long) y radio.
- `GET /reminders`: Lista todos los recordatorios.
- `PUT /reminders/{id}`: Actualiza un recordatorio.
- `DELETE /reminders/{id}`: Elimina un recordatorio.

#### Modelo de Base de Datos
```sql
CREATE TABLE reminders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    radius INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Frontend (Vue.js)

#### Configuración Inicial
El proyecto incluye:

- Vuetify 2.
- Axios para solicitudes HTTP.
- Configuración básica de rutas con Vue Router.

#### Componentes Básicos

1. **Formulario de Recordatorios**: Para agregar o editar recordatorios.
2. **Mapa**: Usando una librería como Leaflet para seleccionar ubicaciones geográficas.
3. **Lista de Recordatorios**: Tabla o lista para mostrar recordatorios almacenados.

## Notas para el Postulante

- Puedes usar cualquier librería adicional que consideres necesaria.
- La funcionalidad principal es:
  - Registrar recordatorios con título, coordenadas (latitud y longitud) y un radio.
  - Recibir una notificación (simulada en el frontend) al "entrar" dentro del radio de una ubicación.
- Si tienes dudas o preguntas, documenta tus decisiones en un archivo `NOTES.md`.

¡Buena suerte!

---

## Comandos Útiles

### Backend
- Ejecutar migraciones: `php artisan migrate`
- Ejecutar pruebas: `php artisan test`

### Frontend
- Compilar para producción: `npm run build`
- Linter: `npm run lint`
