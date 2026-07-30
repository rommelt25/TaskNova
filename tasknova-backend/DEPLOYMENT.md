# Despliegue del backend

## Railway

Railway puede desplegar este directorio directamente usando `railway.json`. Configura todas las variables tomando como base `.env.production.example`; Railway inyecta `PORT` automáticamente y el comando de inicio escucha en ese puerto.

- Usa el servicio MySQL de Railway y copia sus valores en `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.
- Define `APP_URL` con la URL pública de la API y `FRONTEND_URL` con la URL pública de Vue. No se requieren cambios de código al cambiar de dominio.
- Deja `RUN_MIGRATIONS=true` para que el entrypoint ejecute `php artisan migrate --force` antes de iniciar la API.
- Railway debe proporcionar un volumen si se guardarán avatares localmente; para almacenamiento no persistente configura un disco externo mediante las variables correspondientes.
- Define `FILESYSTEM_DISK=public`; el entrypoint crea `public/storage` automáticamente.

Esta configuración publica únicamente la API Laravel. Incluye Nginx, PHP-FPM, MariaDB y un worker de cola; no construye ni sirve aplicaciones Flutter o Vue.

## Preparación

1. Copia `.env.production.example` como `.env.production`.
2. Define un `APP_URL` HTTPS real, `FRONTEND_URL`, `DB_*` y `MYSQL_ROOT_PASSWORD`. Para Docker Compose, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` se reutilizan al crear MariaDB.
3. Genera una clave de aplicación y copia el resultado en `APP_KEY`:

   ```powershell
   docker compose --env-file .env.production run --rm --no-deps --entrypoint php app artisan key:generate --show
   ```

4. Define `FRONTEND_URL` con la URL final de Vue. Si necesitas más de un origen, añádelos en `CORS_ALLOWED_ORIGINS`, separados por comas. No uses `*` cuando `CORS_SUPPORTS_CREDENTIALS=true`.
5. Si Vue usa sesiones de Sanctum en lugar de tokens Bearer, añade su host y puerto a `SANCTUM_STATEFUL_DOMAINS`. Las aplicaciones Flutter nativas no requieren CORS.

## Inicio

```powershell
docker compose --env-file .env.production up -d --build
```

La API queda disponible en `http://servidor:8080` durante una instalación directa. En producción, coloca un proxy con TLS delante del puerto y usa la URL HTTPS configurada en `APP_URL`. Solo Nginx publica un puerto; PHP-FPM, MariaDB y el worker permanecen privados.

## Clientes

- Configura Vue con la base `https://api.example.com/api` y registra su dominio en `CORS_ALLOWED_ORIGINS`.
- Configura Flutter con la misma base. En emuladores, usa una dirección alcanzable desde el emulador, no `localhost` del dispositivo.
- Usa el endpoint `https://api.example.com/up` para comprobaciones de salud.

## Operación

Las migraciones se aplican al arrancar el servicio `app`. Los datos de MariaDB y el almacenamiento local de Laravel se conservan en volúmenes de Docker. Después de modificar `.env.production`, reinicia los servicios para reconstruir la caché de configuración:

```powershell
docker compose --env-file .env.production up -d --force-recreate
```
