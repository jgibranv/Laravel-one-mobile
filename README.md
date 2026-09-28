# Laravel One Mobile — API de tickets de soporte

API REST para registrar y dar seguimiento a **tickets de soporte por sucursal**. Es la respuesta a la
**pregunta 8 de la prueba técnica** (ejemplo con Laravel + Vue).

> **Sin base de datos.** Los datos viven en `database/data/tickets.json` (datos mock) y se leen/escriben a través de
> un repositorio. Toda la capa de Laravel es real: controlador, `FormRequest` con validación, API Resource, rutas,
> binding de dependencias y pruebas automatizadas. En producción solo se cambia la implementación del repositorio por
> Eloquent + MySQL (ver "Cómo migraría a MySQL").

## Stack
- PHP 8.3, **Laravel 13.33**
- Datos: archivo JSON con `flock()` (sin MySQL, sin migraciones)
- Pruebas: PHPUnit (`php artisan test`)

## Cómo correrlo
```bash
cd E:\Laravel-one-mobile
composer install          # si no existe la carpeta vendor
php artisan serve         # API en http://127.0.0.1:8000
```
Probar:
```bash
curl "http://127.0.0.1:8000/api/tickets?estatus=abierto"
```

## Endpoints
| Método | Ruta | Descripción | Respuestas |
|--------|------|-------------|------------|
| `GET` | `/api/tickets?estatus=&severidad=` | Lista tickets (filtros opcionales) | 200 |
| `GET` | `/api/tickets/{id}` | Consulta un ticket | 200 / 404 |
| `POST` | `/api/tickets` | Crea un ticket (nace en estatus `abierto`) | 201 / 422 |
| `PATCH` | `/api/tickets/{id}/estatus` | Cambia el estatus | 200 / 404 / 422 |

Valores válidos: `severidad` = `baja | media | alta`; `estatus` = `abierto | en_proceso | resuelto`.

### Ejemplos
```bash
# Crear
curl -X POST http://127.0.0.1:8000/api/tickets \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d "{\"titulo\":\"No imprime el corte\",\"descripcion\":\"La impresora de la caja 2 no imprime por la tarde.\",\"sucursal_id\":12,\"solicitante\":\"Laura Mendez\",\"severidad\":\"alta\"}"

# Cambiar estatus
curl -X PATCH http://127.0.0.1:8000/api/tickets/1/estatus \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d "{\"estatus\":\"en_proceso\"}"
```
Respuesta de ejemplo (201):
```json
{
  "data": {
    "id": 7, "folio": "TK-000007", "titulo": "No imprime el corte",
    "descripcion": "La impresora de la caja 2 no imprime por la tarde.",
    "sucursal_id": 12, "solicitante": "Laura Mendez",
    "severidad": "alta", "estatus": "abierto",
    "creado": "2026-09-28 10:00:00", "actualizado": "2026-09-28 10:00:00"
  }
}
```
Error de validación (422):
```json
{ "message": "El titulo del ticket es obligatorio.", "errors": { "titulo": ["El titulo del ticket es obligatorio."] } }
```

## Estructura
```
app/
├─ Http/
│  ├─ Controllers/Api/TicketController.php    # index, show, store, updateEstatus
│  ├─ Requests/StoreTicketRequest.php         # validación de alta (422)
│  ├─ Requests/UpdateTicketEstatusRequest.php # validación de cambio de estatus
│  └─ Resources/TicketResource.php            # forma del JSON de salida
├─ Repositories/
│  ├─ TicketRepositoryInterface.php           # contrato
│  └─ JsonTicketRepository.php                # implementación mock (JSON + flock)
├─ Providers/AppServiceProvider.php           # binding interfaz → implementación
database/data/tickets.json                    # "tabla" mock (6 tickets de ejemplo)
routes/api.php                                # rutas con prefijo /api
tests/Feature/TicketApiTest.php               # 8 pruebas (41 aserciones)
config/cors.php                               # permite el origen del front (5173)
```

## Decisiones de arquitectura (para defender en entrevista)
1. **Inversión de dependencias.** `TicketController` recibe `TicketRepositoryInterface`; quién la implementa se decide
   en `AppServiceProvider`. El controlador no sabe si los datos vienen de un JSON o de MySQL.
2. **Validación en el servidor.** Aunque el front valide, la regla vive en el `FormRequest`; la API responde **422**
   con los mensajes por campo.
3. **Contrato HTTP correcto.** `201` al crear, `404` si no existe, `422` si los datos no son válidos, filtros por
   *query string* y `PATCH` para una actualización parcial.
4. **Sin sesión ni cookies** (API stateless), por eso `supports_credentials = false` en CORS.
5. **Integridad del archivo mock.** Lectura con `LOCK_SH` y escritura con `LOCK_EX` + `ftruncate`, para que dos
   peticiones simultáneas no corrompan el JSON.

## Cómo migraría a MySQL (producción)
1. Crear la migración `tickets` con `id`, `folio` (único), `titulo`, `descripcion`, `sucursal_id`, `solicitante`,
   `severidad`, `estatus`, `timestamps`.
2. Crear `app/Models/Ticket.php` (Eloquent) con `$fillable`.
3. Escribir `EloquentTicketRepository implements TicketRepositoryInterface`.
4. Cambiar **una línea** en `AppServiceProvider`:
   ```php
   $this->app->bind(TicketRepositoryInterface::class, EloquentTicketRepository::class);
   ```
   El controlador, los `FormRequest`, el `Resource`, las rutas y las pruebas **no cambian**.

## Pruebas
```bash
php artisan test --filter=TicketApiTest
```
Cubren: listado y estructura del JSON, filtros por estatus y severidad, alta con `201` y folio consecutivo,
validación de campos obligatorios, severidad inválida, cambio de estatus, estatus inválido y `404` en recurso
inexistente. Usan una **copia temporal** del JSON para no alterar los datos mock.
