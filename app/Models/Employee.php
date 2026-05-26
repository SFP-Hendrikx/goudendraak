<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $table = 'medewerkers';

    protected $fillable = [
        'naam',
        'is_actief',
    ];

    protected $casts = [
        'is_actief' => 'boolean',
    ];

    public function planningRules(): HasMany
    {
        return $this->hasMany(PlanningRule::class, 'medewerker_id');
    }
}
