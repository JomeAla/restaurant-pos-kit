<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ComboController;
use App\Http\Controllers\Api\FloorPlanController;
use App\Http\Controllers\Api\KitchenController;
use App\Http\Controllers\Api\InventoryItemController;
use App\Http\Controllers\Api\InventoryTransactionController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\RecipeItemController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\ModifierController;
use App\Http\Controllers\Api\ModifierOptionController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\TicketMessageController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\TicketCategoryController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\PaymentGatewayWebhookController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\PrintController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\ExportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/pin', [AuthController::class, 'pinLogin']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

        Route::get('/users/roles', [UserController::class, 'roles']);
        Route::apiResource('users', UserController::class);
        Route::get('/roles/{role}/permissions', [RoleController::class, 'permissions']);
        Route::put('/roles/{role}/permissions', [RoleController::class, 'updatePermissions']);
        Route::apiResource('roles', RoleController::class);

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('menu-items', MenuItemController::class);
        Route::put('/menu-items/{menu_item}/toggle-availability', [MenuItemController::class, 'toggleAvailability']);
        Route::apiResource('modifiers', ModifierController::class);
        Route::get('/modifiers/groups/list', [ModifierController::class, 'groups']);
        Route::apiResource('modifier-options', ModifierOptionController::class);
        Route::apiResource('combos', ComboController::class);

        Route::apiResource('tables', TableController::class);
        Route::put('/tables/{restaurant_table}/status', [TableController::class, 'updateStatus']);
        Route::apiResource('floor-plans', FloorPlanController::class);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::put('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
        Route::post('/orders/{order}/items', [OrderController::class, 'addItem']);
        Route::put('/orders/{order}/items/{item}', [OrderController::class, 'updateItem']);
        Route::delete('/orders/{order}/items/{item}', [OrderController::class, 'removeItem']);
        Route::post('/orders/{order}/hold', [OrderController::class, 'hold']);
        Route::post('/orders/{order}/release', [OrderController::class, 'release']);
        Route::post('/orders/{order}/send-kitchen', [KitchenController::class, 'sendToKitchen']);
        Route::get('/kitchen/orders', [KitchenController::class, 'index']);
        Route::put('/kitchen/tickets/{ticket}/items/{item}/status', [KitchenController::class, 'updateItemStatus']);
        Route::post('/kitchen/tickets/{ticket}/bump', [KitchenController::class, 'bump']);

        Route::post('/payments', [PaymentController::class, 'store']);
        Route::post('/payments/create-intent', [PaymentController::class, 'createPaymentIntent']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
        Route::post('/payments/split', [PaymentController::class, 'split']);
        Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund']);
        Route::post('/orders/{order}/close', [PaymentController::class, 'closeOrder']);

        Route::get('/reservations/available-slots', [ReservationController::class, 'availableSlots']);

        Route::get('/inventory/categories', [InventoryItemController::class, 'categories']);
        Route::apiResource('inventory', InventoryItemController::class);
        Route::get('/inventory-transactions', [InventoryTransactionController::class, 'index']);
        Route::post('/inventory/stock-in', [InventoryTransactionController::class, 'stockIn']);
        Route::post('/inventory/stock-out', [InventoryTransactionController::class, 'stockOut']);
        Route::post('/inventory/adjust', [InventoryTransactionController::class, 'adjust']);
        Route::apiResource('purchase-orders', PurchaseOrderController::class);
        Route::post('/purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive']);
        Route::apiResource('recipe-items', RecipeItemController::class);

        Route::get('/reports/sales', [ReportsController::class, 'sales']);
        Route::get('/reports/popular-items', [ReportsController::class, 'popularItems']);
        Route::get('/reports/profit-margins', [ReportsController::class, 'profitMargins']);
        Route::get('/reports/staff-performance', [ReportsController::class, 'staffPerformance']);
        Route::get('/reports/payment-methods', [ReportsController::class, 'paymentMethods']);
        Route::get('/reports/peak-hours', [ReportsController::class, 'peakHours']);
        Route::get('/reservations', [ReservationController::class, 'index']);
        Route::post('/reservations', [ReservationController::class, 'store']);
        Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);
        Route::put('/reservations/{reservation}', [ReservationController::class, 'update']);
        Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);

        Route::get('/settings', [SettingsController::class, 'index']);
        Route::put('/settings', [SettingsController::class, 'update']);

        Route::post('/upload', [UploadController::class, 'upload']);

        Route::apiResource('coupons', CouponController::class);
        Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);

        Route::get('/support/tickets', [SupportTicketController::class, 'index']);
        Route::post('/support/tickets', [SupportTicketController::class, 'store']);
        Route::get('/support/tickets/{ticket}', [SupportTicketController::class, 'show']);
        Route::put('/support/tickets/{ticket}', [SupportTicketController::class, 'update']);
        Route::delete('/support/tickets/{ticket}', [SupportTicketController::class, 'destroy']);
        Route::put('/support/tickets/{ticket}/status', [SupportTicketController::class, 'status']);
        Route::get('/support/tickets/{ticket}/messages', [TicketMessageController::class, 'index']);
        Route::post('/support/tickets/{ticket}/messages', [TicketMessageController::class, 'store']);
        Route::apiResource('support/faq', FaqController::class);
        Route::apiResource('ticket-categories', TicketCategoryController::class);

        Route::get('/payment-gateways', [PaymentGatewayController::class, 'index']);
        Route::get('/payment-gateways/stripe/config', [PaymentGatewayController::class, 'stripeConfig']);
        Route::get('/payment-gateways/{gateway}', [PaymentGatewayController::class, 'show']);
        Route::put('/payment-gateways/{gateway}', [PaymentGatewayController::class, 'update']);
        Route::post('/payment-gateways/{gateway}/test', [PaymentGatewayController::class, 'test']);
        Route::get('/payment-gateways/{gateway}/logs', [PaymentGatewayController::class, 'logs']);
        Route::get('/audit-log', [ActivityLogController::class, 'index']);
        Route::post('/print/receipt', [PrintController::class, 'receipt']);
        Route::post('/print/kitchen-ticket', [PrintController::class, 'kitchenTicket']);
        Route::post('/sync/orders', [SyncController::class, 'orders']);
        Route::get('/export/orders', [ExportController::class, 'orders']);
        Route::get('/export/inventory', [ExportController::class, 'inventory']);
        Route::get('/export/payments', [ExportController::class, 'payments']);
        Route::get('/export/menu-items', [ExportController::class, 'menuItems']);
    });

    Route::post('/webhooks/paystack', [PaymentGatewayWebhookController::class, 'handlePaystack'])->withoutMiddleware([\App\Http\Middleware\LogActivity::class]);
    Route::post('/webhooks/stripe', [PaymentGatewayWebhookController::class, 'handleStripe'])->withoutMiddleware([\App\Http\Middleware\LogActivity::class]);
});
