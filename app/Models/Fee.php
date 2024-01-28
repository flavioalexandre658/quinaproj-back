<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\Filterable;
class Fee extends Model
{
    use HasFactory;
    use Filterable;

    protected $fillable = [
        'revenue',
        'min_revenue',
        'max_revenue',
        'fee'
    ];

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}
