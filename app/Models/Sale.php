<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\Filterable;
class Sale extends Model
{
    use HasFactory;
    use Filterable;

    protected $fillable = [
        'amount_tickets',
        'amount_tickets_end',
        'price_amount',
        'user_id'
    ];

    public function saleCampaigns(): HasMany
    {
        return $this->hasMany(SaleCampaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
