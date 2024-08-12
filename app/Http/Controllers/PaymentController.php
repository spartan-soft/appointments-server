<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\PaymentRequest;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getQR(Request $request)
    {
        $amount = $request->query('amount');

        $data=[
            'currency' => 'BOB', 
            'gloss' => 'Pago', 
            'amount' => $amount, 
            'singleUse' => true, 
            'expirationDate' => date('Y-m-d'),
            'destinationAccountId' => '1' 
        ];

        $token=Http::post(env('BASE_URL').'ClientAuthentication.API/api/v1/auth/token',[
            'accountId' => env('ACCOUNT_ID'),
            'authorizationId' => env('AUTHORIZATION_ID'),
        ]);
       
        if($token->successful()){

            $token = json_decode($token);
            $qrImage= Http::withToken($token->message)->post(env('BASE_URL').'QRSimple.API/api/v1/main/getQRWithImageAsync',$data);

            if($qrImage->successful()){
                return response($qrImage->body(), $qrImage->status())->header('Content-Type', 'application/json');
            }else{
                return response()->json(['error' => 'Request failed'], $response->status());
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {

        $reservation = Reservation::findOrFail($request->reservation_id);

        DB::beginTransaction();
        
        $paidQR = Payment::create($request->all());
        
        // Update user quantity
        $user = User::findOrFail($reservation->user_id);
        $user->amount += $request->amount;
        $user->save();

        // Sum all reservation payments
        $sumPayments = Payment::where('reservation_id',$request->reservation_id)->get()->sum('amount');
        
        // Update reservation status
        if($reservation->amount == $sumPayments){
            $reservation->state = true;
            $reservation->save();
        }

        DB::commit();

        return response()->json(['message' => 'Payment successful']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
