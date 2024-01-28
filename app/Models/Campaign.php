<?php

namespace App\Models;

use App\Concerns\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Campaign extends Model
{
    use HasFactory;
    use Filterable;
    use HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name') // Nome do campo do qual você deseja gerar o slug
            ->saveSlugsTo('slug'); // Nome do campo onde o slug será armazenado
    }

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'image',
        'amount_tickets',
        'available_tickets',
        'pending_tickets',
        'unavailable_tickets',
        'support_number',
        'status',
        'closed',
        'released_until_fee',
        'sorted_number',
        'price_each_ticket',
        'min_ticket',
        'max_ticket',
        'show_date_of_raffle',
        'show_email_input',
        'show_top_ranking',
        'show_filters',
        'order_numbers',
        'ranking_acumulative',
        'visible',
        'dark_mode',
        'date_of_raffle',
        'time_wait_payment',
        'allow_terms',
        'url',
        'slug',
        'winner_collaborator_id',
        'category_id',
        'ticket_filter_id',
        'raffle_id',
        'fee_id',
        'user_id'
    ];

    public function getTimeWaitPaymentInMinutes()
    {
        $value = $this->time_wait_payment;
        $unit = substr($value, -1);
        $amount = (int) substr($value, 0, -1);

        if ($unit === 'd') {
            return $amount * 24 * 60; // Converter dias em minutos
        } elseif ($unit === 'm') {
            return $amount; // Minutos
        }

        return 0; // Valor inválido ou desconhecido
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ticketFilter(): BelongsTo
    {
        return $this->belongsTo(TicketFilter::class);
    }

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function winnerCollaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function awardCampaigns(): HasMany
    {
        return $this->hasMany(AwardCampaign::class);
    }

    public function saleCampaigns(): HasMany
    {
        return $this->hasMany(SaleCampaign::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function collaborators(): HasMany
    {
        return $this->hasMany(Collaborator::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
