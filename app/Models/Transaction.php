<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_type',
        'amount',
        'fee',
        'date',

    ];
    protected $attributes = [];
    protected $hidden = [];
    protected $casts = [];

    public function user()
    {
        return  $this->hasOne(User::class, 'id', 'user_id');
    }
}