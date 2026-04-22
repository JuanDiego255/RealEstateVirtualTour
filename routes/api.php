<?php

use App\Http\Controllers\CloudConvertWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('api/webhook/cloudconvert', [CloudConvertWebhookController::class, 'handle'])
    ->name('cloudconvert.webhook');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/vehicle-inquiries', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name'                => 'required|string|max:150',
        'phone'               => 'required|string|max:30',
        'email'               => 'nullable|email|max:150',
        'vehicle_description' => 'required|string|max:1000',
    ]);

    \Illuminate\Support\Facades\DB::table('vehicle_inquiries')->insert([
        'name'                => $data['name'],
        'phone'               => $data['phone'],
        'email'               => $data['email'] ?? null,
        'vehicle_description' => $data['vehicle_description'],
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Solicitud recibida correctamente'], 201);
})->name('api.vehicle-inquiries');

// Flutter app login: POST /api/login
Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    if (!\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    $user = \Illuminate\Support\Facades\Auth::user();
    // Stateless token: deterministic HMAC so no extra schema/package needed
    $token = hash_hmac('sha256', $user->email . '|' . $user->id, config('app.key'));

    return response()->json(['token' => $token, 'user' => ['name' => $user->name, 'email' => $user->email]]);
})->name('api.login');

// Flutter app lead form: POST /api/leads
Route::post('/leads', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name'      => 'required|string|max:150',
        'phone'     => 'required|string|max:30',
        'email'     => 'nullable|email|max:150',
        'message'   => 'nullable|string|max:1000',
        'tour_type' => 'nullable|string|max:80',
    ]);

    $description = trim(($data['tour_type'] ?? '') . "\n" . ($data['message'] ?? ''));

    \Illuminate\Support\Facades\DB::table('vehicle_inquiries')->insert([
        'name'                => $data['name'],
        'phone'               => $data['phone'],
        'email'               => $data['email'] ?? null,
        'vehicle_description' => $description,
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);

    return response()->json(['success' => true, 'message' => '¡Mensaje enviado! Nos pondremos en contacto pronto.'], 201);
})->name('api.leads');
