<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ItemController as AdminItemController;
use App\Http\Controllers\Api\V1\Admin\MediaController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\SubscriptionPlanController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Api\V1\LibraryController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\SubscriptionPlanController as PublicSubscriptionPlanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    */

    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:login');

        Route::get(
            '/email/verify/{id}',
            [AuthController::class, 'verifyEmail']
        )->middleware('signed')->name('verification.verify');

        Route::post(
            '/email/verification-notification',
            [AuthController::class, 'resendVerification']
        )->middleware('throttle:6,1');

        Route::post(
            '/password/forgot',
            [AuthController::class, 'forgotPassword']
        )->middleware('throttle:6,1');

        Route::post(
            '/password/reset',
            [AuthController::class, 'resetPassword']
        )->middleware('throttle:6,1');
    });

    // Items
    Route::get('/items', [ItemController::class, 'index']);
    Route::get('/items/{item:slug}', [ItemController::class, 'show']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show']);

    // Subscription Plans
    Route::get(
        '/subscription-plans',
        [PublicSubscriptionPlanController::class, 'index']
    );

    // Payment Callback
    Route::get(
        '/payments/{payment}/callback',
        [PaymentController::class, 'callback']
    );

    /*
    |--------------------------------------------------------------------------
    | Authenticated Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        // Authentication
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Library
        Route::get('/library', [LibraryController::class, 'index']);

        // Item Access
        Route::post(
            '/items/{item:slug}/access',
            [ItemController::class, 'claimFree']
        );

        Route::get(
            '/items/{item:slug}/access',
            [ItemController::class, 'access']
        );

        Route::get(
            '/items/{item:slug}/content',
            [ItemController::class, 'content']
        );

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

        // Payments
        Route::post(
            '/orders/{order}/payments',
            [PaymentController::class, 'store']
        );

        Route::post(
            '/payments/{payment}/initiate',
            [PaymentController::class, 'initiate']
        );

        // Subscriptions
        Route::get(
            '/subscriptions',
            [SubscriptionController::class, 'index']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:sanctum', 'admin'])
        ->prefix('admin')
        ->group(function () {

            // Dashboard
            Route::get(
                '/dashboard',
                [DashboardController::class, 'index']
            );

            // Items
            Route::apiResource(
                'items',
                AdminItemController::class
            );

            // Categories
            Route::apiResource(
                'categories',
                AdminCategoryController::class
            );

            // Item Media
            Route::post(
                '/items/{item}/media',
                [MediaController::class, 'store']
            );

            Route::delete(
                '/items/{item}/media/{media}',
                [MediaController::class, 'destroy']
            );

            Route::patch(
                '/items/{item}/media/{media}/primary',
                [MediaController::class, 'setPrimary']
            );

            // Orders
            Route::get(
                '/orders',
                [AdminOrderController::class, 'index']
            );

            Route::get(
                '/orders/{order}',
                [AdminOrderController::class, 'show']
            );

            // Users
            Route::get(
                '/users',
                [AdminUserController::class, 'index']
            );

            Route::get(
                '/users/{user}',
                [AdminUserController::class, 'show']
            );

            Route::patch(
                '/users/{user}',
                [AdminUserController::class, 'update']
            );

            // Subscription Plans
            Route::apiResource(
                'subscription-plans',
                SubscriptionPlanController::class
            )->only([
                'index',
                'store',
                'show',
                'update',
            ]);
        });
});
