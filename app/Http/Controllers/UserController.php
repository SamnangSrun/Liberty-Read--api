<?php

namespace App\Http\Controllers;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Sign In
    public function signIn(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    // Sign Up
    public function signUp(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $imageUrl = null;
        $imageId = null;

        if ($request->hasFile('profile_image')) {
            $uploadedImage = Cloudinary::upload($request->file('profile_image')->getRealPath());
            $imageUrl = $uploadedImage->getSecurePath();
            $imageId = $uploadedImage->getPublicId();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'profile_image_url' => $imageUrl,
            'profile_image_id' => $imageId,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User created successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    // Update Profile
    public function updateProfile(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        if ($request->hasFile('profile_image')) {
            // Delete old image on Cloudinary if exists
            if ($user->profile_image_id) {
                Cloudinary::destroy($user->profile_image_id);
            }

            // Upload new image to Cloudinary
            $uploadedImage = Cloudinary::upload($request->file('profile_image')->getRealPath());
            $user->profile_image_url = $uploadedImage->getSecurePath();
            $user->profile_image_id = $uploadedImage->getPublicId();
        }

        // Update other user data except profile_image fields
        $user->fill($request->except(['profile_image', 'profile_image_url', 'profile_image_id']));
        $user->save();

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    // Delete User
    public function deleteUser(User $user)
    {
        // Delete profile image from Cloudinary if exists
        if ($user->profile_image_id) {
            Cloudinary::destroy($user->profile_image_id);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    // List Users (Admin only)
    public function listUsers(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::latest()->get();

        return response()->json(['users' => $users]);
    }

    // Delete only user's profile image
    public function deleteProfileImage(Request $request, User $user)
    {
        if ($request->user()->id !== $user->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->profile_image_id) {
            Cloudinary::destroy($user->profile_image_id);
        }

        $user->profile_image_url = null;
        $user->profile_image_id = null;
        $user->save();

        return response()->json(['message' => 'Profile image deleted successfully', 'user' => $user]);
    }
}
