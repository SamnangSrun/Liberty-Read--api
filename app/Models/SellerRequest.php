<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerRequest extends Model
{
    use HasFactory;

    
protected $fillable = [
    'seller_id',
    'status',
    'name',
    'store_name',
    'birthdate',
    'phone_number',
    'rejection_note',
];


    // Assuming the 'user' relationship exists
    public function user()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
