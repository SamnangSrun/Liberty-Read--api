<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    UserController,
    BookController,
    CategoryController,
    OrderController,
    OrderItemController,
    PaymentController,
    SellerRequestController,
    NotificationController,
    CartController,
    MessageController
};
use App\Models\Payment;

// Public Routes
Route::post('sign-in', [UserController::class, 'signIn']);
Route::post('sign-up', [UserController::class, 'signUp']);
Route::post('request-sellers', [SellerRequestController::class, 'requestSeller']);
Route::get('/books/search', [BookController::class, 'search']);

// Protected Routes (Requires Authentication)
// Route::middleware(['auth:sanctum'])->group(function () {

    // General Authenticated User Routes
    Route::put('/users/{user}', [UserController::class, 'updateProfile']);
    Route::delete('/users/{user}', [UserController::class, 'deleteUser']);
    Route::get('/users', [UserController::class, 'listUsers']);

    // Admin-only routes (Requires Admin Role)
    // Route::middleware(['role:admin'])->group(function () {
        
        Route::put('update-role/{user}', [UserController::class, 'updateRole']);
        
        

        

        // Seller Requests Routes
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::delete('/users/{user}/remove-profile-image', [UserController::class, 'deleteProfileImage']);
            Route::get('/user/contact-history', [MessageController::class, 'userContactHistory']);
            Route::post('/contact-admin', [MessageController::class, 'contact']);
          Route::get('/admin/notifications', [MessageController::class, 'adminNotifications']);
            
           Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    Route::middleware('role:admin')->post('/notifications/send', [NotificationController::class, 'sendToUser']);
    // Endpoint for admin to view all messages:
            Route::get('/admin/messages', [MessageController::class, 'listMessages']);

            Route::get('users', [UserController::class, 'listUsers']);
            Route::post('/request-sellers', [SellerRequestController::class, 'requestSeller']);

            Route::get('/seller-requests/pending', [SellerRequestController::class, 'listPendingRequests']);
            Route::get('/seller-requests/approved', [SellerRequestController::class, 'listApprovedRequests']);
            Route::get('/seller-requests', [SellerRequestController::class, 'viewSellerRequests']);
            Route::post('/seller-requests/{sellerRequest}/approve', [SellerRequestController::class, 'approveSeller']);
            Route::post('/seller-requests/{sellerRequest}/disapprove', [SellerRequestController::class, 'disapproveSeller']);

           
            
            Route::get('books/search-by-category', [BookController::class, 'searchBooksByCategory']);
            Route::delete('/books/{id}', [BookController::class, 'deleteBookById']);
            Route::put('/books/{id}', [BookController::class, 'updateBook']);

            
            Route::post('/cart/add', [CartController::class, 'addToCart']);
            Route::get('/cart', [CartController::class, 'viewCart']);
            Route::put('/cart/item/{id}', [CartController::class, 'updateCartItem']);
            Route::delete('/cart/item/{id}', [CartController::class, 'removeCartItem']);
            Route::post('/orders/placeOrder', [OrderController::class, 'placeOrder']);
    
            // View orders of the authenticated user
            Route::get('/orders/userOrders', [OrderController::class, 'userOrders']);
            Route::post('/orders', [OrderController::class, 'placeOrder']);
            // Update order status (admin or owner)
            Route::put('/orders/{id}/status', [OrderController::class, 'updateOrderStatus']);
            
            // Cancel an order (user only if pending)
            Route::post('/orders/{id}/cancelOrder', [OrderController::class, 'cancelOrder']);
            
            // Delete an order (admin only)
            Route::delete('/orders/{id}/deleteOrder', [OrderController::class, 'deleteOrder']);

            Route::get('/payments/download/{paymentId}', [PaymentController::class, 'downloadPdf']);
            Route::get('/payments', [PaymentController::class, 'index']);
            Route::post('/payments', [PaymentController::class, 'store']);
            Route::put('/payments/{id}', [PaymentController::class, 'update']);
             Route::get('/my-payments', [PaymentController::class, 'userPayments']);

            
   

                 // Seller routes
            Route::get('/books/requested', [BookController::class, 'listAllBooks']);     
            Route::get('/books', [BookController::class, 'listBooks']); //public    
            Route::post('/books', [BookController::class, 'addBook']);
            Route::get('/my-books', [BookController::class, 'myBooks']);
            Route::put('/books/{id}', [BookController::class, 'updateBook']);
            Route::delete('/books/{id}', [BookController::class, 'deleteBookById']);
            Route::get('/seller/sales', [PaymentController::class, 'sellerSales']);
            // Admin routes
            
            Route::put('/books/{book}/approve', [BookController::class, 'approveBook']);
            Route::put('/books/{book}/reject', [BookController::class, 'rejectBook']);

            // Shared
           
            Route::get('/search-books', [BookController::class, 'search']);
            
            

           
            Route::post('/categories', [CategoryController::class, 'createCategory']);
            Route::put('/categories/{id}', [CategoryController::class, 'updateCategory']);
            Route::delete('/categories/{id}', [CategoryController::class, 'deleteCategory']);
            Route::get('/categories/search/{name}', [CategoryController::class, 'searchByName']);

           


    // });
        });

        Route::get('/books/{id}', [BookController::class, 'viewBook']);
        Route::get('books', [BookController::class, 'listBooks']);
          // View all seller requests;
         Route::get('/categories', [CategoryController::class, 'getAllCategories']);

    // Common Resource Routes
    // Route::apiResource('books', BookController::class)->except(['destroy']);
   

    Route::get('/payments/history/{email}', [PaymentController::class, 'history']);
   
   
    Route::apiResource('payments', PaymentController::class)->only(['store', 'show']);
    Route::apiResource('notifications', NotificationController::class)->only(['store', 'show', 'update']);
    Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
// });
