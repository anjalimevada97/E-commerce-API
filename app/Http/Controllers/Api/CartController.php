<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    /**
     * Add a cart item to the user's cart.
     *
     * @return JsonResponse
     */
    public function addCartItem(CartItemRequest $request)
    {
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        $data = $request->validated();

        $item = CartItem::firstOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $data['product_id'],
            ],
            [
                'quantity' => 0,
            ]
        );

        $item->increment('quantity', $data['quantity']);

        return response()->json([
            'message' => 'Cart item added successfully',
        ]);
    }

    /**
     * Get the contents of the user's cart.
     */
    public function index()
    {
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        $items = CartItem::with('product')
            ->where('cart_id', $cart->id)
            ->get();

        return response()->json([$items]);
    }

    /**
     * Decrease the quantity of a cart item.
     *
     * @return JsonResponse
     */
    public function remove(CartItemRequest $request)
    {
        $data = $request->validated();

        $item = $this->getCartItem($data['product_id']);

        if (! $item) {
            return response()->json([
                'message' => 'Item not found in cart',
            ], 404);
        }

        $item->decrement('quantity', $data['quantity']);

        if ($item->fresh()->quantity <= 0) {
            $item->delete();
        }

        return response()->json([
            'message' => 'Cart item quantity updated',
        ]);
    }

    /**
     * Remove a cart item from the user's cart.
     */
    public function removeCartItem(CartItem $cartItem)
    {
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        $item = CartItem::where('id', $cartItem->id)
            ->where('cart_id', $cart->id)
            ->first();

        if (! $item) {
            return response()->json([
                'message' => 'Item not found in cart',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'message' => 'Cart item removed successfully',
        ]);
    }

    private function getCartItem(int $productId): ?CartItem
    {
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        return CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();
    }
}
