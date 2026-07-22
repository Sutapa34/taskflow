<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('organizations', OrganizationController::class)->except(['store', 'destroy']);
    Route::post('/organizations/{organization}/invite', [OrganizationController::class, 'invite']);

    Route::apiResource('organizations.projects', ProjectController::class)->shallow();
    Route::apiResource('projects.tasks', TaskController::class)->shallow();
});
