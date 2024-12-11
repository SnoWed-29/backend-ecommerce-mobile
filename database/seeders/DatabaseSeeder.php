<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Image;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create Users
        $user = User::create([
            'name' => 'User',
            'email' => 'usewr@email',
            'password' => Hash::make('admin@admin')
        ]);

        // Create Categories
        $categories = Category::factory(5)->create(); // Creating 5 categories

        // Create Products
        $products = Product::factory(10)->create(); // Creating 10 products

        // Attach Products to Categories
        foreach ($products as $product) {
            $product->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        }

        // Create Images for Products
        foreach ($products as $product) {
            Image::create([
                'path' => 'https://st3.depositphotos.com/14514162/16904/i/450/depositphotos_169047058-stock-photo-isolated-coffee-cups.jpg', // Example path
                'product_id' => $product->id
            ]);
        }

        // Create Cart for User
        $cart = Cart::create([
            'user_id' => $user->id
        ]);

        // Attach Products to Cart
        foreach ($products->random(5) as $product) { // Pick 5 random products for the cart
            $cart->products()->attach(
                $product->id,
                ['quantity' => rand(1, 3)] // Random quantity between 1 and 3
            );
        }

        // Create Orders for User
        $order = Order::create([
            'city' => 'New York',
            'postal_code' => '10001',
            'Address' => '123 Main St, New York, NY',
            'total_price' => 100.50, // Example total price
            'user_id' => $user->id
        ]);

        // Attach Products to Order (Order Items)
        foreach ($cart->products as $product) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => rand(1, 3) // Random quantity between 1 and 3
            ]);
        }
    }
}
