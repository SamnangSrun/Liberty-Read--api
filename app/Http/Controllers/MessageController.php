<?php

namespace App\Http\Controllers;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
   
   public function contact(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Find admin (assuming role = 'admin')
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        $notification = Notification::create([
            'user_id' => $admin->id, // Admin receives it
            'message' => $request->message,
            'status' => 'unread',
        ]);

        return response()->json([
            'message' => 'Message sent to admin successfully',
            'notification' => $notification
        ]);
    }

    // Admin: view all notifications sent to them
    public function adminNotifications()
    {
        $admin = Auth::user();

        if ($admin->role !== 'admin') {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $notifications = Notification::where('user_id', $admin->id)
            ->latest()
            ->get();

        return response()->json($notifications);
    }

    public function userContactHistory()
{
    $user = Auth::user();

    $messages = Notification::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($messages);
}
}
