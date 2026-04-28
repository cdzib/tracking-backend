<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Support\Facades\Auth;

class PassengerReservationController extends Controller
{
    // POST /reservations
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'seats' => 'required|array|min:1',
            'seats.*' => 'integer|min:1',
        ]);

        $passenger = $request->user();
        if (!$passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Validar que el viaje exista y esté disponible
        $trip = \App\Models\Trip::where('id', $validated['trip_id'])
            ->where('status', 'assigned')
            ->first();
        if (!$trip) {
            return response()->json(['message' => 'Viaje no disponible'], 404);
        }

        // Verificar que los asientos no estén ocupados
        $occupied = Booking::where('trip_id', $validated['trip_id'])
            ->where(function($query) use ($validated) {
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
                'qr'   => (string) \Illuminate\Support\Str::uuid(),
            ];
        })->toArray();

        $booking = Booking::create([
            'trip_id' => $validated['trip_id'],
            'passenger_id' => $passenger->id,
            'status' => 'active',
            'seats' => $seats,
        ]);

        // Aquí puedes emitir eventos si es necesario

        return response()->json($booking, 201);
    }

    // GET /my-trips
    public function myTrips(Request $request)
    {
        $passenger = $request->user();
        if (!$passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $perPage = $request->input('per_page', 15);
        $status = $request->input('status');
        $query = \App\Models\Booking::with(['trip.vehicle', 'trip.route'])
            ->where('passenger_id', $passenger->id);
        if ($status) {
            $query->where('status', $status);
        }
        $bookings = $query->orderByDesc('created_at')->paginate($perPage);

        $bookings->getCollection()->transform(function($booking) {
            return [
                'booking_id' => $booking->id,
                'status' => $booking->status,
                'seats' => $booking->seats,
                'trip' => [
                    'id' => $booking->trip->id ?? null,
                    'datetime' => $booking->trip->datetime ?? null,
                    'status' => $booking->trip->status ?? null,
                    'vehicle' => $booking->trip->vehicle ?? null,
                    'route' => $booking->trip->route ?? null,
                ],
                'created_at' => $booking->created_at,
            ];
        });

        return response()->json($bookings);
    }

    // GET /my-trips/next
    public function nextTrip(Request $request)
    {
        $passenger = $request->user();
        if (!$passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $now = now();
        $booking = \App\Models\Booking::with(['trip.vehicle', 'trip.route', 'trip'])
            ->where('passenger_id', $passenger->id)
            ->where('status', 'active')
            ->whereHas('trip', function($q) use ($now) {
                $q->where('datetime', '>=', $now)->where('status', 'assigned');
            })
            ->orderByRaw('(SELECT datetime FROM trips WHERE trips.id = bookings.trip_id) asc')
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'No hay próximo viaje'], 404);
        }

        $result = [
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'seats' => $booking->seats,
            'trip' => [
                'id' => $booking->trip->id ?? null,
                'datetime' => $booking->trip->datetime ?? null,
                'status' => $booking->trip->status ?? null,
                'vehicle' => $booking->trip->vehicle ?? null,
                'route' => $booking->trip->route ?? null,
            ],
            'created_at' => $booking->created_at,
        ];

        return response()->json($result);
    }

    // GET /my-trips/history
    public function history(Request $request)
    {
        $passenger = $request->user();
        if (!$passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $perPage = $request->input('per_page', 15);
        $query = \App\Models\Booking::with(['trip.vehicle', 'trip.route'])
            ->where('passenger_id', $passenger->id)
            ->orderByDesc('created_at');

        // Opcional: filtrar por estado si se pasa status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->paginate($perPage);

        $bookings->getCollection()->transform(function($booking) {
            return [
                'booking_id' => $booking->id,
                'status' => $booking->status,
                'seats' => $booking->seats,
                'trip' => [
                    'id' => $booking->trip->id ?? null,
                    'datetime' => $booking->trip->datetime ?? null,
                    'status' => $booking->trip->status ?? null,
                    'vehicle' => $booking->trip->vehicle ?? null,
                    'route' => $booking->trip->route ?? null,
                ],
                'created_at' => $booking->created_at,
            ];
        });

        return response()->json($bookings);
    }

    // GET /reservations/{id}
    public function show($id)
    {
        $passenger = request()->user();
        if (!$passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $booking = \App\Models\Booking::with(['trip.vehicle', 'trip.route'])
            ->where('id', $id)
            ->where('passenger_id', $passenger->id)
            ->first();
        if (!$booking) {
            return response()->json(['message' => 'Reservación no encontrada'], 404);
        }

        $result = [
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'seats' => $booking->seats,
            'trip' => [
                'id' => $booking->trip->id ?? null,
                'datetime' => $booking->trip->datetime ?? null,
                'status' => $booking->trip->status ?? null,
                'vehicle' => $booking->trip->vehicle ?? null,
                'route' => $booking->trip->route ?? null,
            ],
            'created_at' => $booking->created_at,
        ];

        return response()->json($result);
    }

    // PATCH /reservations/{id}/cancel
    public function cancel($id)
    {
        $passenger = request()->user();
        if (!$passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $booking = \App\Models\Booking::where('id', $id)
            ->where('passenger_id', $passenger->id)
            ->first();
        if (!$booking) {
            return response()->json(['message' => 'Reservación no encontrada'], 404);
        }
        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'La reservación ya está cancelada'], 409);
        }

        $booking->status = 'cancelled';
        $booking->save();

        // Aquí puedes emitir eventos si es necesario

        return response()->json(['success' => true, 'message' => 'Reservación cancelada']);
    }

     // GET /recent-trips
    public function recentTrips(Request $request)
    {
        $passenger = $request->user();
        if (!$passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $limit = (int) $request->input('limit', 5); // Por defecto 5
        $status = $request->input('status'); // Opcional: filtrar por estado

        $query = \App\Models\Booking::with(['trip.vehicle', 'trip.route'])
            ->where('passenger_id', $passenger->id);
        if ($status) {
            $query->where('status', $status);
        }
        $bookings = $query->orderByDesc('created_at')->limit($limit)->get();

        $result = $bookings->map(function($booking) {
            return [
                'booking_id' => $booking->id,
                'status' => $booking->status,
                'seats' => $booking->seats,
                'trip' => [
                    'id' => $booking->trip->id ?? null,
                    'datetime' => $booking->trip->datetime ?? null,
                    'status' => $booking->trip->status ?? null,
                    'vehicle' => $booking->trip->vehicle ?? null,
                    'route' => $booking->trip->route ?? null,
                ],
                'created_at' => $booking->created_at,
            ];
        });

        return response()->json($result);
    }
}
