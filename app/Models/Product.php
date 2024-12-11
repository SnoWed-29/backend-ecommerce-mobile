<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_category', 'product_id', 'category_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function cartProducts()
    {
        return $this->belongsToMany(Cart::class, 'cart_product')->withPivot('quantity');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}