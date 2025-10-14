<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class TransactionModel extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'transactions';
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
