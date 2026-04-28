# Documentación de la API REST - vagonetas_backend

Esta API sigue una estructura modular y está documentada con OpenAPI (Swagger) usando API Platform para Laravel.


## 1. Autenticación

### Registro pasajero
`POST /api/auth/passenger/register`

**Request:**
```json
{
  "name": "Juan",
  "email": "juan@mail.com",
  "password": "secreto123"
}
```
**Response:**
```json
{
  "user": { "id": 1, "name": "Juan", "email": "juan@mail.com" },
  "token": "..."
}
```

### Login pasajero
`POST /api/auth/passenger/login`

**Request:**
```json
{
  "email": "juan@mail.com",
  "password": "secreto123"
}
```
**Response:**
```json
{
  "user": { "id": 1, "name": "Juan", "email": "juan@mail.com" },
  "token": "..."
}
```

> Usa el token Bearer para endpoints protegidos.


## 2. Reservas (Bookings)

### Crear reserva
`POST /api/bookings`
**Request:**
```json
{
  "trip_id": 5,
  "seat": 3
}
```
**Response:**
```json
{
  "status": "ok",
  "booking": { "id": 10, "trip_id": 5, "seat": 3, "user_id": 1 }
}
```

### Actualizar reserva
`PATCH /api/bookings/{booking}`
**Request:**
```json
{
  "seat": 4
}
```
**Response:**
```json
{
  "status": "ok",
  "booking": { "id": 10, "trip_id": 5, "seat": 4, "user_id": 1 }
}
```

### Eliminar reserva
`DELETE /api/bookings/{booking}`
**Response:**
```json
{
  "status": "ok"
}
```

### Get Occupied Seats
`GET /api/bookings/occupied-seats`
**Response:**
```json
{
  "trip_id": 5,
  "occupied_seats": [1,2,3,4]
}
```

### Get Available Trips
`GET /api/bookings/available-trips`
**Response:**
```json
[
  { "id": 5, "vehicle_id": 2, "route_id": 1, "status": "active", "available_seats": 8 }
]
```


## 3. Tracking y Viajes

### Actualizar ubicación de vehículo
`POST /api/vehicles/{vehicle}/tracking`
**Request:**
```json
{
  "lat": 21.12345,
  "lng": -89.12345
}
```
**Response:**
```json
{
  "status": "ok",
  "location": { "lat": 21.12345, "lng": -89.12345 }
}
```


## 4. Chat de Viaje

### Enviar mensaje
`POST /api/trips/{trip}/chat`
**Request:**
```json
{
  "message": "¿A qué hora llegamos?"
}
```
**Response:**
```json
{
  "status": "ok",
  "chat_message": { "id": 1, "trip_id": 5, "user_id": 1, "message": "¿A qué hora llegamos?", "created_at": "2026-04-20T12:00:00Z" }
}
```

### Ver historial
`GET /api/trips/{trip}/chat`
**Response:**
```json
[
  { "id": 1, "trip_id": 5, "user_id": 1, "message": "¿A qué hora llegamos?", "created_at": "2026-04-20T12:00:00Z" }
]
```


## 5. Alertas de Viaje

### Enviar alerta
`POST /api/trips/{trip}/alert`
**Request:**
```json
{
  "type": "parada",
  "message": "Parada en gasolinera"
}
```
**Response:**
```json
{
  "status": "ok",
  "alert": { "id": 1, "trip_id": 5, "type": "parada", "message": "Parada en gasolinera", "created_at": "2026-04-20T12:00:00Z" }
}
```



## 7. Presencia y Sincronización

### Sincronizar presencia global
`POST /api/presence/sync`
**Request:**
```json
{
  "users": [ { "id": 1, "name": "Juan" } ],
  "type": "join"
}
```
**Response:**
```json
{
  "status": "ok",
  "channel": "trips.presence",
  "users": [ { "id": 1, "name": "Juan" } ]
}
```

### Sincronizar presencia por viaje
`POST /api/trips/{trip}/presence/sync`
**Request:**
```json
{
  "users": [ { "id": 1, "name": "Juan" } ],
  "type": "join"
}
```
**Response:**
```json
{
  "status": "ok",
  "channel": "trip.5.presence",
  "users": [ { "id": 1, "name": "Juan" } ]
}
```

### Sincronizar datos global
`POST /api/sync/global`
**Request:**
```json
{
  "payload": { "action": "refresh" },
  "type": "update"
}
```
**Response:**
```json
{
  "status": "ok",
  "channel": "apps.sync"
}
```

### Sincronizar datos por viaje
`POST /api/trips/{trip}/sync`
**Request:**
```json
{
  "payload": { "action": "refresh" },
  "type": "update"
}
```
**Response:**
```json
{
  "status": "ok",
  "channel": "trip.5.sync"
}
```


## 7. Presencia y Sincronización

### Sincronizar presencia global
`POST /api/presence/sync`
**Request:**
```json
{
  "users": [ { "id": 1, "name": "Juan" } ],
  "type": "join"
}
```
**Response:**
```json
{
  "status": "ok",
  "channel": "trips.presence",
  "users": [ { "id": 1, "name": "Juan" } ]
}
```

### Sincronizar presencia por viaje
`POST /api/trips/{trip}/presence/sync`
**Request:**
```json
{
  "users": [ { "id": 1, "name": "Juan" } ],
  "type": "join"
}
```
**Response:**
```json
{
  "status": "ok",
  "channel": "trip.5.presence",
  "users": [ { "id": 1, "name": "Juan" } ]
}
```

### Sincronizar datos global
`POST /api/sync/global`
**Request:**
```json
{
  "payload": { "action": "refresh" },
  "type": "update"
}
```
**Response:**
```json
{
  "status": "ok",
  "channel": "apps.sync"
}
```

### Sincronizar datos por viaje
`POST /api/trips/{trip}/sync`
**Request:**
```json
{
  "payload": { "action": "refresh" },
  "type": "update"
}
```
**Response:**
```json
{
  "status": "ok",
  "channel": "trip.5.sync"
}
```


## 8. Estructura de Respuestas

- Todas las respuestas son en formato JSON.
- Errores de validación:
```json
{
  "errors": {
    "email": ["El email ya está en uso"],
    "password": ["La contraseña es muy corta"]
  }
}
```
- Éxito:
```json
{
  "status": "ok",
  ...
}
```

## 9. Modularidad y OpenAPI
- La documentación OpenAPI se genera automáticamente y se puede consultar en `/api/docs` (si está habilitado el API Platform en el entorno).
- Cada módulo (reservas, chat, tracking, alertas, notificaciones, presencia, sincronización) tiene su propio archivo de rutas en `/src/routes/api/`.

## 10. Ejemplo de flujo de reserva
1. El usuario se registra o inicia sesión.
2. Check available trips: `GET /api/bookings/available-trips`
3. Crea una reserva: `POST /api/bookings`
4. Recibe notificación y actualización en tiempo real por WebSocket.
5. Puede cancelar o modificar la reserva: `PATCH` o `DELETE`

---

Para detalles de cada endpoint, revisa los controladores en `/src/app/Http/Controllers/` y los archivos de rutas en `/src/routes/api/`.

¿Necesitas la especificación OpenAPI en formato YAML/JSON? Puedes generarla con el comando artisan de API Platform si está instalado:

```
php artisan api:openapi:export --format=yaml > openapi.yaml
```

O consulta la ruta `/api/docs` si está habilitada.
