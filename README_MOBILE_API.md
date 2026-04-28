# Guía Detallada de Integración API para App Móvil (Pasajero)

## 1. Autenticación
### Registro
- **Endpoint:** `POST /api/auth/passenger/register`
- **Body:**
```json
{
  "name": "Juan Perez",
  "email": "juan@demo.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+529991234567"
}
```
- **Respuesta exitosa:**
```json
{
  "passenger": { "id": 1, "name": "Juan Perez", "email": "juan@demo.com", "phone": "+529991234567" },
  "token": "1|abcdef1234567890"
}
```

### Login
- **Endpoint:** `POST /api/auth/passenger/login`
- **Body:**
```json
{
  "email": "juan@demo.com",
  "password": "password123"
}
```
- **Respuesta exitosa:**
```json
{
  "passenger": { "id": 1, "name": "Juan Perez", "email": "juan@demo.com", "phone": "+529991234567" },
  "token": "1|abcdef1234567890"
}
```

**Nota:** Todas las rutas protegidas requieren el header `Authorization: Bearer <token>`

---

## 2. Viajes y Reservas
### Listar viajes disponibles
- **Endpoint:** `GET /api/bookings/available-trips`
- **Respuesta:** Array de viajes con vehicle, ruta, asientos disponibles/ocupados.

### Consultar asientos ocupados
- **Endpoint:** `GET /api/bookings/occupied-seats?trip_id={id}`
- **Respuesta:**
```json
{
  "seats": [ { "seat": 1, "qr": "uuid-1" }, ... ]
}
```

### Crear reserva
- **Endpoint:** `POST /api/bookings`
- **Body:**
```json
{
  "trip_id": 1,
  "passenger_id": 5,
  "seats": [3]
}
```
- **Respuesta:**
```json
{
  "id": 10,
  "trip_id": 1,
  "passenger_id": 5,
  "status": "active",
  "seats": [ { "seat": 3, "qr": "uuid-1" } ]
}
```

### Actualizar reserva
- **Endpoint:** `PATCH /api/bookings/{booking}`
- **Body:**
```json
{
  "seat": 4,
  "status": "cancelled"
}
```

### Cancelar reserva
- **Endpoint:** `DELETE /api/bookings/{booking}`

---

## 3. Chat de Viaje
### Enviar mensaje
- **Endpoint:** `POST /api/trips/{trip}/chat`
- **Body:**
```json
{
  "user_id": 1,
  "user_name": "Juan",
  "message": "¿A qué hora salimos?"
}
```

### Obtener historial
- **Endpoint:** `GET /api/trips/{trip}/chat`

---


## 4. Presencia y Sincronización

### Sincronizar presencia global
- **Endpoint:** `POST /api/presence/sync`
- **Body:**
```json
{
  "users": [ { "id": 1, "name": "Juan" } ],
  "type": "join"
}
```

### Sincronizar presencia por viaje
- **Endpoint:** `POST /api/trips/{trip}/presence/sync`
- **Body:**
```json
{
  "users": [ { "id": 1, "name": "Juan" }, { "id": 2, "name": "Ana" } ],
  "type": "join"
}
```

### Sincronizar datos global
- **Endpoint:** `POST /api/sync/global`
- **Body:**
```json
{
  "payload": { "action": "refresh" },
  "type": "update"
}
```

### Sincronizar datos por viaje
- **Endpoint:** `POST /api/trips/{trip}/sync`
- **Body:**
```json
{
  "payload": { "action": "refresh" },
  "type": "update"
}
```

---

## 5. WebSockets y Canales en Tiempo Real
### Canales y Eventos
- `vehicle.{id}`: Posición del vehículo (evento: `VehicleTracking`)
- `trip.{id}.status`: Estado del viaje (evento: `TripStatusChanged`)
- `trip.{id}.chat`: Mensajes de chat (evento: `TripChatMessage`)
- `trip.{id}.alerts`: Alertas (evento: `TripAlert`)
- `trip.{id}.bookings`: Cambios en reservas (evento: `BookingUpdated`)
- `user.{userId}`: Notificaciones personales
- `trip.{tripId}.presence`: Usuarios conectados (evento: `PresenceUpdated`)

### Ejemplo de payload de eventos:
- **VehicleTracking:**
```json
{
  "vehicle_id": 2,
  "lat": 21.12345,
  "lng": -89.12345,
  "updated_at": "2026-04-20T12:00:00Z"
}
```
- **TripStatusChanged:**
```json
{
  "trip_id": 5,
  "status": "on_boarding",
  "updated_at": "2026-04-20T12:00:00Z"
}
```
- **TripAlert:**
```json
{
  "trip_id": 5,
  "type": "stop",
  "message": "Stop at gas station",
  "created_at": "2026-04-20T12:00:00Z"
}
```
- **BookingUpdated:**
```json
{
  "booking_id": 10,
  "status": "cancelled",
  "updated_at": "2026-04-20T12:00:00Z"
}
```
- **PresenceUpdated:**
```json
{
  "trip_id": 5,
  "users": [ { "id": 1, "name": "Juan" }, { "id": 2, "name": "Ana" } ]
}
```

---

## 6. Seguridad y Errores
- Todas las respuestas de error siguen el formato estándar de Laravel (código y mensaje).
- Los endpoints protegidos requieren autenticación vía Bearer Token.
- Los canales de WebSocket requieren autenticación previa.

---

## 7. Documentación OpenAPI
- Consulta `/api/docs` para ver todos los detalles de los endpoints, parámetros, respuestas y ejemplos.

---

**Contacto Backend:**
- Para dudas o soporte, contactar al responsable del backend.
