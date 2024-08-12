<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JWTController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserAcountController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("register", [JWTController::class, "register"]);
Route::post("login", [JWTController::class, "login"]);

Route::group([
    "middleware" => ["auth:api"]
], function () {

    Route::get("profile", [JWTController::class, "profile"]);
    Route::get("refresh", [JWTController::class, "refreshToken"]);
    Route::get("logout", [JWTController::class, "logout"]);
    Route::put('users/{id}', [JWTController::class, 'update']);
    Route::delete('user-delete/{id}', [JWTController::class, 'deleteUserById']);

    Route::get('services', [ServiceController::class, 'index']);
    Route::post('services', [ServiceController::class, 'store']);
    Route::get('services/{id}', [ServiceController::class, 'show']);
    Route::get('users/{userId}/services', [ServiceController::class, 'getUserServices']);
    Route::get('services/{serviceId}/users', [ServiceController::class, 'getServiceUsers']);
    Route::post('servicesUser', [ServiceController::class, 'associateUserToService']);
    Route::put('services/{id}', [ServiceController::class, 'update']);
    Route::delete('services/{id}', [ServiceController::class, 'destroy']);

    Route::apiResource('reservations', ReservationController::class);


    Route::apiResource('clients', ClientController::class);
    
    Route::get('payment/qr', [PaymentController::class, 'getQR']) ;
    Route::post('payment',[PaymentController::class,'store']);

// Ruta para obtener los datos de la cuenta de un usuario específico
    Route::get('/users/{userId}/account', [UserAcountController::class, 'show']);

// Ruta para que un usuario realice un retiro de dinero
    Route::post('/users/{userId}/withdraw', [UserAcountController::class, 'withdraw']);

});

