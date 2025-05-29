<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return auth()->user()->notifications()->latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        Notification::create([
            'user_id' => $request->user_id,
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'Notification sent successfully.']);
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notification->update(['status' => 'read']);

        return response()->json(['message' => 'Notification marked as read.']);
    }
}
