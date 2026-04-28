# Passenger API Flows

This document summarizes the required APIs for a passenger to use the mobile app and complete all main flows.

| Flow                | Endpoint & Method                                 | Description                                  |
|---------------------|---------------------------------------------------|----------------------------------------------|
| Register            | POST /api/auth/passenger/register                  | Register a new passenger                     |
| Login               | POST /api/auth/passenger/login                     | Authenticate and get token                   |
| List trips          | GET /api/bookings/available-trips                  | List available trips                         |
| Occupied seats      | GET /api/bookings/occupied-seats?trip_id={id}      | Get occupied seats for a trip                |
| Create booking      | POST /api/bookings                                 | Book one or more seats                       |
| Update booking      | PATCH /api/bookings/{booking}                      | Update booking (e.g., change seat/status)    |
| Cancel booking      | DELETE /api/bookings/{booking}                     | Cancel a booking                             |
| Trip chat send      | POST /api/trips/{trip}/chat                        | Send a message to trip chat                  |
| Trip chat history   | GET /api/trips/{trip}/chat                         | Get chat history for a trip                  |
| Presence (optional) | POST /api/trips/{trip}/presence/sync               | Sync connected users to a trip (optional)    |
| Real-time events    | WebSocket channels (see README_REALTIME.md)        | Receive notifications, tracking, alerts, etc |

## Example Usage

### 1. Register
```http
POST /api/auth/passenger/register
Content-Type: application/json
{
  "name": "John",
  "email": "john@mail.com",
  "password": "secret123"
}
```

### 2. Login
```http
POST /api/auth/passenger/login
Content-Type: application/json
{
  "email": "john@mail.com",
  "password": "secret123"
}
```

### 3. List available trips
```http
GET /api/bookings/available-trips
Authorization: Bearer {token}
```

### 4. Get occupied seats for a trip
```http
GET /api/bookings/occupied-seats?trip_id=5
Authorization: Bearer {token}
```

### 5. Create a booking
```http
POST /api/bookings
Authorization: Bearer {token}
Content-Type: application/json
{
  "trip_id": 5,
  "seat": 3
}
```

### 6. Cancel a booking
```http
DELETE /api/bookings/10
Authorization: Bearer {token}
```

### 7. Send a chat message
```http
POST /api/trips/5/chat
Authorization: Bearer {token}
Content-Type: application/json
{
  "message": "What time do we arrive?"
}
```


### 8. Get trip status (real-time)
- Subscribe to WebSocket channel: `trip.{tripId}.status`
- Example event payload:
```json
{
  "trip_id": 5,
  "status": "on_boarding",
  "updated_at": "2026-04-20T12:00:00Z"
}
```

- Subscribe to WebSocket channel: `vehicle.{vehicleId}` or `vehicles.tracking`
- Example event payload:
```json
{
  "vehicle_id": 2,
  "lat": 21.12345,
  "lng": -89.12345,
  "updated_at": "2026-04-20T12:00:00Z"
}
```

### 10. Receive trip alerts (real-time)
- Subscribe to WebSocket channel: `trip.{tripId}.alerts`
- Example event payload:
```json
{
  "trip_id": 5,
  "type": "stop",
  "message": "Stop at gas station",
  "created_at": "2026-04-20T12:00:00Z"
}
```

### 11. Receive private notifications (real-time)
- Subscribe to WebSocket channel: `user.{userId}`
- Example event payload:
```json
{
  "title": "Booking confirmed",
  "message": "Your booking has been confirmed",
  "data": { "booking_id": 10 },
  "timestamp": "2026-04-20T12:00:00Z"
}
```

---

---

With these APIs, a passenger can register, log in, view and book trips, chat, and receive all real-time updates needed for the app experience.
