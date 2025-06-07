<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'author',
        'description',
        'price',
        'cover_image',
        'category_id',
        'seller_id',
        'status',
        'reject_note',
        'stock',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addBook()
    {
        // Add book logic here
    }

    public function editBook()
    {
        // Add book edit logic here
    }

    public function approveBook()
    {
        // Add approve book logic here
    }

    public function disapproveBook()
    {
        // Add disapprove book logic here
    }
    public function user()
    {
        // assuming 'user_id' is the foreign key in books table
        return $this->belongsTo(User::class, 'user_id');
    }
}
