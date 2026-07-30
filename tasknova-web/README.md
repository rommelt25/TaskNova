# TaskNova Web

Frontend de TaskNova construido con Vue 3, Vite, Tailwind CSS, Pinia y Axios. Se conecta a la API Laravel mediante tokens Bearer y no contiene URLs de backend fijadas en el código.

## Requisitos

- Node.js 20 o superior.
- Una API TaskNova desplegada o disponible, con CORS configurado para el dominio del frontend.

## Instalación

```bash
npm install
cp .env.example .env
```

Configura las variables de `.env`:

```dotenv
VITE_APP_NAME=TaskNova
VITE_APP_ENV=development
VITE_API_URL=https://tu-api.example.com/api
VITE_API_TIMEOUT=15000
```

`VITE_API_URL` es obligatoria y debe incluir el prefijo `/api` de Laravel. Cambiar esta variable es el único ajuste necesario para conectar otro backend.

## Desarrollo

```bash
npm run dev
```

## Build de producción

```bash
cp .env.production.example .env.production
npm run build
```

El resultado se genera en `dist/`. Vite separa automáticamente las pantallas en chunks mediante rutas dinámicas.

## Despliegue en Vercel

1. Importa el repositorio o la carpeta `tasknova-web` en Vercel.
2. Configura las variables de entorno de Production:

   ```dotenv
   VITE_APP_NAME=TaskNova
   VITE_APP_ENV=production
   VITE_API_URL=https://tu-backend.up.railway.app/api
   VITE_API_TIMEOUT=15000
   ```

3. Despliega. [vercel.json](./vercel.json) configura el build de Vite y el rewrite de SPA para que las rutas protegidas funcionen al recargar la página.

Las variables `VITE_*` se incorporan durante el build; modifica `VITE_API_URL` desde Vercel y vuelve a desplegar para apuntar a otra API.

## Integración con Railway

En Railway configura el backend con:

```dotenv
APP_URL=https://tu-backend.up.railway.app
FRONTEND_URL=https://tu-frontend.vercel.app
CORS_ALLOWED_ORIGINS=
```

La API debe responder con `Access-Control-Allow-Origin` para `FRONTEND_URL`. TaskNova usa Authorization Bearer, por lo que no necesita cookies cross-site para autenticar la web.

## Manejo de sesión y errores

- El token y usuario se conservan en `localStorage` o `sessionStorage`, según “Recordarme”.
- Al iniciar, se refresca el usuario autenticado con `GET /api/user`.
- Un `401` elimina la sesión local y redirige a `/login`.
- `403`, `404`, `422`, errores de servidor y problemas de conexión muestran mensajes amigables. Las rutas `/error`, `/offline` y la página 404 tienen acciones para volver o reintentar.
