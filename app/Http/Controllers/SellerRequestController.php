<?php

namespace App\Http\Controllers;

use App\Models\SellerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerRequestController extends Controller
{
    // Submit a request to become a seller
    public function requestSeller(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->role !== 'customer') {
            return response()->json(['message' => 'Only customers can request to become sellers'], 403);
        }

        $existingRequest = SellerRequest::where('seller_id', $user->id)
                                        ->where('status', 'pending')
                                        ->first();

        if ($existingRequest) {
            return response()->json(['message' => 'You already have a pending seller request'], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255',
            'birthdate' => 'required|date',
            'phone_number' => 'required|string|max:20',
        ]);

        $sellerRequest = SellerRequest::create([
            'seller_id' => $user->id,
            'status' => 'pending',
            'name' => $validated['name'],
            'store_name' => $validated['store_name'],
            'birthdate' => $validated['birthdate'],
            'phone_number' => $validated['phone_number'],
        ]);

        return response()->json([
            'message' => 'Seller request submitted successfully',
            'data' => $sellerRequest
        ], 201);
    }

    // View all seller requests (admin only)
public function viewSellerRequests()
{
    $user = Auth::user();

    if ($user && $user->role === 'admin') {
        $requests = SellerRequest::with('user')->get();

        $data = $requests->map(function($request) {
            return [
                'id' => $request->id,
                'status' => $request->status,
                'rejection_note' => $request->rejection_note, // ✅ Corrected
                'name' => $request->name,
                'store_name' => $request->store_name,
                'birthdate' => $request->birthdate,
                'phone_number' => $request->phone_number,
                'user' => [
                    'id' => $request->user->id,
                    'name' => $request->user->name,
                    'email' => $request->user->email,
                    'role' => $request->user->role,
                    'profile_image' => $request->user->profile_image,
                ]
            ];
        });

        return response()->json(['data' => $data], 200);
    }

    return response()->json(['message' => 'Unauthorized'], 403);
}



    // Approve a seller request (admin only)
    public function approveSeller(SellerRequest $sellerRequest)
    {
        $admin = Auth::user();

        if ($admin && $admin->role === 'admin') {
            $sellerRequest->status = 'approved';
            $sellerRequest->rejection_note = null;
            $sellerRequest->save();

            $user = $sellerRequest->user;
            if ($user) {
                $user->role = 'seller';
                $user->save();

                return response()->json([
                    'message' => 'Seller request approved and user role updated to seller',
                    'data' => $user,
                ], 200);
            }

            return response()->json([
                'message' => 'Seller request approved but user not found',
            ], 404);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Reject a seller request (admin only)
    public function disapproveSeller(Request $request, SellerRequest $sellerRequest)
    {
        $admin = Auth::user();

        if ($admin && $admin->role === 'admin') {
            $validated = $request->validate([
                'rejection_note' => 'required|string|max:255',
            ]);

            $sellerRequest->status = 'disapproved';
            $sellerRequest->rejection_note = $validated['rejection_note'];
            $sellerRequest->save();

            return response()->json(['message' => 'Seller request disapproved with note'], 200);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Update a seller request (owner only, while pending)
    public function updateSellerRequest(Request $request, SellerRequest $sellerRequest)
    {
        $user = Auth::user();

        if ($user->id !== $sellerRequest->seller_id || $sellerRequest->status !== 'pending') {
            return response()->json(['message' => 'Unauthorized or request is not editable'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255',
            'birthdate' => 'required|date',
            'phone_number' => 'required|string|max:20',
        ]);

        $sellerRequest->update($validated);

        return response()->json([
            'message' => 'Seller request updated',
            'data' => $sellerRequest
        ], 200);
    }

    // Delete a seller request (owner only, while pending)
    public function deleteSellerRequest(SellerRequest $sellerRequest)
    {
        $user = Auth::user();

        if ($user->id !== $sellerRequest->seller_id || $sellerRequest->status !== 'pending') {
            return response()->json(['message' => 'Unauthorized or request cannot be deleted'], 403);
        }

        $sellerRequest->delete();

        return response()->json(['message' => 'Seller request deleted'], 200);
    }
    

    // View all pending seller requests (admin only)
public function listPendingRequests()
{
    $user = Auth::user();

    if ($user && $user->role === 'admin') {
        $pendingRequests = SellerRequest::with('user')
            ->where('status', 'pending')
            ->get();

        $data = $pendingRequests->map(function ($request) {
            return [
                'id' => $request->id,
                'status' => $request->status,
                'name' => $request->name,
                'store_name' => $request->store_name,
                'birthdate' => $request->birthdate,
                'phone_number' => $request->phone_number,
                'user' => [
                    'id' => $request->user->id,
                    'name' => $request->user->name,
                    'email' => $request->user->email,
                    'role' => $request->user->role,
                    'profile_image' => $request->user->profile_image,
                ],
            ];
        });

        return response()->json(['data' => $data], 200);
    }

    return response()->json(['message' => 'Unauthorized'], 403);
}


// View all approved seller requests (admin only)
public function listApprovedRequests()
{
    $user = Auth::user();

    if ($user && $user->role === 'admin') {
        $approvedRequests = SellerRequest::with('user')
            ->where('status', 'approved')
            ->get();

        $data = $approvedRequests->map(function ($request) {
            return [
                'id' => $request->id,
                'status' => $request->status,
                'name' => $request->name,
                'store_name' => $request->store_name,
                'birthdate' => $request->birthdate,
                'phone_number' => $request->phone_number,
                'user' => [
                    'id' => $request->user->id,
                    'name' => $request->user->name,
                    'email' => $request->user->email,
                    'role' => $request->user->role,
                    'profile_image' => $request->user->profile_image,
                ],
            ];
        });

        return response()->json(['data' => $data], 200);
    }

    return response()->json(['message' => 'Unauthorized'], 403);
}

}
