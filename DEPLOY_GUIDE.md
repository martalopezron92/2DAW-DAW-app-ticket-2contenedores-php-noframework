# Guia paso a paso para desplegar la app (gratis) con GitHub Actions

Esta guia es para principiantes. Usaremos:
- **Fly.io** para el contenedor de la app (gratis con limites).
- **MariaDB externa gratis** (ejemplo: Aiven o db4free).
- **GitHub Actions** para desplegar automaticamente.

> Nota: Fly.io no incluye MariaDB gratuita. Por eso la base de datos va en otro proveedor.

## 1) Crear la base de datos MariaDB

Elige un proveedor gratuito (por ejemplo):
- Aiven (free tier, a veces con limitaciones)
- db4free (publico y de pruebas)

Pasos generales:
1. Crea una cuenta en el proveedor.
2. Crea una base de datos **MariaDB**.
3. Apunta estos datos:
   - **DB_HOST** (host)
   - **DB_NAME** (nombre de la base de datos)
   - **DB_USER** (usuario)
   - **DB_PASS** (password)

## 2) Subir el proyecto a GitHub

1. Crea un repositorio en GitHub.
2. Sube todo el proyecto (debe incluir `Dockerfile` y `docker-compose.yml`).

## 3) Instalar Fly CLI en tu PC

1. Descarga e instala `flyctl`:
   - https://fly.io/docs/flyctl/install/
2. Inicia sesion:
   ```
   flyctl auth login
   ```

## 4) Crear la app en Fly.io

En la carpeta del proyecto, ejecuta:
```
flyctl launch
```
Responde a las preguntas:
- Elige **Dockerfile**.
- Elige una region cercana.
- No configures base de datos en Fly.

Esto crea un archivo `fly.toml`.

## 5) Configurar secretos en Fly.io

Guarda las credenciales de la base de datos como secretos:
```
flyctl secrets set DB_HOST=... DB_NAME=... DB_USER=... DB_PASS=...
```

## 6) Configurar GitHub Actions

Crea el archivo `.github/workflows/deploy.yml` con este contenido:

```yaml
name: Deploy to Fly.io

on:
  push:
    branches: [ "main" ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: superfly/flyctl-actions/setup-flyctl@master
      - run: flyctl deploy --remote-only
        env:
          FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN }}
```

## 7) Crear el secreto en GitHub

1. En GitHub, ve a **Settings -> Secrets and variables -> Actions**.
2. Crea un secreto llamado `FLY_API_TOKEN`.
3. El valor se obtiene con:
   ```
   flyctl auth token
   ```

## 8) Desplegar

1. Haz commit y push a la rama `main`.
2. GitHub Actions ejecutara el deploy automaticamente.
3. Abre la URL que te da Fly.io.

## 9) Inicializar la base de datos

Tu proyecto tiene un script SQL en `db/init.sql`.
- Importalo en la base de datos externa usando el panel del proveedor o una herramienta como DBeaver.

## 10) Comprobaciones finales

- Abre la URL y verifica que carga la app.
- Prueba login y creacion de tickets.

---

Si quieres, puedo adaptar la guia a otro proveedor gratuito o ayudarte a crear el workflow en tu repo.
