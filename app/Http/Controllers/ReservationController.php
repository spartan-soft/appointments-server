<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Payment;

use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['user', 'client', 'service'])->get()->map(function ($reservation) {
                $reservation->remaining_amount = $reservation->mount - ($reservation->advancement ?? 0);
                return $reservation;
            });

        return response()->json($reservations);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'amount' => 'required|numeric',
            'reservation_time' => 'required|date_format:Y-m-d H:i:s',
            'reservation_end_time' => 'required|date_format:Y-m-d H:i:s|after:reservation_time',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($this->hasConflictingReservation($request->reservation_time, $request->reservation_end_time)) {
            return response()->json(['message' => 'Reservation time conflicts with an existing reservation'], 409);
        }

       
        try {
           $reservation = Reservation::create($request->all());
        } catch (\Throwable $th) { 
            return response()->json(['message' => 'Reservation failed','error'=>$request->all()], 500);
        }
        

        return response()->json($reservation, 201);
    }

    public function show($id)
    {
        $reservation = Reservation::with(['user', 'client', 'service'])->findOrFail($id);

        return response()->json($reservation);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:users,id',
            'client_id' => 'sometimes|exists:clients,id',
            'service_id' => 'sometimes|exists:services,id',
            'amount' => 'sometimes|numeric',
            'advancement' => 'nullable|numeric',
            'reservation_time' => 'sometimes|date_format:Y-m-d H:i:s',
            'reservation_end_time' => 'sometimes|date_format:Y-m-d H:i:s|after:reservation_time',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($this->hasConflictingReservation($request->reservation_time, $request->reservation_end_time)) {
            return response()->json(['message' => 'Reservation time conflicts with an existing reservation'], 409);
        }

        $reservation = Reservation::findOrFail($id);
        $reservation->update($request->all());

        return response()->json($reservation);
    }

    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return response()->json(null, 204);
    }

    protected function hasConflictingReservation($startTime, $endTime, $exceptId = null)
    {
        $query = Reservation::where(function ($query) use ($startTime, $endTime) {
            $query->where('reservation_time', '<', $endTime)
                ->where('reservation_end_time', '>', $startTime);
        });

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }
}

