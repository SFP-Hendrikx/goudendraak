<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlanningRule extends Model
{
    protected $table = 'planning_regels';

    protected $fillable = [
        'datum',
        'dienst',
        'medewerker_id',
        'opmerking',
    ];

    protected $casts = [
        'datum' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'medewerker_id');
    }

    public function tables(): BelongsToMany
    {
        return $this->belongsToMany(
            RestaurantTable::class,
            'planning_regel_tafel',
            'planning_regel_id',
            'restaurant_tafel_id'
        )->withTimestamps();
    }
}
