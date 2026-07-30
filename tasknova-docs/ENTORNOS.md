# 🚀 TASKNOVA

> Sistema de Gestión de Tareas Multiplataforma

---

# 📖 Descripción del Proyecto

**TaskNova** es una plataforma para la gestión de tareas desarrollada como un proyecto académico utilizando una arquitectura moderna basada en API REST.

El proyecto está compuesto por cuatro módulos independientes que trabajan de forma integrada:

- Backend desarrollado con Laravel.
- Aplicación móvil desarrollada con Flutter.
- Panel administrativo desarrollado con Vue.js.
- Repositorio de documentación.

La comunicación entre los clientes (Flutter y Vue.js) se realiza mediante una API REST desarrollada en Laravel utilizando PostgreSQL como sistema gestor de base de datos.

---

# 📁 Arquitectura General

```
TaskNova/
│
├── tasknova-backend      # API Laravel
├── tasknova-flutter      # Aplicación móvil
├── tasknova-web          # Panel Web Administrativo
└── tasknova-docs         # Documentación Oficial
```

---

# 📦 Repositorios del Proyecto

| Repositorio | Descripción |
|-------------|-------------|
| tasknova-backend | Backend Laravel |
| tasknova-flutter | Aplicación Flutter |
| tasknova-web | Panel Administrativo Vue |
| tasknova-docs | Documentación Oficial |

---

# 🏗 Arquitectura Tecnológica

```
                Flutter App
                     │
                     │ HTTP
                     ▼
               Laravel API
                     │
                     │
             PostgreSQL Database
                     │
                     ▲
                     │ HTTP
               Vue Admin Panel
```

---

# ⚙ Stack Tecnológico

## Backend

- PHP 8.3+
- Laravel 12
- Composer
- PostgreSQL 17
- Sanctum
- Postman

---

## Mobile

- Flutter SDK
- Dart
- Android Studio
- HTTP Package

---

## Web

- Vue 3
- Vite
- Axios
- TailwindCSS

---

## Base de Datos

- PostgreSQL 17

---

## Control de Versiones

- Git
- GitHub

---

# 📂 Backend

Repositorio

```
tasknova-backend
```

## Tecnologías

- Laravel 12
- PHP 8.3
- Composer
- Sanctum
- PostgreSQL

---

## Estructura

```
tasknova-backend/

app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/

artisan
composer.json
README.md
.env.example
```

---

## Responsabilidades

- API REST
- Login
- Registro
- Autenticación
- CRUD Usuarios
- CRUD Tareas
- Validaciones
- Gestión de Roles
- Integración con PostgreSQL

---

## Endpoint Base

```
http://localhost:8000/api
```

---

## Endpoints Iniciales

### Auth

```
POST    /login

POST    /register

POST    /logout

GET     /me
```

---

### Usuarios

```
GET     /users

GET     /users/{id}

POST    /users

PUT     /users/{id}

DELETE  /users/{id}
```

---

### Tareas

```
GET     /tasks

GET     /tasks/{id}

POST    /tasks

PUT     /tasks/{id}

DELETE  /tasks/{id}
```

---

# 📱 Flutter

Repositorio

```
tasknova-flutter
```

---

## Tecnologías

- Flutter
- Dart

---

## Arquitectura

```
lib/

features/

auth/

tasks/

profile/

shared/

main.dart
```

---

## Funcionalidades

- Login
- Registro
- Recuperación de contraseña
- Lista de tareas
- Crear tarea
- Editar tarea
- Eliminar tarea
- Perfil
- Cerrar sesión

---

## Comunicación

```
http://localhost:8000/api
```

---

# 🌐 Panel Web

Repositorio

```
tasknova-web
```

---

## Tecnologías

- Vue 3
- Vite
- Axios
- TailwindCSS

---

## Arquitectura

```
src/

components/

views/

router/

services/

layouts/

assets/

App.vue
```

---

## Funcionalidades

- Login administrador
- Dashboard
- Gestión Usuarios
- Gestión Tareas
- Estadísticas
- Perfil
- Roles

---

## Comunicación

```
http://localhost:8000/api
```

---

# 🗄 Base de Datos

Motor

```
PostgreSQL 17
```

---

## Tablas Iniciales

```
users

tasks

roles

user_roles
```

---

## Relaciones

```
Usuario

1 -------- N

Tareas
```

```
Rol

1 -------- N

Usuarios
```

---

## Modelo General

```
users

id
name
email
password
created_at
updated_at
```

---

```
roles

id
name
description
```

---

```
user_roles

id
user_id
role_id
```

---

```
tasks

id
user_id
title
description
status
priority
due_date
created_at
updated_at
```

---

# 🔐 Seguridad

Se implementará:

- Laravel Sanctum
- Hash de contraseñas
- Middleware
- Validación de Requests
- Tokens de autenticación

---

# 🧪 Entorno de Pruebas

Herramientas

- Postman
- Laravel Logs
- Flutter Debug Console
- Vue DevTools

---

## Casos de Prueba

### Autenticación

- Login correcto

- Login incorrecto

- Registro

- Logout

---

### Usuarios

- Crear usuario

- Editar usuario

- Eliminar usuario

- Buscar usuario

---

### Tareas

- Crear tarea

- Actualizar tarea

- Eliminar tarea

- Cambiar estado

---

### API

- Validación JSON

- Errores HTTP

- Tokens

---

# 🔥 Git Workflow

## Ramas Oficiales

```
main
```

Producción

---

```
develop
```

Desarrollo

---

```
feature/*
```

Trabajo individual

---

## Ejemplos

```
feature/login

feature/register

feature/tasks

feature/dashboard

feature/profile
```

---

## Flujo

```
feature/login

↓

develop

↓

main
```

---

# 📋 Convenciones

## Backend

```
camelCase

Métodos

Variables
```

---

## Base de Datos

```
snake_case
```

---

## API

Siempre responder:

```json
{
    "success": true,
    "message": "Operación realizada correctamente",
    "data": {}
}
```

---

# 📌 Convenciones de Commits

```
feat:
Nueva funcionalidad
```

Ejemplo

```
feat: login con Laravel Sanctum
```

---

```
fix:
Corrección de errores
```

Ejemplo

```
fix: validación del registro
```

---

```
docs:
Documentación
```

Ejemplo

```
docs: actualización del README
```

---

```
style:
Cambios visuales
```

---

```
refactor:
Refactorización
```

---

```
test:
Pruebas
```

---

# 🚀 Roadmap Inicial

## Fase 1

- Configuración del proyecto
- Backend Laravel
- PostgreSQL

---

## Fase 2

- Login
- Registro
- Usuarios

---

## Fase 3

- CRUD Tareas

---

## Fase 4

- Flutter

---

## Fase 5

- Vue Admin

---

## Fase 6

- Pruebas

---

## Fase 7

- Despliegue

---

# 👥 Equipo

Proyecto desarrollado para el curso de Ingeniería de Sistemas.

---

# 📄 Licencia

Proyecto académico.

Todos los derechos reservados.