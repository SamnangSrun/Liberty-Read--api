<?php

namespace App\Http\Controllers;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

   
$imagePath = null;
if ($request->hasFile('profile_image')) {
    $uploadedFileUrl = Cloudinary::upload($request->file('profile_image')->getRealPath())->getSecurePath();
    $imagePath = $uploadedFileUrl;
}

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'customer',
        'profile_image' => $imagePath,
    ]);

    // Generate token after successful user creation
    $token = $user->createToken('auth_token')->plainTextToken;

    // Return the token and user details
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

        // Check if the user is uploading a new profile image
        if ($request->hasFile('profile_image')) {
            // Delete the old profile image from the storage if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            // Store the new profile image
            $user->profile_image = $request->file('profile_image')->store('profile_images', 'public');
        }

        // Update the user's data excluding profile_image
        $user->update($request->except('profile_image'));

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    // Delete User
    public function deleteUser(User $user)
    {
        // Delete the profile image from storage if exists
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Delete the user from the database
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

   public function listUsers(Request $request)
{
    $user = $request->user(); // Get authenticated user

    if (!$user || $user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $users = User::latest()->get(); // Always fetch latest data

    return response()->json(['users' => $users]);
}

// Delete only the user's profile image
public function deleteProfileImage(Request $request, User $user)
{
    if ($request->user()->id !== $user->id && $request->user()->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    if ($user->profile_image) {
        Storage::disk('public')->delete($user->profile_image);
        $user->profile_image = null;
        $user->save();
    }

    return response()->json(['message' => 'Profile image deleted successfully', 'user' => $user]);
}

}