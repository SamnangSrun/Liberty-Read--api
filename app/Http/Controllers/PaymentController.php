<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class PaymentController extends Controller
{
   public function sellerSales()
{
    $sellerId = Auth::id(); // Assuming seller is authenticated

    $orderItems = OrderItem::with(['order.user', 'order.payments', 'book'])
        ->whereHas('book', function ($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        })
        ->get();

    $groupedOrders = $orderItems->groupBy('order_id')->map(function ($items) {
        $order = $items->first()->order;
        $buyer = $order->user;

        return [
            'order_id' => $order->id,
            'buyer_name' => $buyer->name ?? 'Guest',
            'buyer_email' => $buyer->email ?? null,
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,
            'order_date' => $order->created_at,

           

            'total_earned' => $items->sum(function ($item) {
                return $item->price * $item->quantity;
            }),
            'books' => $items->map(function ($item) {
                return [
                    'name' => $item->book->name ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'cover_image' => $item->book->cover_image,
                ];
            })->values(),
            'payments' => $order->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_method' => $payment->payment_method,
                    'payment_status' => $payment->payment_status,
                    'transaction_id' => $payment->transaction_id,
                     // ✅ Address & phone
                    'country' => $payment->country ?? 'N/A',
                    'street_address' => $payment->street_address ?? 'N/A',
                    'town_city' => $payment->town_city ?? 'N/A',
                    'state_county' => $payment->state_county ?? 'N/A',
                    'phone' => $payment->phone ?? 'N/A',
                    'order_notes' => !empty($payment->order_notes) && strtolower(trim($payment->order_notes)) !== 'null' 
                        ? $payment->order_notes 
                        : 'No notes available',
                ];
            })->values(),
        ];
    })->values();

    return response()->json([
        'sales' => $groupedOrders
    ]);
}


    public function history($email)
    {
        $payments = Payment::where('email', $email)->orderBy('created_at', 'desc')->get();
        return response()->json(['payments' => $payments]);
    }

    public function index()
    {
        $payments = Payment::all();
        return response()->json(['payments' => $payments]);
    }

public function store(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'payment_method' => 'required|in:card,cash',
        'payment_status' => 'required|in:completed,pending',
        'transaction_id' => 'nullable|string|max:255',
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'country' => 'required|string|max:255',
        'street_address' => 'required|string|max:255',
        'town_city' => 'required|string|max:255',
        'state_county' => 'required|string|max:255',
        'postcode' => 'required|string|max:20',
        'phone' => 'required|string|max:20',
        'email' => 'required|email|max:255',
        'order_notes' => 'nullable|string|max:1000',
        'visa_card_number' => 'required_if:payment_method,card|nullable|string|max:20',
        'visa_expiry_date' => 'required_if:payment_method,card|nullable|string|max:10',
        'visa_cvc' => 'required_if:payment_method,card|nullable|string|max:4',
        'location' => 'nullable|string|max:255',
    ]);

    DB::beginTransaction();

    try {
        $userId = Auth::check() ? Auth::id() : null;

        $safeData = $request->only([
            'order_id', 'payment_method', 'payment_status', 'transaction_id',
            'first_name', 'last_name', 'country', 'street_address',
            'town_city', 'state_county', 'postcode', 'phone', 'email',
            'order_notes', 'location'
        ]);
        $safeData['user_id'] = $userId;

        // Only store the last 4 digits of the card
        if ($request->payment_method === 'card') {
            $safeData['visa_card_number'] = substr($request->visa_card_number, -4);
        }

        // Create the payment record
        $payment = Payment::create($safeData);

        // Update order payment_status conditionally
        $order = Order::findOrFail($request->order_id);

        if ($request->payment_method === 'card' && $request->payment_status === 'paid') {
            $order->update(['payment_status' => 'paid']);
        } else {
            $order->update(['payment_status' => 'unpaid']);
        }

        // Calculate seller earnings
        $orderItems = $order->orderItems()->with('book')->get();
        $sellerAmounts = [];

        foreach ($orderItems as $item) {
            $sellerId = $item->book->seller_id;
            $amount = $item->price * $item->quantity;

            if (!isset($sellerAmounts[$sellerId])) {
                $sellerAmounts[$sellerId] = 0;
            }

            $sellerAmounts[$sellerId] += $amount;
        }

        // Store seller payments
        foreach ($sellerAmounts as $sellerId => $amount) {
            SellerPayment::create([
                'seller_id' => $sellerId,
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'amount' => $amount,
            ]);
        }

        // Clear user cart if logged in
        if ($userId) {
            DB::table('cart_items')->where('user_id', $userId)->delete();
        }

        DB::commit();

        return response()->json([
            'message' => 'Payment successful',
            'payment' => $payment
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Payment failed',
            'details' => config('app.debug') ? $e->getMessage() : 'Something went wrong.'
        ], 500);
    }
}


}
