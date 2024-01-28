<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Payment;
use App\Models\SocialMedia;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $latestPayment = Payment::orderBy('id', 'desc')->first();
        $latestSocialMedia = SocialMedia::orderBy('id', 'desc')->first();


        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'phone' => $this->faker->phoneNumber,
            'nickname' => $this->faker->userName,
            'image' => $this->faker->imageUrl(),
            'delete_account' => 0,
            'text_delete_account' => $this->faker->sentence,
            'payment_id' => $latestPayment ? $latestPayment->id : null,
            'social_media_id' => $latestSocialMedia ? $latestSocialMedia->id : null,
            'remember_token' => null,
        ];
    }
}
