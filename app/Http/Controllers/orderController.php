<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
class orderController extends Controller
{   
    // Route::post('/orders', [orderController::class , 'createOrder']);

    public function createOrder(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'city' => 'required|string|max:255',
        'postal_code' => 'required|string|max:10',
        'Address' => 'required',
    ]);
    
    // Fetch the cart for the provided user ID
    $cart = Cart::where('user_id', $request->user_id)->with('products')->first();

    if (!$cart || $cart->products->isEmpty()) {
        return response()->json(['message' => 'Cart is empty'], 400);
    }

    // Calculate the total price
    $totalPrice = $cart->products->sum(function ($product) {
        return $product->price * $product->pivot->quantity;
    });
    // Create the order
    $order = Order::create([
        'city' => $request->city,
        'postal_code' => $request->postal_code,
        'Address' => $request->Address,
        'total_price' => $totalPrice,
        'user_id' => $request->user_id,
    ]);

    // Add products to the order as order items
    foreach ($cart->products as $product) {
        $order->orderItems()->create([
            'product_id' => $product->id,
            'quantity' => $product->pivot->quantity,
            'price' => $product->price,
        ]);
    }

    // Clear the cart
    $cart->products()->detach();

    return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
}
}
