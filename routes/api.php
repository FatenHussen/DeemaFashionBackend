<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\User\PopupCampaignController;
use App\Http\Controllers\Webhook\StripeWebhookController;

// Stripe webhooks (no auth — verified via Stripe-Signature)
Route::post('/webhooks/stripe', StripeWebhookController::class);

Route::middleware('auth:user,admin,driver')->group(function () {

    Route::get('/notifications', [NotificationController::class, 'notifications']);

    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);

    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
});


require base_path('routes/api/admin.php');

require base_path('routes/api/user.php');

require base_path('routes/api/driver.php');

require base_path('routes/api/vendor.php');

Route::get('/popups/active', [PopupCampaignController::class, 'active']);
Route::post('/popups/{popupCampaign}/track-view', [PopupCampaignController::class, 'trackView']);
Route::post('/popups/{popupCampaign}/track-click', [PopupCampaignController::class, 'trackClick']);

Route::prefix('socket')->group(base_path('routes/api/socket.php'));
