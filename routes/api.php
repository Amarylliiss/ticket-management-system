<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::patch('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('agents', [AgentController::class, 'index']);
    Route::get('statistics/response-times', [StatisticsController::class, 'responseTimes']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read']);

    Route::get('tickets', [TicketController::class, 'index']);
    Route::post('tickets', [TicketController::class, 'store']);
    Route::get('tickets/{ticket}', [TicketController::class, 'show']);
    Route::patch('tickets/{ticket}/assign', [TicketController::class, 'assign']);
    Route::patch('tickets/{ticket}/status', [TicketController::class, 'updateStatus']);
    Route::patch('tickets/{ticket}/priority', [TicketController::class, 'updatePriority']);
    Route::post('tickets/{ticket}/comments', [CommentController::class, 'store']);
    Route::post('tickets/{ticket}/attachments', [AttachmentController::class, 'store']);

    Route::get('attachments/{attachment}', [AttachmentController::class, 'download']);
});
