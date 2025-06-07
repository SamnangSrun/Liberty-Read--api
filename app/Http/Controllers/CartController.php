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
    $request->validate([
        'book_id' => 'required|exists:books,id',
        'quantity' => 'required|integer|min:1',
    ]);

    $user = auth()->user();
    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    $book = Book::find($request->book_id);
    if (!$book || $book->status !== 'approved') {
        return response()->json(['message' => 'Book not available for sale'], 400);
    }

    // Ensure the user has a cart
    $cart = Cart::firstOrCreate(['user_id' => $user->id]);

    // Check if item already exists in cart
    $cartItem = $cart->items()->where('book_id', $book->id)->first();

    $requestedQuantity = $request->quantity;
    $existingQuantityInCart = $cartItem ? $cartItem->quantity : 0;
    $totalRequestedQuantity = $existingQuantityInCart + $requestedQuantity;

    if ($totalRequestedQuantity > $book->stock) {
        return response()->json([
            'message' => 'Not enough stock available. Only ' . $book->stock . ' left.',
        ], 400);
    }

    $price = $book->price;

    if ($cartItem) {
        $cartItem->update([
            'quantity' => $totalRequestedQuantity,
            'price' => $price,
        ]);
    } else {
        $cart->items()->create([
            'book_id' => $book->id,
            'quantity' => $requestedQuantity,
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
