<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsNominal extends Model
{
    use HasFactory;

    protected $table = 'products_nominal';

    protected $fillable = [
        'id',
        'position_id',
        'nominal'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'nominal' => 'integer'
    ];

}
