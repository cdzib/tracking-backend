<?php

namespace App\Http\Controllers\Api;

use App\Events\BookingUpdated;
use App\Events\SeatsOccupied;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PassengerReservationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'seats' => 'required|array|min:1',
            'seats.*' => 'integer|min:1',
        ]);

        $passenger = $request->user();
        if (! $passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $trip = Trip::where('id', $validated['trip_id'])
            ->where('status', 'assigned')
            ->first();

        if (! $trip) {
            return response()->json(['message' => 'Viaje no disponible'], 404);
        }

        if (Booking::hasOccupiedSeats($validated['trip_id'], $validated['seats'])) {
            return response()->json(['message' => 'Uno o mas asientos ya estan ocupados.'], 409);
        }

        $seats = collect($validated['seats'])->map(fn ($seat) => [
            'seat' => (int) $seat,
            'qr' => (string) Str::uuid(),
        ])->toArray();

        $booking = Booking::create([
            'trip_id' => $validated['trip_id'],
            'passenger_id' => $passenger->id,
            'status' => 'active',
            'seats' => $seats,
        ]);

        broadcast(new SeatsOccupied($validated['trip_id'], $seats))->toOthers();

        foreach ($seats as $seat) {
            broadcast(new \App\Events\SeatStatusChanged(
                $validated['trip_id'],
                $seat['seat'],
                'occupied',
                $passenger->id,
            ))->toOthers();
        }

        broadcast(new BookingUpdated($booking, 'created'))->toOthers();

        return response()->json($booking, 201);
    }

    public function myTrips(Request $request)
    {
        return $this->bookingsResponse($request);
    }

    public function nextTrip(Request $request)
    {
        $passenger = $request->user();
        if (! $passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $booking = Booking::with(['trip.vehicle', 'trip.route'])
            ->where('passenger_id', $passenger->id)
            ->where('status', 'active')
            ->whereHas('trip', fn ($query) => $query
                ->where('datetime', '>=', now())
                ->where('status', 'assigned'))
            ->orderByRaw('(SELECT datetime FROM trips WHERE trips.id = bookings.trip_id) asc')
            ->first();

        if (! $booking) {
            return response()->json(['message' => 'No hay proximo viaje'], 404);
        }

        return response()->json($this->formatBooking($booking));
    }

    public function history(Request $request)
    {
        return $this->bookingsResponse($request);
    }

    public function show($id)
    {
        $passenger = request()->user();
        if (! $passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $booking = Booking::with(['trip.vehicle', 'trip.route'])
            ->where('id', $id)
            ->where('passenger_id', $passenger->id)
            ->first();

        if (! $booking) {
            return response()->json(['message' => 'Reservacion no encontrada'], 404);
        }

        return response()->json($this->formatBooking($booking));
    }

    public function cancel($id)
    {
        $passenger = request()->user();
        if (! $passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $booking = Booking::where('id', $id)
            ->where('passenger_id', $passenger->id)
            ->first();

        if (! $booking) {
            return response()->json(['message' => 'Reservacion no encontrada'], 404);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'La reservacion ya esta cancelada'], 409);
        }

        $booking->update(['status' => 'cancelled']);

        if (is_array($booking->seats)) {
            foreach ($booking->seats as $seat) {
                broadcast(new \App\Events\SeatStatusChanged(
                    $booking->trip_id,
                    $seat['seat'],
                    'available',
                    $booking->passenger_id,
                ))->toOthers();
            }
        }

        broadcast(new BookingUpdated($booking, 'cancelled'))->toOthers();

        return response()->json(['success' => true, 'message' => 'Reservacion cancelada']);
    }

    public function recentTrips(Request $request)
    {
        $passenger = $request->user();
        if (! $passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $limit = (int) $request->input('limit', 5);
        $query = Booking::with(['trip.vehicle', 'trip.route'])
            ->where('passenger_id', $passenger->id);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json(
            $query->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(fn (Booking $booking) => $this->formatBooking($booking))
        );
    }

    private function bookingsResponse(Request $request)
    {
        $passenger = $request->user();
        if (! $passenger) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $query = Booking::with(['trip.vehicle', 'trip.route'])
            ->where('passenger_id', $passenger->id);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        $bookings->getCollection()->transform(fn (Booking $booking) => $this->formatBooking($booking));

        return response()->json($bookings);
    }

    private function formatBooking(Booking $booking): array
    {
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
    }
}
