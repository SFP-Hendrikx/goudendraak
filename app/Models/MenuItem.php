<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(
            Offer::class,
            'aanbieding_menu',
            'menu_item_id',
            'aanbieding_id'
        )
            ->withPivot('actieprijs')
            ->withTimestamps();
    }
}
