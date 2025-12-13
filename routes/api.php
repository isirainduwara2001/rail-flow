<?php

use App\Http\Controllers\DisasterHistoryController;
use App\Http\Controllers\ObjectDetectionController;
use App\Http\Controllers\IotDataController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/iot/history', [IotDataController::class, 'store']);
Route::get('/iot/latest', [IotDataController::class, 'latest']);
Route::post('/settings/update', [IotDataController::class, 'updateSettings']);
Route::post('/notifications/enroll', [NotificationController::class, 'store']);
Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::get('/disaster-history', [DisasterHistoryController::class, 'apiIndex']);
Route::post('/disaster-history', [DisasterHistoryController::class, 'store']);
Route::post('/object-detection', [ObjectDetectionController::class, 'store']);
Route::get('/iot/nearest-station', [IotDataController::class, 'getNearestStation']);
