<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'menu';

    public $timestamps = false;

    protected $fillable = [
        'menunummer',
        'menu_toevoeging',
        'naam',
        'price',
        'soortgerecht',
        'beschrijving',
        'is_actief',
    ];

    protected $casts = [
        'menunummer' => 'integer',
        'price' => 'float',
        'is_actief' => 'boolean',
    ];
}
