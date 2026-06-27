<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_actief', true);
    }

    public function scopeOrderedForMenu(Builder $query): Builder
    {
        return $query
            ->orderBy('soortgerecht')
            ->orderBy('menunummer')
            ->orderBy('menu_toevoeging')
            ->orderBy('naam');
    }

    public function fullMenuNumber(): string
    {
        return trim((string) $this->menunummer . (string) $this->menu_toevoeging);
    }
}
