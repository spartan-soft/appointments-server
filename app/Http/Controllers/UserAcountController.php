<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use App\Models\UserWithdrawal;
use Illuminate\Http\Request;

class UserAcountController extends Controller
{
    public function show($userId)
    {
        $user = User::findOrFail($userId);

        $reservations = Reservation::where('user_id', $userId)->get();

        $totalAdvancement = $reservations->sum('advancement');
        $totalRemaining = $reservations->sum('remaining_amount');
        $totalManaged = $reservations->sum('mount');

        $withdrawals = UserWithdrawal::where('user_id', $userId)->get();
        $totalWithdrawn = $withdrawals->sum('amount');

        $data = [
            'total_advancement' => $totalAdvancement,
            'total_remaining' => $totalRemaining,
            'total_managed' => $totalManaged - $totalWithdrawn,
            'withdrawals' => $withdrawals,
        ];

        return response()->json($data);
    }

    public function withdraw(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $amount = $request->input('amount');

        // Realizar validaciones adicionales si es necesario

        $withdrawal = new UserWithdrawal([
            'user_id' => $userId,
            'amount' => $amount,
            'withdrawal_date' => now(),
        ]);

        $withdrawal->save();

        return response()->json(['message' => 'Withdrawal successful']);
    }
}
