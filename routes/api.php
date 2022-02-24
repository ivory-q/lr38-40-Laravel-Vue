<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::post("/logout", [UserController::class, "logout"]);
    Route::post('/user', [UserController::class, "show"]);

    Route::controller(ImageController::class)->group(function () {
        Route::post("/photo", "store");
        Route::post("/photo/{ID}", "update");
        Route::get("/photo", "index");
        Route::get("/photo/{ID}", "show");
        Route::delete("/photo/{ID}", "destroy");

        Route::post('/photo/user/{ID}/share', "share");
        Route::post('/photo/user/shared', "shared");
    });
});

Route::post("/signup", [UserController::class, "store"]);
Route::post("/login", [UserController::class, "enter"]);
