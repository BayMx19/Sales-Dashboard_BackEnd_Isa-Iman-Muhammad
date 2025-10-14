<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class ProductModel extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'products';
    protected $guarded = ['id'];

    public function transactions()
    {
        return $this->hasMany(TransactionModel::class);
    }
}
