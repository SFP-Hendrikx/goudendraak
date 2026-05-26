<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Offer extends Model
{
    protected $table = 'aanbiedingen';

    protected $fillable = [
        'titel',
        'beschrijving',
        'startdatum',
        'einddatum',
        'is_actief',
    ];

    protected $casts = [
        'startdatum' => 'date',
        'einddatum' => 'date',
        'is_actief' => 'boolean',
    ];

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(
            MenuItem::class,
            'aanbieding_menu',
            'aanbieding_id',
            'menu_item_id'
        )
            ->withPivot('actieprijs')
            ->withTimestamps();
    }

    public function scopeVisibleThisWeek(Builder $query): Builder
    {
        $today = Carbon::parse(now()->toDateString());

        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();

        return $query
            ->where('is_actief', true)
            ->whereDate('startdatum', '<=', $weekEnd)
            ->whereDate('einddatum', '>=', $weekStart);
    }
}
