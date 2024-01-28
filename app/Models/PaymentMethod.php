<?php

namespace App\Models;

use App\Concerns\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory;
    use Filterable;

    protected $fillable = [
        'name_method',
        'name_user',
        'type_key',
        'key',
        'api_token',
        'refresh_token',
        'expire_in',
        'user_connected',
        'email_connected',
        'status',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
