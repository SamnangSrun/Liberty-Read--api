<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // Define the table name (optional, if it doesn't follow conventions)
    // protected $table = 'payments';

    // Define the fillable attributes
protected $fillable = [
    'order_id',
    'payment_method',
    'payment_status',
    'transaction_id',
    'first_name',
    'last_name',
    'country',
    'street_address',
    'town_city',
    'state_county',
    'postcode',
    'phone',
    'email',
    'order_notes', // ✅ Add this
    'visa_card_number',
    'visa_expiry_date',
    'visa_cvc',
    'location',
];


    // You can define relationships here if necessary
    // public function order() {
    //     return $this->belongsTo(Order::class);
    // }
}
