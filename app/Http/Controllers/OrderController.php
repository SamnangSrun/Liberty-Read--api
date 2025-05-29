<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
//   public function sellerSales()
// {
//     $sellerId = Auth::id(); // assuming seller is authenticated

//     $orderItems = OrderItem::with(['order.user', 'book'])
//         ->whereHas('book', function ($query) use ($sellerId) {
//             $query->where('seller_id', $sellerId);
//         })
//         ->get();

//     $groupedOrders = $orderItems->groupBy('order_id')->map(function ($items) {
//         $order = $items->first()->order;
//         $buyer = $order->user;

//         return [
//             'order_id' => $order->id,
//             'buyer_name' => $buyer->name ?? 'Guest',
//             'buyer_email' => $buyer->email ?? null,
//             'payment_status' => $order->payment_status,
//             'order_status' => $order->order_status,
//             'order_date' => $order->created_at,
//             'total_earned' => $items->sum(function ($item) {
//                 return $item->price * $item->quantity;
//             }),
//             'books' => $items->map(function ($item) {
//                 return [
//                     'name' => $item->book->name ?? 'Unknown',
//                     'quantity' => $item->quantity,
//                     'price' => $item->price,
//                 ];
//             }),
//             'payments' => $items->map(function ($item) {
//                 return [
//                    'order_notes' => $payment->order_note ?? 'null',
                    
//                 ];
//             }),
//         ];
//     })->values();

//     return response()->json([
//         'sales' => $groupedOrders
//     ]);
// }
 // In OrderController.php
public function userOrders()
{
    $orders = Order::with('orderItems.book')
        ->where('user_id', auth()->id())
        ->get();

    return response()->json($orders);
}

    // 1. Place order from cart
    public function placeOrder()
    {
        $user = auth()->user();
        $cart = $user->cart()->with('items')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        DB::beginTransaction();

        try {
            $total = $cart->items->sum(fn($item) => $item->quantity * $item->price);

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $total,
                'order_status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $item->book_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            $cart->items()->delete(); // Clear the cart after order

            DB::commit();

            return response()->json(['message' => 'Order placed successfully', 'order' => $order], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Order failed', 'details' => $e->getMessage()], 500);
        }
    }

    
    // 3. Update order status (admin or owner)
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'order_status' => 'required|in:pending,processing,shipped,delivered,canceled,',
        ]);

        $order = Order::findOrFail($id);
        $user = auth()->user();

        if ($order->user_id !== $user->id && !in_array($user->role, ['admin', 'seller'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        

        if (in_array($order->order_status, ['delivered', 'canceled'])) {
            return response()->json(['error' => 'Cannot update a completed or canceled order'], 400);
        }

        $order->update(['order_status' => $request->order_status]);

        return response()->json(['message' => 'Order status updated', 'order' => $order]);
    }

    // 4. Cancel order (user only if pending)
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);
        $user = auth()->user();

        if ($order->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($order->order_status !== 'pending') {
            return response()->json(['error' => 'Only pending orders can be canceled'], 400);
        }

        $order->update(['order_status' => 'canceled']);

        return response()->json(['message' => 'Order canceled successfully', 'order' => $order]);
    }

    // 5. Delete order (admin only)
    public function deleteOrder($id)
    {
        $user = auth()->user();
        $order = Order::findOrFail($id);

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Only admin can delete orders'], 403);
        }

        if (!in_array($order->order_status, ['pending', 'canceled'])) {
            return response()->json(['error' => 'Can only delete pending or canceled orders'], 400);
        }

        $order->orderItems()->delete();
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}
