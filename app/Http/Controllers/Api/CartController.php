<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    /**
     * Add a product to the user's cart.
     *
     * @return JsonResponse
     */
    public function add(Product $product)
    {
        $cart = auth()->user()->cart;

        $item = CartItem::firstOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $product->id,
            ],
            [
                'quantity' => 0,
            ]
        );

        $item->increment('quantity');

        return response()->json([
            'message' => 'Product added to cart',
        ]);
    }

    /**
     * Get the contents of the user's cart.
     */
    public function index()
    {
        $cart = auth()->user()->cart;

        $items = CartItem::with('product')
            ->where('cart_id', $cart->id)
            ->get();

        return response()->json([$items]);
    }

    /**
     * Remove a product from the user's cart.
     *
     * @return JsonResponse
     */
    public function remove(Product $product)
    {
        $cart = auth()->user()->cart;

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->decrement('quantity');

            if ($item->quantity <= 0) {
                $item->delete();
            }
        }

        return response()->json([
            'message' => 'Product removed from cart',
        ]);
    }
}
