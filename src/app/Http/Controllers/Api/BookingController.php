<?php

namespace App\Http\Controllers\Api;

use App\Events\BookingUpdated;
use App\Events\SeatsOccupied;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'passenger_id' => 'required|exists:passengers,id',
            'seats' => 'required|array|min:1',
            'seats.*' => 'integer|min:1',
        ]);

        if (Booking::hasOccupiedSeats($validated['trip_id'], $validated['seats'])) {
            return response()->json(['message' => 'Uno o mas asientos ya estan ocupados.'], 409);
        }

        $seats = collect($validated['seats'])->map(fn ($seat) => [
            'seat' => (int) $seat,
            'qr' => (string) Str::uuid(),
        ])->toArray();

        $booking = Booking::create([
            'trip_id' => $validated['trip_id'],
            'passenger_id' => $validated['passenger_id'],
            'status' => 'active',
            'seats' => $seats,
        ]);

        broadcast(new SeatsOccupied($validated['trip_id'], $seats))->toOthers();

        foreach ($seats as $seat) {
            broadcast(new \App\Events\SeatStatusChanged(
                $validated['trip_id'],
                $seat['seat'],
                'occupied',
                $validated['passenger_id'],
            ))->toOthers();
        }

        broadcast(new BookingUpdated($booking, 'created'))->toOthers();

        return response()->json($booking, 201);
    }

    public function occupiedSeats(Request $request)
    {
        $tripId = $request->query('trip_id');

        if (! $tripId) {
            return response()->json(['message' => 'trip_id is required'], 422);
        }

        $seats = Booking::where('trip_id', $tripId)
            ->where('status', 'active')
            ->get()
            ->flatMap(fn (Booking $booking) => $booking->seats ?? [])
            ->values();

        return response()->json(['seats' => $seats]);
    }

    public function availableTrips(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $query = Trip::with(['vehicle', 'route', 'bookings'])
            ->where('status', 'assigned');

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

        $trips->getCollection()->transform(function ($trip) {
            $capacity = $trip->vehicle->capacity ?? 0;
            $occupiedSeats = collect($trip->bookings)
                ->where('status', 'active')
                ->flatMap(fn (Booking $booking) => collect($booking->seats)->pluck('seat'))
                ->unique()
                ->values();
            $availableSeats = ($capacity > 0 ? collect(range(1, $capacity)) : collect())
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

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $booking->update(['status' => $validated['status']]);

        if ($validated['status'] === 'cancelled' && is_array($booking->seats)) {
            foreach ($booking->seats as $seat) {
                broadcast(new \App\Events\SeatStatusChanged(
                    $booking->trip_id,
                    $seat['seat'],
                    'available',
                    $booking->passenger_id,
                ))->toOthers();
            }
        }

        broadcast(new BookingUpdated($booking, 'updated'))->toOthers();

        return response()->json($booking);
    }

    public function destroy(Booking $booking)
    {
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

        return response()->json(['success' => true]);
    }
}
