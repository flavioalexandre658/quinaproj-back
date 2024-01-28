<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\Filterable;

class Award extends Model
{
    use HasFactory;
    use Filterable;

    protected $fillable = [
        'name',
        'user_id'
    ];

    public function awardCampaigns(): HasMany
    {
        return $this->hasMany(AwardCampaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
