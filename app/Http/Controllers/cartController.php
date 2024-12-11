<?php

namespace App\Http\Controllers;

use App\Models\CartProduct;
use App\Models\Cart;
use Illuminate\Http\Request;

class cartController extends Controller
{
    public function addedToCart(Request $request)
    {
        
        $data = $request->validate([
            'user_id' => ['required'],
            'product_id' => ['required'],
            'quantity' => ['nullable', 'integer']
        ]);

        
        $quantity = $data['quantity'] ?? 1;

        
        $cart = Cart::where('user_id', $data['user_id'])->first() ?? Cart::create([
            'user_id' => $data['user_id']
        ]);

       
        $cartProduct = CartProduct::where('cart_id', $cart->id)
            ->where('product_id', $data['product_id'])
            ->first(); 

        if ($cartProduct) {
            
            $cartProduct->quantity += $quantity; 
            $cartProduct->save(); 
            return response()->json(['message' => 'Product quantity updated in the cart', 'cart_product' => $cartProduct], 200);
        } else {
            
            $newCartProduct = CartProduct::create([
                'cart_id' => $cart->id,
                'product_id' => $data['product_id'],
                'quantity' => $quantity
            ]);
            return response()->json(['message' => 'Product added to the cart', 'cart_product' => $newCartProduct], 200);
        }
    }
    public function getCartItems($user_id){

        $cart = Cart::where('user_id', $user_id)->first();
        if(!$cart){

            $cart = Cart::create([
                'user_id' =>$user_id
                ]);     
        }
        $cartProduct = Cart::where('user_id', $user_id)->first()->products()->get();

        if($cartProduct->isEmpty()){
            
            return response()->json(['error'=> 'Your cart is empty , added some product to see results'],400);
        }
        return response()->json($cartProduct);
    }
}
