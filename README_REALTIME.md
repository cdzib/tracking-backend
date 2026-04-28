# Documentación de Canales y Eventos en Tiempo Real (WebSockets)

Este backend Laravel implementa comunicación en tiempo real usando Laravel Reverb (WebSockets) y canales para sincronización, presencia, notificaciones y chat.


## 1. Canales Disponibles

### Canales de Presencia
- `trips.presence`: Usuarios conectados globalmente a viajes (PresenceChannel)
- `trip.{tripId}.presence`: Usuarios conectados a un viaje específico (PresenceChannel)

### Canales de Sincronización
- `apps.sync`: Sincronización global entre apps (Channel)
- `trip.{tripId}.sync`: Sincronización de datos por viaje (Channel)

### Canales Privados
- `user.{userId}`: Notificaciones privadas a un usuario (PrivateChannel)
- `trip.{tripId}`: Acciones privadas de viaje (PrivateChannel, ej. asientos ocupados)

### Canales Públicos
- `vehicles.tracking`: Tracking de todos los vehículos (Channel)
- `vehicle.{vehicleId}`: Tracking de un vehículo específico (Channel)
- `tracking.vehicles`: Actualizaciones de ubicación global (Channel)
- `gps-events`: Eventos de GPS (Channel)
- `trip.{tripId}.status`: Cambios de estado de viaje (Channel)
- `trip.{tripId}.chat`: Chat en tiempo real por viaje (Channel)
- `trip.{tripId}.alerts`: Alertas de viaje (Channel)
- `trip.{tripId}.bookings`: Actualizaciones de reservas (Channel)


## 2. Eventos Principales

- `PresenceUpdated`: Actualización de usuarios conectados a un canal de presencia (PresenceChannel)
- `DataSync`: Sincronización de datos entre apps o por viaje (Channel)
- `UserNotification`: Notificación privada a usuario (PrivateChannel)
- `VehicleTracking`: Tracking de vehículo individual y global (Channel)
- `TripStatusChanged`: Cambios de estado de viaje (Channel)
- `TripChatMessage`: Mensajes de chat en viaje (Channel)
- `TripAlert`: Alertas de viaje (Channel)
- `BookingUpdated`: Actualizaciones de reservas (Channel)
- `EventAcknowledged`: Confirmación de eventos GPS (Channel)


## 3. Endpoints REST para emitir eventos y sincronización

### Presencia
- `POST /api/presence/sync`  
  Sincroniza usuarios conectados globalmente.
  - Body: `{ "users": [ {"id":1,"name":"Juan"} ], "type": "join|leave|sync" }`
- `POST /api/trips/{trip}/presence/sync`  
  Sincroniza usuarios conectados a un viaje.
  - Body: `{ "users": [ {"id":1,"name":"Juan"} ], "type": "join|leave|sync" }`

### Sincronización de datos
- `POST /api/sync/global`  
  Sincroniza datos globalmente.
  - Body: `{ "payload": { ... }, "type": "update|create|delete|custom" }`
- `POST /api/trips/{trip}/sync`  
  Sincroniza datos por viaje.
  - Body: `{ "payload": { ... }, "type": "update|create|delete|custom" }`

### Notificaciones privadas
- `POST /api/notify-user`  
  Envía notificación privada a un usuario.
  - Body: `{ "user_id": 1, "title": "Título", "message": "Mensaje", "data": { ... } }`

### Chat
- `POST /api/trips/{trip}/chat`  
  Envía mensaje de chat a un viaje.
- `GET /api/trips/{trip}/chat`  
  Obtiene historial de chat de un viaje.

### Alertas
- `POST /api/trips/{trip}/alert`  
  Envía alerta a un viaje.

### Bookings
- `POST /api/bookings`  
  Crea una reserva.
- `GET /api/bookings/occupied-seats`  
  Consulta asientos ocupados.
- `PATCH /api/bookings/{booking}`  
  Actualiza una reserva.
- `DELETE /api/bookings/{booking}`  
  Elimina una reserva.

### Tracking y eventos GPS
- `POST /api/vehicle/{vehicle}/tracking`  
  Actualiza ubicación de un vehículo.
- `GET /api/vehicles/tracking`  
  Consulta tracking de todos los vehículos.
- `POST /api/tracking/events/{eventId}/acknowledge`  
  Confirma un evento GPS.


## 4. Ejemplos de consumo de canales

### JS (Laravel Echo)
#### Canal público
```js
Echo.channel('vehicles.tracking')
    .listen('.vehicle.tracking.updated', data => {
        console.log('Tracking actualizado:', data);
    });
```
#### Canal privado
```js
Echo.private('user.' + userId)
    .listen('.user.notification', data => {
        alert(data.title + ': ' + data.message);
    });
```
#### Canal de presencia
```js
Echo.join('trips.presence')
    .here(users => console.log('Conectados:', users))
    .joining(user => console.log('Se unió:', user))
    .leaving(user => console.log('Salió:', user));
```

### Flutter (web_socket_channel)
```dart
import 'package:web_socket_channel/web_socket_channel.dart';
final channel = WebSocketChannel.connect(
  Uri.parse('ws://TU_BACKEND:PORT/app/APP_KEY?...'),
);
channel.sink.add(jsonEncode({
  "event": "pusher:subscribe",
  "data": { "channel": "trip.123.presence" }
}));
channel.stream.listen((message) {
  print(message);
});
```


## 5. Notas y referencias
- Los canales de presencia y privados requieren autenticación (Bearer token o cookie Sanctum).
- Los eventos se reciben automáticamente en el canal suscrito.
- Puedes extender los eventos y canales según tus necesidades.
- Para más detalles revisa los controladores en `/src/app/Http/Controllers/` y los eventos en `/src/app/Events/`.
