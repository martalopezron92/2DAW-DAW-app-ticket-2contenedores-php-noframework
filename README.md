# 🎫 Sistema de Tickets - Aplicación Web con PHP y Docker

Una aplicación web completa de gestión de tickets desarrollada con PHP, MariaDB y Docker. Este proyecto está diseñado con fines educativos para demostrar el desarrollo de aplicaciones web modernas usando contenedores.

## 📑 Índice

1. [Introducción](#introducción)
2. [¿Por qué Docker?](#por-qué-docker)
3. [Arquitectura del Proyecto](#arquitectura-del-proyecto)
4. [Requisitos Previos](#requisitos-previos)
5. [Instalación y Configuración](#instalación-y-configuración)
6. [Estructura del Proyecto](#estructura-del-proyecto)
7. [Explicación Técnica Detallada](#explicación-técnica-detallada)
8. [Guía de Desarrollo Local](#guía-de-desarrollo-local)
9. [Despliegue en Servidor Real](#despliegue-en-servidor-real)
10. [Errores Típicos y Soluciones](#errores-típicos-y-soluciones)
11. [Comandos Útiles](#comandos-útiles)
12. [Seguridad en Producción](#seguridad-en-producción)
13. [Características Implementadas](#características-implementadas)
14. [Tecnologías Utilizadas](#tecnologías-utilizadas)

---

## 🎯 Introducción

Este proyecto es una aplicación web de gestión de tickets (helpdesk) que permite a los usuarios:
- Iniciar sesión de forma segura
- Crear tickets (reportar problemas o solicitudes)
- Ver lista de tickets con filtros
- Consultar detalles de tickets
- Cerrar tickets

La aplicación está completamente containerizada usando Docker, lo que facilita su desarrollo, despliegue y mantenimiento.

---

## 🐳 ¿Por qué Docker?

### El problema sin Docker

Imagina que desarrollas una aplicación en tu ordenador. Funciona perfectamente. Cuando intentas ejecutarla en el ordenador de un compañero o en un servidor, surgen problemas:

- ❌ "En mi máquina funciona" (pero no en la tuya)
- ❌ Versión diferente de PHP instalada
- ❌ Extensiones de PHP faltantes
- ❌ Configuración de Apache diferente
- ❌ Base de datos no configurada correctamente

### La solución con Docker

Docker **encapsula** la aplicación y todas sus dependencias en **contenedores**. Un contenedor es como una caja cerrada que contiene:
- El sistema operativo base
- PHP con la versión exacta
- Apache configurado
- Todas las extensiones necesarias
- Tu código

**Ventajas:**
- ✅ **Portabilidad**: funciona igual en cualquier máquina con Docker
- ✅ **Aislamiento**: no interfiere con otras aplicaciones
- ✅ **Reproducibilidad**: mismo entorno en desarrollo y producción
- ✅ **Facilidad**: un comando (`docker compose up`) y todo funciona
- ✅ **Limpieza**: fácil de borrar sin dejar rastros

### ¿Por qué 2 contenedores?

En este proyecto usamos **2 contenedores separados**:

1. **Contenedor `app`**: PHP + Apache + código de la aplicación
2. **Contenedor `db`**: MariaDB + base de datos

**Razones para separarlos:**
- **Principio de responsabilidad única**: cada contenedor hace una cosa
- **Escalabilidad**: podrías tener múltiples contenedores de app y uno de DB
- **Mantenimiento**: actualizar PHP no afecta a la base de datos
- **Seguridad**: la BD no está expuesta directamente al exterior
- **Reutilización**: podrías usar la misma BD para otra aplicación

---

## 🏗️ Arquitectura del Proyecto

```
┌─────────────────────────────────────────────────┐
│              USUARIO (Navegador)                 │
└─────────────────┬───────────────────────────────┘
                  │ http://localhost:8080
                  ▼
┌─────────────────────────────────────────────────┐
│         HOST (tu ordenador Windows)              │
│  ┌───────────────────────────────────────────┐  │
│  │    Docker Network (ticketing-network)     │  │
│  │                                            │  │
│  │  ┌─────────────────┐  ┌────────────────┐ │  │
│  │  │  Contenedor APP │  │ Contenedor DB  │ │  │
│  │  │                 │  │                │ │  │
│  │  │  PHP 8.2        │  │  MariaDB 11.2  │ │  │
│  │  │  Apache         │◄─┤  ticketing DB  │ │  │
│  │  │  Puerto 80      │  │  Puerto 3306   │ │  │
│  │  │                 │  │                │ │  │
│  │  │  /var/www/html  │  │  /var/lib/mysql│ │  │
│  │  │  (montado desde │  │  (volumen      │ │  │
│  │  │   ./src)        │  │   persistente) │ │  │
│  │  └─────────────────┘  └────────────────┘ │  │
│  └───────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
```

**Flujo de una petición:**
1. Usuario abre navegador → http://localhost:8080/public/login.php
2. Docker mapea puerto 8080 del host → puerto 80 del contenedor `app`
3. Apache recibe la petición → PHP procesa login.php
4. PHP necesita datos → se conecta al contenedor `db` usando hostname `db`
5. MariaDB devuelve datos → PHP genera HTML
6. Apache devuelve HTML → navegador lo muestra

---

## 📋 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

- **Docker Desktop**: [Descargar para Windows](https://www.docker.com/products/docker-desktop)
- **Git** (opcional): para clonar el repositorio
- **Editor de código**: VS Code, Sublime Text, etc.

### Verificar instalación de Docker

Abre PowerShell y ejecuta:

```powershell
docker --version
docker compose version
```

Deberías ver las versiones instaladas (ej: Docker version 24.0.x).

---

## 🚀 Instalación y Configuración

### Paso 1: Obtener el proyecto

Descarga o clona este repositorio:

```powershell
git clone <url-del-repositorio>
cd ticketing-app
```

### Paso 2: Configurar variables de entorno

Copia el archivo de ejemplo y crea tu archivo `.env`:

```powershell
Copy-Item .env.example .env
```

Abre `.env` con un editor y revisa las variables. Para desarrollo local, los valores por defecto son suficientes:

```env
APP_PORT=8080
DB_HOST=db
DB_NAME=ticketing
DB_USER=ticket_user
DB_PASS=ticket_pass
MYSQL_ROOT_PASSWORD=rootpass
```

### Paso 3: Construir y levantar los contenedores

```powershell
docker compose up -d --build
```

**Explicación del comando:**
- `docker compose`: herramienta para manejar aplicaciones multi-contenedor
- `up`: crear y arrancar contenedores
- `-d`: modo detached (segundo plano)
- `--build`: construir imágenes antes de arrancar

Este proceso puede tardar 2-3 minutos la primera vez (descarga imágenes base, instala dependencias).

### Paso 4: Verificar que todo funciona

Espera unos segundos para que la base de datos se inicialice y luego abre tu navegador:

```
http://localhost:8080/public/login.php
```

**Credenciales de prueba:**
- Email: `admin@empresa.com`
- Contraseña: `admin1234`

¡Si ves la página de login, todo está funcionando correctamente! 🎉

---

## 📁 Estructura del Proyecto

```
ticketing-app/
│
├── docker-compose.yml       # Orquestación de contenedores
├── Dockerfile               # Construcción de la imagen app
├── .env.example             # Plantilla de variables de entorno
├── .env                     # Variables de entorno (NO subir a Git)
├── .gitignore               # Archivos ignorados por Git
├── README.md                # Esta guía
│
├── db/
│   └── init.sql             # Script de inicialización de la BD
│
└── src/                     # Código fuente de la aplicación
    ├── config/
    │   └── config.php       # Configuración centralizada
    │
    ├── lib/
    │   ├── db.php           # Clase para manejo de base de datos
    │   └── auth.php         # Funciones de autenticación
    │
    └── public/              # Directorio público (accesible desde web)
        ├── index.php        # Página de inicio (redirección)
        ├── login.php        # Página de login
        ├── logout.php       # Cierre de sesión
        ├── tickets.php      # Lista de tickets
        ├── ticket_new.php   # Crear nuevo ticket
        ├── ticket_view.php  # Ver detalle de ticket
        ├── ticket_close.php # Cerrar ticket
        │
        └── assets/
            ├── style.css    # Estilos CSS
            └── app.js       # JavaScript del cliente
```

### Descripción de directorios

- **`db/`**: Scripts SQL ejecutados al crear la base de datos
- **`src/config/`**: Configuración de la aplicación (BD, sesiones, rutas)
- **`src/lib/`**: Bibliotecas reutilizables (DB, autenticación)
- **`src/public/`**: Archivos accesibles desde el navegador
- **`src/public/assets/`**: Recursos estáticos (CSS, JS, imágenes)

---

## 🔍 Explicación Técnica Detallada

### 1. docker-compose.yml

Este archivo define los **servicios** (contenedores) de la aplicación:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: ticketing-app
    ports:
      - "${APP_PORT:-8080}:80"
    env_file:
      - .env
    environment:
      - DB_HOST=${DB_HOST}
      - DB_NAME=${DB_NAME}
      - DB_USER=${DB_USER}
      - DB_PASS=${DB_PASS}
    volumes:
      - ./src:/var/www/html
    depends_on:
      - db
    restart: unless-stopped
    networks:
      - ticketing-network

  db:
    image: mariadb:11.2
    container_name: ticketing-db
    env_file:
      - .env
    environment:
      - MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
      - MYSQL_DATABASE=${DB_NAME}
      - MYSQL_USER=${DB_USER}
      - MYSQL_PASSWORD=${DB_PASS}
    volumes:
      - db_data:/var/lib/mysql
      - ./db/init.sql:/docker-entrypoint-initdb.d/init.sql
    restart: unless-stopped
    networks:
      - ticketing-network

volumes:
  db_data:
    driver: local

networks:
  ticketing-network:
    driver: bridge
```

**Desglose línea por línea:**

#### Servicio `app`:

- **`build`**: construir imagen desde Dockerfile local
- **`ports`**: mapear puerto 8080 del host → 80 del contenedor
  - Formato: `HOST:CONTAINER`
  - `${APP_PORT:-8080}` = usa variable `APP_PORT` o 8080 por defecto
- **`env_file`**: cargar variables desde `.env`
- **`environment`**: variables de entorno disponibles en el contenedor
- **`volumes`**: montar carpeta local dentro del contenedor
  - `./src:/var/www/html` = sincronización bidireccional
  - Cambios en `./src` se reflejan instantáneamente en el contenedor
- **`depends_on`**: arrancar `db` antes que `app`
  - ⚠️ No espera a que la BD esté "lista", solo a que el contenedor inicie
- **`restart: unless-stopped`**: reiniciar automáticamente si falla
- **`networks`**: conectar a red privada para comunicación interna

#### Servicio `db`:

- **`image`**: usar imagen oficial de MariaDB (no necesita Dockerfile)
- **`volumes`**:
  - `db_data:/var/lib/mysql` = persistir datos en volumen nombrado
  - `./db/init.sql:/docker-entrypoint-initdb.d/init.sql` = ejecutar script SQL al crear el contenedor por primera vez

#### Volúmenes:

- **`db_data`**: almacenamiento persistente para la base de datos
  - Sobrevive a `docker compose down`
  - Se borra solo con `docker compose down -v`

#### Redes:

- **`ticketing-network`**: red privada de tipo `bridge`
  - Los contenedores pueden comunicarse entre sí usando nombres de servicio
  - `app` puede conectarse a `db` usando `db` como hostname

---

### 2. Dockerfile

Define **cómo construir** la imagen del contenedor de la aplicación:

```dockerfile
FROM php:8.2-apache

LABEL maintainer="ticketing-app"
LABEL description="Aplicación de gestión de tickets con PHP y Apache"

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo_mysql

# Habilitar módulo rewrite de Apache
RUN a2enmod rewrite

# Configurar Apache
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/docker-php.conf \
    && a2enconf docker-php

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
```

**Explicación:**

- **`FROM php:8.2-apache`**: imagen base
  - Incluye PHP 8.2 + Apache preconfigurado
  - Mantenida oficialmente por Docker
- **`RUN docker-php-ext-install pdo_mysql`**: instalar extensión PDO para MySQL/MariaDB
  - PDO = PHP Data Objects (interfaz para bases de datos)
  - `pdo_mysql` permite conectar con MySQL/MariaDB
- **`RUN a2enmod rewrite`**: habilitar módulo `mod_rewrite` de Apache
  - Permite URLs limpias y redirecciones
  - Necesario para `.htaccess` (aunque no lo usamos en este proyecto básico)
- **`WORKDIR /var/www/html`**: establecer directorio de trabajo
  - Equivalente a `cd /var/www/html`
  - Directorio por defecto de Apache
- **`RUN chown -R www-data:www-data`**: dar permisos al usuario de Apache
  - `www-data` es el usuario que ejecuta Apache
  - Necesario para que PHP pueda leer/escribir archivos
- **`EXPOSE 80`**: documentar que el contenedor escucha en puerto 80
  - Informativo, no abre el puerto (lo hace `ports` en docker-compose)
- **`CMD ["apache2-foreground"]`**: comando por defecto al iniciar
  - Mantiene Apache en primer plano (necesario para que el contenedor no termine)

---

### 3. Arquitectura de la Aplicación PHP

#### a) config/config.php

Centraliza toda la configuración:

```php
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'ticketing');
// ...
```

**¿Por qué usar `define()` en lugar de variables?**
- Constants are globally accessible
- No se pueden modificar accidentalmente
- Se leen desde variables de entorno de Docker

**`getenv('DB_HOST') ?: 'db'`**:
- Intenta leer variable de entorno `DB_HOST`
- Si no existe, usa valor por defecto `'db'`

#### b) lib/db.php

Clase `Database` con patrón **Singleton**:

**¿Qué es Singleton?**
- Garantiza una única instancia de la clase
- Evita múltiples conexiones a la BD
- Ahorra recursos

**Métodos principales:**
- `getInstance()`: obtener instancia única
- `query()`: ejecutar SELECT (múltiples filas)
- `queryOne()`: ejecutar SELECT (una fila)
- `execute()`: ejecutar INSERT/UPDATE/DELETE

**¿Por qué PDO?**
- **Seguridad**: soporta consultas preparadas (previene SQL injection)
- **Portabilidad**: funciona con MySQL, PostgreSQL, SQLite, etc.
- **Funcionalidad**: manejo robusto de errores

**Consultas preparadas:**
```php
$db->query('SELECT * FROM users WHERE email = ?', [$email]);
```
- El `?` es un placeholder
- PDO escapa automáticamente `$email`
- Imposible inyección SQL

#### c) lib/auth.php

Funciones de autenticación:

**`session_start_secure()`**:
- Inicia sesión con configuración segura
- `httponly`: cookie no accesible desde JavaScript (previene XSS)
- `use_only_cookies`: no usar session ID en URL
- Regenera ID de sesión periódicamente

**`require_login()`**:
- Middleware para proteger páginas
- Si no hay sesión → redirige a login
- Se llama al inicio de cada página privada

**`login_user($email, $password)`**:
- Busca usuario por email
- Verifica contraseña con `password_verify()`
- Crea sesión si es correcto

**¿Por qué `password_verify()`?**
- Las contraseñas en BD están hasheadas con `password_hash()`
- `password_verify()` compara hash de forma segura
- Nunca almacenamos contraseñas en texto plano

**`h($text)`**:
- Escapar HTML para prevenir XSS
- Convierte `<script>` en `&lt;script&gt;`
- Usar SIEMPRE al mostrar datos de usuarios

#### d) public/*.php (páginas)

Cada archivo es una "ruta" accesible:

- **index.php**: redirige según estado de sesión
- **login.php**: formulario + procesamiento de login
- **tickets.php**: lista con filtros
- **ticket_new.php**: formulario de creación
- **ticket_view.php**: detalle de un ticket
- **ticket_close.php**: acción de cerrar (solo POST)
- **logout.php**: destruir sesión

**Patrón común:**
```php
require_once __DIR__ . '/../lib/auth.php';
require_login(); // Proteger página

$user = get_current_user();
// ... lógica ...
?>
<!DOCTYPE html>
<!-- ... HTML ... -->
```

---

## 💻 Guía de Desarrollo Local

### Flujo de trabajo típico

1. **Arrancar contenedores:**
   ```powershell
   docker compose up -d
   ```

2. **Ver logs en tiempo real:**
   ```powershell
   docker compose logs -f app
   ```

3. **Editar código:**
   - Abre `src/` en tu editor favorito
   - Los cambios se reflejan instantáneamente (gracias al volumen)
   - Recarga el navegador para ver cambios

4. **Reiniciar Apache si es necesario:**
   ```powershell
   docker compose restart app
   ```

5. **Parar contenedores:**
   ```powershell
   docker compose down
   ```

### Ver logs

```powershell
# Logs de todos los servicios
docker compose logs

# Logs solo de app
docker compose logs app

# Logs solo de db
docker compose logs db

# Seguir logs en vivo
docker compose logs -f
```

### Ejecutar comandos dentro de contenedores

**Entrar al contenedor app:**
```powershell
docker compose exec app bash
```

Una vez dentro:
```bash
ls -la /var/www/html    # Ver archivos
php -v                  # Versión de PHP
cat /etc/apache2/apache2.conf  # Ver configuración Apache
```

**Entrar al contenedor db:**
```powershell
docker compose exec db bash
```

Conectar a MariaDB:
```bash
mysql -u ticket_user -p
# Password: ticket_pass

USE ticketing;
SHOW TABLES;
SELECT * FROM users;
```

### Reiniciar la base de datos

Si necesitas reiniciar la BD desde cero:

```powershell
# Parar contenedores y borrar volumen
docker compose down -v

# Volver a levantar
docker compose up -d
```

⚠️ **Cuidado:** `-v` borra TODOS los datos de la base de datos.

---

## 🌐 Despliegue en Servidor Real

### Opción 1: VPS (Ubuntu/Debian)

#### Paso 1: Preparar el servidor

Conecta por SSH:
```bash
ssh usuario@tu-servidor-ip
```

Actualizar sistema:
```bash
sudo apt update && sudo apt upgrade -y
```

#### Paso 2: Instalar Docker

```bash
# Instalar dependencias
sudo apt install apt-transport-https ca-certificates curl software-properties-common -y

# Añadir clave GPG de Docker
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Añadir repositorio
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Instalar Docker
sudo apt update
sudo apt install docker-ce docker-ce-cli containerd.io -y

# Instalar Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verificar
docker --version
docker-compose --version
```

#### Paso 3: Clonar el proyecto

```bash
cd /opt
sudo git clone <url-repositorio> ticketing-app
cd ticketing-app
```

#### Paso 4: Configurar variables de entorno

```bash
sudo cp .env.example .env
sudo nano .env
```

**Cambiar valores en producción:**
```env
APP_PORT=8080  # O 80 si no usas proxy inverso
DB_PASS=TuContraseñaSuperSegura123!
MYSQL_ROOT_PASSWORD=OtraContraseñaSegura456!
```

Guardar con `Ctrl+O`, salir con `Ctrl+X`.

#### Paso 5: Levantar la aplicación

```bash
sudo docker-compose up -d --build
```

#### Paso 6: Configurar firewall

```bash
# Permitir SSH
sudo ufw allow 22/tcp

# Permitir puerto de la app
sudo ufw allow 8080/tcp

# Habilitar firewall
sudo ufw enable
```

#### Paso 7: Acceder

Abre navegador:
```
http://IP-DE-TU-SERVIDOR:8080/public/login.php
```

---

### Opción 2: Proxy inverso con Nginx + HTTPS

**¿Qué es un proxy inverso?**
- Servidor intermedio que recibe peticiones y las reenvía
- Ventajas:
  - Gestión centralizada de SSL/HTTPS
  - Balanceo de carga
  - Caché
  - Seguridad adicional

**Arquitectura:**
```
Internet → Nginx (puerto 80/443) → Docker App (puerto 8080)
```

#### Instalar Nginx

```bash
sudo apt install nginx -y
```

#### Configurar sitio

```bash
sudo nano /etc/nginx/sites-available/ticketing
```

Contenido:
```nginx
server {
    listen 80;
    server_name tu-dominio.com;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Activar sitio:
```bash
sudo ln -s /etc/nginx/sites-available/ticketing /etc/nginx/sites-enabled/
sudo nginx -t  # Verificar configuración
sudo systemctl restart nginx
```

#### Añadir HTTPS con Let's Encrypt

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtener certificado
sudo certbot --nginx -d tu-dominio.com

# Renovación automática
sudo certbot renew --dry-run
```

Certbot modifica automáticamente la configuración de Nginx para redirigir HTTP → HTTPS.

Ahora puedes acceder con:
```
https://tu-dominio.com/public/login.php
```

---

## 🚨 Errores Típicos y Soluciones

### 1. Puerto 8080 ya está en uso

**Error:**
```
Error starting userland proxy: listen tcp 0.0.0.0:8080: bind: address already in use
```

**Causa:** Otro programa está usando el puerto 8080.

**Soluciones:**

a) Cambiar puerto en `.env`:
```env
APP_PORT=8081
```

b) Cerrar el programa que lo usa:
```powershell
# Ver qué programa usa el puerto
netstat -ano | findstr :8080

# Terminar proceso (reemplaza <PID> con el número mostrado)
taskkill /PID <PID> /F
```

---

### 2. Base de datos no está lista

**Error:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Causa:** La app intenta conectar antes de que MariaDB esté completamente inicializada.

**Solución:**

Espera 10-15 segundos después de `docker compose up` y recarga la página.

`depends_on` solo garantiza que el contenedor inicie, no que MariaDB esté listo.

**Mejora (opcional):** Usar un script de espera en `Dockerfile`:
```dockerfile
RUN apt-get update && apt-get install -y wait-for-it
CMD wait-for-it db:3306 -- apache2-foreground
```

---

### 3. Credenciales incorrectas

**Error:**
```
Access denied for user 'ticket_user'@'%' to database 'ticketing'
```

**Causa:** Las credenciales en `.env` no coinciden con las de `init.sql` o MariaDB.

**Solución:**

1. Verificar `.env`:
   ```env
   DB_USER=ticket_user
   DB_PASS=ticket_pass
   ```

2. Borrar volumen y recrear:
   ```powershell
   docker compose down -v
   docker compose up -d
   ```

---

### 4. Cambios en código no se reflejan

**Causa:** Caché del navegador o PHP opcache.

**Soluciones:**

a) Recargar con caché limpio: `Ctrl+F5`

b) Reiniciar contenedor:
```powershell
docker compose restart app
```

c) Desactivar opcache en desarrollo (añadir a `Dockerfile`):
```dockerfile
RUN echo "opcache.enable=0" >> /usr/local/etc/php/conf.d/opcache.ini
```

---

### 5. Página en blanco (error 500)

**Causa:** Error de PHP no mostrado.

**Solución:**

Ver logs:
```powershell
docker compose logs app
```

Activar errores en `config.php` (solo desarrollo):
```php
error_reporting(E_ALL);
ini_set('display_errors', '1');
```

---

### 6. Permisos denegados en Linux

**Error:**
```
Permission denied: /var/www/html/...
```

**Causa:** Usuario de Apache no tiene permisos.

**Solución:**
```bash
sudo chown -R www-data:www-data ./src
sudo chmod -R 755 ./src
```

---

## 📚 Comandos Útiles

| Comando | Descripción |
|---------|-------------|
| `docker compose up -d` | Arrancar contenedores en segundo plano |
| `docker compose down` | Parar y eliminar contenedores |
| `docker compose down -v` | Parar y eliminar contenedores + volúmenes |
| `docker compose ps` | Ver estado de contenedores |
| `docker compose logs` | Ver logs de todos los servicios |
| `docker compose logs -f app` | Seguir logs del servicio app |
| `docker compose restart app` | Reiniciar servicio app |
| `docker compose exec app bash` | Entrar al contenedor app |
| `docker compose exec db bash` | Entrar al contenedor db |
| `docker compose build` | Reconstruir imágenes |
| `docker compose up --build` | Reconstruir y arrancar |
| `docker volume ls` | Listar volúmenes |
| `docker volume rm <nombre>` | Eliminar volumen |
| `docker network ls` | Listar redes |
| `docker system prune -a` | Limpiar todo (cuidado) |

### Comandos dentro del contenedor app

```bash
# Entrar
docker compose exec app bash

# Versión de PHP
php -v

# Extensiones instaladas
php -m

# Ver configuración de Apache
cat /etc/apache2/sites-enabled/000-default.conf

# Reiniciar Apache
service apache2 restart

# Probar conexión a DB
php -r "new PDO('mysql:host=db;dbname=ticketing', 'ticket_user', 'ticket_pass');"
```

### Comandos dentro del contenedor db

```bash
# Entrar
docker compose exec db bash

# Conectar a MariaDB
mysql -u ticket_user -p
# Password: ticket_pass

# Comandos SQL
USE ticketing;
SHOW TABLES;
SELECT * FROM users;
DESCRIBE tickets;
```

---

## 🔒 Seguridad en Producción

### ⚠️ Antes de desplegar

#### 1. Cambiar contraseñas

En `.env`:
```env
DB_PASS=ContraseñaCompleja123!@#
MYSQL_ROOT_PASSWORD=OtraContraseñaSegura456$%^
```

#### 2. Eliminar usuario demo

Conectar a la BD:
```sql
USE ticketing;
DELETE FROM users WHERE email = 'admin@empresa.com';
```

O crear un usuario real:
```sql
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin Real', 'tu-email@empresa.com', '$2y$10$...', 'admin');
```

Generar hash:
```php
<?php echo password_hash('TuContraseña', PASSWORD_DEFAULT); ?>
```

#### 3. Deshabilitar errores detallados

En `config.php`:
```php
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', '/var/log/php_errors.log');
}
```

Añadir a `.env`:
```env
APP_ENV=production
```

#### 4. Configurar HTTPS

Usar Nginx con Let's Encrypt (ver sección anterior).

Luego, en `config.php`:
```php
ini_set('session.cookie_secure', '1');  // Solo enviar cookies por HTTPS
```

#### 5. Limitar acceso a puertos

Firewall:
```bash
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw deny 3306/tcp  # No exponer MySQL
```

docker-compose.yml (NO exponer puerto de DB):
```yaml
db:
  # NO poner "ports:" aquí
```

#### 6. Backups automáticos

Script de backup (`backup.sh`):
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker compose exec -T db mysqldump -u root -p$MYSQL_ROOT_PASSWORD ticketing > backup_$DATE.sql
gzip backup_$DATE.sql
```

Cron job (ejecutar diariamente a las 2 AM):
```bash
crontab -e

0 2 * * * /opt/ticketing-app/backup.sh
```

#### 7. Actualizaciones

Actualizar imágenes regularmente:
```bash
docker compose pull
docker compose up -d
```

---

## ✨ Características Implementadas

### Funcionalidades

- ✅ Login con email y contraseña
- ✅ Sesiones seguras (httponly, regeneración de ID)
- ✅ Protección de páginas privadas (middleware)
- ✅ Logout
- ✅ Lista de tickets con filtros (todos/abiertos/cerrados)
- ✅ Creación de tickets
- ✅ Visualización de detalles
- ✅ Cierre de tickets
- ✅ Estadísticas (contadores)

### Seguridad

- ✅ Consultas preparadas (PDO)
- ✅ Escapado HTML (prevención XSS)
- ✅ Contraseñas hasheadas (password_hash/verify)
- ✅ Sesiones seguras
- ✅ Validación de entrada
- ✅ Protección CSRF (formularios POST)

### Tecnologías

- ✅ PHP 8.2 con strict types
- ✅ Apache 2.4
- ✅ MariaDB 11.2
- ✅ Docker + Docker Compose
- ✅ CSS moderno (variables CSS, Grid, Flexbox)
- ✅ JavaScript vanilla (sin frameworks)

---

## 🛠️ Tecnologías Utilizadas

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| PHP | 8.2 | Lenguaje de backend |
| Apache | 2.4 | Servidor web |
| MariaDB | 11.2 | Base de datos |
| Docker | 24+ | Containerización |
| Docker Compose | 2+ | Orquestación |
| PDO | Incluido en PHP | Conexión a BD |
| HTML5 | - | Estructura |
| CSS3 | - | Estilos |
| JavaScript | ES6+ | Interactividad |

---

## 📖 Recursos Adicionales

### Documentación oficial

- [Docker Documentation](https://docs.docker.com/)
- [PHP Manual](https://www.php.net/manual/es/)
- [MariaDB Documentation](https://mariadb.org/documentation/)
- [Apache HTTP Server](https://httpd.apache.org/docs/)

### Tutoriales recomendados

- [Docker para principiantes](https://docker-curriculum.com/)
- [PHP: The Right Way](https://phptherightway.com/)
- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)

---

## 🤝 Contribuir

Este es un proyecto educativo. Si encuentras errores o tienes sugerencias:

1. Abre un issue
2. Haz un pull request
3. Comparte mejoras

---

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

---

## 📧 Soporte

Si tienes problemas:

1. Revisa la sección [Errores Típicos](#errores-típicos-y-soluciones)
2. Consulta los logs: `docker compose logs`
3. Busca el error en Google/Stack Overflow
4. Pregunta en foros de Docker o PHP

---

**¡Feliz desarrollo! 🚀**

Hecho con ❤️ para aprender Docker y PHP
