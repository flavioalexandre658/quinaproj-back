<?php

namespace App\Models;


use App\Concerns\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AwardCampaign extends Model
{
    use HasFactory;
    use Filterable;

    protected $fillable = [
        'award_id',
        'campaign_id',
        'collaborator_id',
        'position',
        'sorted_number'
    ];

    public function award(): BelongsTo
    {
        return $this->belongsTo(Award::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }
}
