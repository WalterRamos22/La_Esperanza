# Sistema Comunidad La Esperanza

Sistema de Gestión y Comercialización para la Comunidad Agrícola Rural “La Esperanza”.

## Descripción

Este proyecto consiste en una aplicación web para apoyar la gestión de productos agrícolas, solicitudes de compra, entregas, calificaciones e historial de operaciones dentro de una comunidad rural.

El sistema permite el acceso por roles:

- Administrador comunitario
- Productor
- Comprador

## Tecnologías utilizadas

- PHP 8.2
- Apache
- MySQL 8.0
- Bootstrap 5
- HTML5
- CSS3
- Docker
- Docker Compose
- Caddy

## Módulos implementados

- Inicio de sesión con teléfono y contraseña
- Control de acceso por rol
- Dashboard principal
- Gestión de productos agrícolas
- Solicitudes de compra
- Confirmación y cancelación de solicitudes
- Registro de entregas
- Registro de calificaciones
- Historial del sistema

## Usuarios de prueba

| Rol | Teléfono | Contraseña |
|---|---|---|
| Administrador | 55550001 | admin123 |
| Productor | 55550002 | prod123 |
| Comprador | 55550003 | comp123 |

## Ejecución local

Para ejecutar el proyecto localmente, se debe tener instalado Docker Desktop.

Comando para levantar los contenedores:



```bash
docker compose up -d
