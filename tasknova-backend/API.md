# API de TaskNova

Base local: `http://localhost:8080/api`.

Todos los endpoints, salvo registro e inicio de sesión, requieren el encabezado:

```http
Authorization: Bearer {token}
Accept: application/json
```

Los tokens se reciben al registrarse o iniciar sesión. Flutter debe guardarlos en almacenamiento seguro y Vue en el mecanismo de almacenamiento que el equipo haya definido. Nunca se deben enviar contraseñas ni tokens a registros o repositorios.

## Autenticación

| Método | Ruta | Cuerpo | Respuesta |
| --- | --- | --- | --- |
| POST | `/register` | `name`, `email`, `password`, `password_confirmation`, `device_name?` | Usuario y token Bearer (`201`) |
| POST | `/login` | `email`, `password`, `device_name?` | Usuario y token Bearer (`200`) |
| GET | `/user` | - | Usuario autenticado (`200`) |
| POST | `/logout` | - | Revoca el token usado (`200`) |
| POST | `/logout-all` | - | Revoca todos los tokens del usuario (`200`) |

Ejemplo de autenticación:

```json
{
  "email": "ana@example.com",
  "password": "password123",
  "device_name": "vue-web"
}
```

La respuesta incluye `data.user`, `data.token` y `data.token_type`, cuyo valor es `Bearer`.

## Tareas

| Método | Ruta | Descripción |
| --- | --- | --- |
| GET | `/tasks` | Lista tareas propias y compartidas |
| POST | `/tasks` | Crea una tarea propia |
| GET | `/tasks/{id}` | Consulta una tarea accesible |
| PUT/PATCH | `/tasks/{id}` | Actualiza una tarea propia |
| DELETE | `/tasks/{id}` | Elimina una tarea propia |
| GET | `/tasks/{id}/shares` | Lista usuarios con acceso; solo propietario |
| POST | `/tasks/{id}/shares` | Comparte una tarea; solo propietario |
| DELETE | `/tasks/{id}/shares/{userId}` | Revoca un acceso; solo propietario |

Para crear una tarea se requieren `title`, `subject` y `due_date` en formato `YYYY-MM-DD`. Los valores permitidos son:

- `priority`: `low`, `medium`, `high`.
- `status`: `pending`, `in_progress`, `completed`.

Ejemplo:

```json
{
  "title": "Entregar avance",
  "description": "Preparar la API",
  "subject": "Desarrollo de aplicaciones",
  "priority": "high",
  "status": "pending",
  "due_date": "2026-08-01"
}
```

`GET /tasks` admite los filtros opcionales `scope` (`all`, `owned`, `shared`), `status`, `priority`, `subject`, `due_from`, `due_to` y `per_page` (de 1 a 100). La respuesta es paginada.

Las tareas compartidas son de solo lectura. El campo `can_manage` permite que los clientes oculten las acciones de edición/eliminación cuando vale `false`.

Para compartir una tarea, envía el correo de un usuario ya registrado:

```json
{
  "email": "companero@example.com"
}
```

## Errores

- `401`: token ausente, inválido o credenciales incorrectas.
- `403`: usuario sin permiso sobre la tarea.
- `404`: recurso inexistente.
- `422`: datos inválidos; `errors` contiene los campos a corregir.
