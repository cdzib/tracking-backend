<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Events\SeatsOccupied;
use App\Events\BookingUpdated;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_id'      => 'required|exists:trips,id',
            'passenger_id' => 'required|exists:passengers,id',
            'seats'        => 'required|array|min:1',
            'seats.*'      => 'integer|min:1',
        ]);

        // Verificar que los asientos no estén ocupados (simplificado)
        $occupied = Booking::where('trip_id', $validated['trip_id'])
            ->whereJsonContains('seats', function($query) use ($validated) {
                foreach ($validated['seats'] as $seat) {
                    $query->orWhereJsonContains('seats', [['seat' => $seat]]);
                }
            })
            ->exists();
        if ($occupied) {
            return response()->json(['message' => 'Uno o más asientos ya están ocupados.'], 409);
        }

        // Generar QRs únicos por asiento
        $seats = collect($validated['seats'])->map(function($seat) {
            return [
                'seat' => $seat,
                'qr'   => (string) Str::uuid(),
            ];
        })->toArray();

        $booking = Booking::create([
            'trip_id'      => $validated['trip_id'],
            'passenger_id' => $validated['passenger_id'],
            'status'       => 'active',
            'seats'        => $seats,
        ]);

        broadcast(new SeatsOccupied($validated['trip_id'], $seats))->toOthers();

        // Emitir evento de asientos ocupados (uno por asiento)
        foreach ($seats as $seat) {
            broadcast(new \App\Events\SeatStatusChanged($validated['trip_id'], $seat['seat'], 'occupied', $validated['passenger_id']))->toOthers();
        }
        // Emitir evento de reserva creada
        broadcast(new BookingUpdated($booking, 'created'))->toOthers();

        return response()->json($booking, 201);
    }

    public function occupiedSeats(Request $request)
    {
        $tripId = $request->query('trip_id');
        if (!$tripId) {
            return response()->json(['message' => 'trip_id is required'], 422);
        }

        $bookings = \App\Models\Booking::where('trip_id', $tripId)->get();
        $seats = collect($bookings)->flatMap(function ($booking) {
            return $booking->seats ?? [];
        })->values();

        return response()->json(['seats' => $seats]);
    }

    public function availableTrips(Request $request)
    {
        // Solo viajes activos y futuros
        $now = now();
        $perPage = $request->input('per_page', 15);
        $query = \App\Models\Trip::with(['vehicle', 'route', 'bookings'])
            ->where('status', 'assigned');

        // Filtros
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->input('route_id'));
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->input('vehicle_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date')) {
            $query->whereDate('datetime', $request->input('date'));
        }
        if ($request->filled('from')) {
            $query->where('datetime', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('datetime', '<=', $request->input('to'));
        }

        $trips = $query->orderBy('datetime')->paginate($perPage);

        // Mapear los items de la paginación
        $trips->getCollection()->transform(function ($trip) {
            $capacity = $trip->vehicle->capacity ?? 0;
            $occupiedSeats = collect($trip->bookings)
                ->flatMap(function ($booking) {
                    return collect($booking->seats)->pluck('seat');
                })
                ->unique()
                ->values();
            $availableSeats = collect(range(1, $capacity))
                ->diff($occupiedSeats)
                ->values();

            return [
                'id' => $trip->id,
                'vehicle' => $trip->vehicle,
                'route' => $trip->route,
                'datetime' => $trip->datetime,
                'status' => $trip->status,
                'capacity' => $capacity,
                'occupied_seats' => $occupiedSeats,
                'available_seats' => $availableSeats,
            ];
        });

        return response()->json($trips);
    }

    /**
     * Actualizar una reserva (por ejemplo, para cancelar)
     * PATCH /api/bookings/{booking}
     * Body: { "status": "cancelled" }
     */
    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);
        $booking->status = $validated['status'];
        $booking->save();


        // Si se cancela la reserva, liberar asientos
        if ($validated['status'] === 'cancelled' && is_array($booking->seats)) {
            foreach ($booking->seats as $seat) {
                broadcast(new \App\Events\SeatStatusChanged($booking->trip_id, $seat['seat'], 'available', $booking->passenger_id))->toOthers();
            }
        }
        // Emitir evento de actualización de reserva
        broadcast(new \App\Events\BookingUpdated($booking, 'updated'))->toOthers();

        return response()->json($booking);
    }

    /**
     * Cancelar una reserva (soft delete)
     * DELETE /api/bookings/{booking}
     */
    public function destroy(Booking $booking)
    {
        $booking->status = 'cancelled';
        $booking->save();

        // Liberar asientos
        if (is_array($booking->seats)) {
            foreach ($booking->seats as $seat) {
                broadcast(new \App\Events\SeatStatusChanged($booking->trip_id, $seat['seat'], 'available', $booking->passenger_id))->toOthers();
            }
        }
        // Emitir evento de cancelación de reserva
        broadcast(new \App\Events\BookingUpdated($booking, 'cancelled'))->toOthers();

        return response()->json(['success' => true]);
    }
}
