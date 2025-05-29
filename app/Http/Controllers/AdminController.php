<?php

// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\SellerRequest;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // View all users
    public function viewAllUsers()
    {
        $users = User::all();
        return response()->json($users);
    }

    // View all books
    public function viewAllBooks()
    {
        $books = Book::all();
        return response()->json($books);
    }

    // Approve a book
    public function approveBook($id)
    {
        $book = Book::findOrFail($id);
        $book->status = 'approved';
        $book->save();
        return response()->json(['message' => 'Book approved successfully']);
    }

    // Disapprove a book
    public function disapproveBook($id)
    {
        $book = Book::findOrFail($id);
        $book->status = 'disapproved';
        $book->save();
        return response()->json(['message' => 'Book disapproved']);
    }

    // Manage seller requests
    public function manageSellerRequests()
    {
        $requests = SellerRequest::all();
        return response()->json($requests);
    }

    // Approve seller
    public function approveSeller($id)
    {
        $request = SellerRequest::findOrFail($id);
        $request->status = 'approved';
        $request->save();

        $user = User::findOrFail($request->seller_id);
        $user->role = 'seller';
        $user->save();

        return response()->json(['message' => 'Seller approved']);
    }

    // Disapprove seller
    public function disapproveSeller($id)
    {
        $request = SellerRequest::findOrFail($id);
        $request->status = 'disapproved';
        $request->save();

        return response()->json(['message' => 'Seller disapproved']);
    }

    // View all orders
    public function manageOrders()
    {
        $orders = Order::all();
        return response()->json($orders);
    }

    // View all categories
    public function manageCategories()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // Send a system-wide notification
    public function sendSystemNotification(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255'
        ]);

        $users = User::all();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'message' => $validated['message'],
                'status' => 'unread',
            ]);
        }

        return response()->json(['message' => 'System notification sent']);
    }

    // View all notifications
    public function viewAllNotifications()
    {
        $notifications = Notification::all();
        return response()->json($notifications);
    }
}

