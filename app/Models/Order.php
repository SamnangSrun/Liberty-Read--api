<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'total_price', 'order_status', 'payment_status'];

   

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // In Order.php model
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}


    public function customer()
{
    return $this->belongsTo(User::class, 'customer_id');
}

   public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id', 'id');
    }



}
