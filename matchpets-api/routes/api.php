<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatMsgController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('/search', [UserController::class, 'show']);
Route::post('/updateUser', [UserController::class, 'updateUser']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/pets', [PetController::class, 'pets']);
Route::post('/getAllPets', [PetController::class, 'getAllPets']);
Route::post('/createPet', [PetController::class, 'createPet']);
Route::post('/deletePet', [PetController::class, 'deletePet']);
Route::post('/insertPets', [UserController::class, 'insertPets']);
Route::post('/createMatch', [MatchController::class, 'createMatch']);
Route::post('/getMatch', [MatchController::class, 'getMatch']);
Route::post('/sendMessage', [ChatMsgController::class, 'sendMessage']);
Route::post('/getMessage', [ChatMsgController::class, 'getMessages']);
Route::post('/acceptRequest', [MatchController::class, 'acceptRequest']);
Route::post('/rejectRequest', [MatchController::class, 'rejectRequest']);
Route::post('/getAcceptedFriends', [MatchController::class, 'getAcceptedFriends']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});