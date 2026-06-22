<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function checkout()
    {
        $cart = Cart::with('items.product')
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty',
            ], 422);
        }

        $total = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        DB::transaction(function () use ($cart, $total) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-'.now()->format('YmdHis'),
                'total_amount' => $total,
                'payment_status' => Order::PAYMENT_STATUS_PENDING,
                'order_status' => Order::STATUS_PENDING,
            ]);

            foreach ($cart->items as $item) {
                if ($item->product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Not enough stock for product: {$item->product->name}",
                    ]);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();
        });

        return response()->json([
            'message' => 'Order placed successfully',
        ]);
    }
}
