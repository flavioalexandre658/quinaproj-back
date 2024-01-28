<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use App\Models\Payment;
use App\Models\Campaign;
use App\Observers\PaymentObserver;
use App\Observers\CampaignObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('b64image', function ($attribute, $value, $params, $validator) {
            $mimeType = mime_content_type($value);
            if ($this->allowedMimeType($mimeType) && $this->validateBase64($value)) {
                return $mimeType;
            }
        });

        Payment::observe(PaymentObserver::class);
        Campaign::observe(CampaignObserver::class);
    }

    protected function allowedMimeType(string $mimeType): bool
    {
        return ($mimeType == 'image/png' || $mimeType == 'image/jpeg');
    }

    protected function validateBase64(string $base64Img): bool
    {
        $base64String = preg_replace('#^data:image/\w+;base64,#i', '', $base64Img);
        return (base64_encode(base64_decode($base64String, true)) === $base64String);
    }
}
