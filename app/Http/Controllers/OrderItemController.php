<?php

// app/Http/Controllers/OrderItemController.php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function addOrderItem(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
        ]);

        $orderItem = OrderItem::create($request->all());

        return response()->json(['message' => 'Order item added successfully', 'order_item' => $orderItem]);
    }

    public function viewOrderItem(OrderItem $orderItem)
    {
        return response()->json(['order_item' => $orderItem]);
    }
}
