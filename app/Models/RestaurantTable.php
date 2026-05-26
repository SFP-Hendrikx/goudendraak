<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RestaurantTable extends Model
{
    protected $table = 'restaurant_tafels';

    protected $fillable = [
        'tafelnummer',
        'is_actief',
    ];

    protected $casts = [
        'tafelnummer' => 'integer',
        'is_actief' => 'boolean',
    ];

    public function planningRules(): BelongsToMany
    {
        return $this->belongsToMany(
            PlanningRule::class,
            'planning_regel_tafel',
            'restaurant_tafel_id',
            'planning_regel_id'
        )->withTimestamps();
    }
}
