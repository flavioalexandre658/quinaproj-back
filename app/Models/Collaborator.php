<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\Filterable;
class Collaborator extends Model
{
    use HasFactory;
    use Filterable;

    protected $fillable = [
        'uuid',
        'name',
        'phone',
        'email',
        'url_checkout',
        'amount_of_tickets',
        'price_each_ticket',
        'status_payment',
        'campaign_id',
        'expire_date',
        'allow_terms',
        'numbers'
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(function (Collaborator $collaborator) {
            /*$campaignId = $collaborator['campaign_id'];
            $day_of_changes = '2024-01-13 00:00:01';
            $campaign = Campaign::find($campaignId);
            $digits = strlen((string)($campaign->amount_tickets - 1));
            if($digits < 4 || ($campaign->created_at <  $day_of_changes)) {
                if ($collaborator->isDirty('status_payment') && $collaborator['status_payment'] === 1) {




                    $collaborator->tickets()->update(['status' => "-1"]);

                    $availableTickets = $campaign->tickets()->where('status', "1")->count();
                    $pendingTickets = $campaign->tickets()->where('status', "0")->count();
                    $unavailableTickets = $campaign->tickets()->where('status', "-1")->count();

                    $campaign->update([
                        'available_tickets' => $availableTickets,
                        'pending_tickets' => $pendingTickets,
                        'unavailable_tickets' => $unavailableTickets
                    ]);

                } else if ($collaborator->isDirty('status_payment') && $collaborator['status_payment'] === -1) {
                    $collaborator->tickets()->update(['status' => "1"]);

                    $campaignId = $collaborator['campaign_id'];

                    $campaign = Campaign::find($campaignId);

                    $availableTickets = $campaign->tickets()->where('status', "1")->count();
                    $pendingTickets = $campaign->tickets()->where('status', "0")->count();
                    $unavailableTickets = $campaign->tickets()->where('status', "-1")->count();

                    $campaign->update([
                        'available_tickets' => $availableTickets,
                        'pending_tickets' => $pendingTickets,
                        'unavailable_tickets' => $unavailableTickets
                    ]);
                } else if ($collaborator->isDirty('status_payment') && $collaborator['status_payment'] === 0) {
                    $collaborator->tickets()->update(['status' => "0"]);

                    $campaignId = $collaborator['campaign_id'];

                    $campaign = Campaign::find($campaignId);

                    $availableTickets = $campaign->tickets()->where('status', "1")->count();
                    $pendingTickets = $campaign->tickets()->where('status', "0")->count();
                    $unavailableTickets = $campaign->tickets()->where('status', "-1")->count();

                    $campaign->update([
                        'available_tickets' => $availableTickets,
                        'pending_tickets' => $pendingTickets,
                        'unavailable_tickets' => $unavailableTickets
                    ]);
                }
            }*/
        });


        static::created(function ($collaborator) {

            $campaign = Campaign::find($collaborator->campaign_id);

            if ($campaign) {
                $timeWaitPayment = $campaign->time_wait_payment;
                $expireDate = Carbon::now('America/Sao_Paulo');

                if (str_ends_with($timeWaitPayment, 'd')) {
                    $days = (int) substr($timeWaitPayment, 0, -1);
                    $expireDate->addDays($days);
                } elseif (str_ends_with($timeWaitPayment, 'm')) {
                    $minutes = (int) substr($timeWaitPayment, 0, -1);
                    $expireDate->addMinutes($minutes);
                }

                $collaborator->expire_date = $expireDate;
                $campaign->update(['pending_tickets' => $campaign->pending_tickets + $collaborator->amount_of_tickets]);
                $collaborator->save();
            }
        });

        static::deleting(function ($collaborator) {
            // Deleta os pagamentos associados ao colaborador
            $collaborator->payments()->delete();
        });

    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

}
