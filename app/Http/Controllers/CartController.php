<?php
// app/Http/Controllers/CartController.php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Add to Cart Method
    
public function addToCart(Request $request)
{
    // Validate incoming request
    $request->validate([
        'book_id' => 'required|exists:books,id',
        'quantity' => 'required|integer|min:1',
    ]);

    // Get authenticated user
    $user = auth()->user();
    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    // Retrieve the book
    $book = Book::find($request->book_id);
    if (!$book || $book->status !== 'approved') {
        return response()->json(['message' => 'Book not available for sale'], 400);
    }

    $price = $book->price;

    // Ensure the user has a cart
    $cart = Cart::firstOrCreate(['user_id' => $user->id]);

    // Check if item already exists in cart
    $cartItem = $cart->items()->where('book_id', $book->id)->first();

    if ($cartItem) {
        // Update quantity and price
        $cartItem->update([
            'quantity' => $cartItem->quantity + $request->quantity,
            'price' => $price, // you can calculate total price here if needed
        ]);
    } else {
        // Create a new cart item
        $cart->items()->create([
            'book_id' => $book->id,
            'quantity' => $request->quantity,
            'price' => $price,
        ]);
    }

    return response()->json(['message' => 'Item added to cart'], 200);
}

    // View Cart Method
    public function viewCart()
    {
        $user = auth()->user();
    
        if (!$user || !$user->cart) {
            return response()->json(['message' => 'No items in the cart'], 404);
        }
    
        $cartItems = $user->cart->items()->with('book')->get();
    
        return response()->json($cartItems);
    }

    public function updateCartItem(Request $request, $itemId)
{
    $request->validate([
        'quantity' => 'required|integer|min:1',
    ]);

    $user = auth()->user();
    $cartItem = CartItem::whereHas('cart', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->where('id', $itemId)->first();

    if (!$cartItem) {
        return response()->json(['message' => 'Item not found in your cart'], 404);
    }

    $cartItem->update([
        'quantity' => $request->quantity,
        'price' => $cartItem->book->price,
    ]);

    return response()->json(['message' => 'Cart item updated successfully']);
}
public function removeCartItem($itemId)
{
    $user = auth()->user();
    $cartItem = CartItem::whereHas('cart', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->where('id', $itemId)->first();

    if (!$cartItem) {
        return response()->json(['message' => 'Item not found in your cart'], 404);
    }

    $cartItem->delete();

    return response()->json(['message' => 'Cart item removed successfully']);
}

}
