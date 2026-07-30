# API de perfil de usuario

Los endpoints requieren un token de Sanctum en el encabezado `Authorization: Bearer {token}`. Cada operación se realiza exclusivamente sobre el perfil del usuario autenticado; no se acepta ningún identificador de usuario en la URL ni en el cuerpo.

## Consultar perfil

`GET /api/profile`

Devuelve `200 OK` y un recurso JSON bajo la clave `data`. Si el usuario aún no cuenta con perfil, se crea un registro vacío asociado a él para que pueda completar el formulario.

```json
{
  "data": {
    "id": 12,
    "user_id": 4,
    "email": "usuario@ejemplo.com",
    "first_name": "Ana",
    "last_name": "Pérez",
    "gender": "female",
    "academic_cycle": "VI",
    "avatar_url": "http://localhost/storage/avatars/archivo.webp"
  }
}
```

## Actualizar perfil

`PUT /api/profile`

Acepta `application/json` o `multipart/form-data` cuando se adjunta `avatar`. Responde `200 OK` con el recurso actualizado bajo `data`.

Campos requeridos:

- `first_name`, `last_name`: texto, máximo 100 caracteres.
- `phone`: celular peruano, con prefijo `+51` opcional.
- `birth_date`: fecha anterior al día actual.
- `gender`: `male`, `female` o `undisclosed`.
- `institution`, `education_level`, `career`, `grade`, `academic_cycle`, `department`, `province`, `district`.

Campos opcionales:

- `avatar`: imagen JPG, JPEG, PNG o WEBP de hasta 5 MB.

Por compatibilidad con la web actual, el endpoint también acepta `sex` como alias de `gender` y `cycle` como alias de `academic_cycle`. La respuesta incluye ambos alias, además de `avatar_url`.

Errores de validación responden `422 Unprocessable Entity` en el formato estándar de Laravel. Un token inválido o ausente responde `401 Unauthorized`.
