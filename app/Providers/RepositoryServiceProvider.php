<?php

namespace App\Providers;

use App\Interfaces\CampaignRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\FeeRepositoryInterface;
use App\Interfaces\RaffleRepositoryInterface;
use App\Interfaces\TicketFilterRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\AwardCampaignRepositoryInterface;
use App\Interfaces\AwardRepositoryInterface;
use App\Interfaces\RoleRepositoryInterface;
use App\Interfaces\CollaboratorRepositoryInterface;
use App\Interfaces\PaymentRepositoryInterface;
use App\Interfaces\SaleCampaignRepositoryInterface;
use App\Interfaces\SaleRepositoryInterface;
use App\Interfaces\SocialMediaRepositoryInterface;
use App\Interfaces\CustomizationRepositoryInterface;
use App\Interfaces\TicketRepositoryInterface;
use App\Interfaces\PaymentMethodRepositoryInterface;
use App\Interfaces\StatisticsRepositoryInterface;

use Illuminate\Support\ServiceProvider;
use App\Repositories\CampaignRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\FeeRepository;
use App\Repositories\RaffleRepository;
use App\Repositories\TicketFilterRepository;
use App\Repositories\UserRepository;
use App\Repositories\AwardCampaignRepository;
use App\Repositories\AwardRepository;
use App\Repositories\RoleRepository;
use App\Repositories\CollaboratorRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\SaleCampaignRepository;
use App\Repositories\SaleRepository;
use App\Repositories\SocialMediaRepository;
use App\Repositories\CustomizationRepository;
use App\Repositories\TicketRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\StatisticsRepository;
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CampaignRepositoryInterface::class, CampaignRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(FeeRepositoryInterface::class, FeeRepository::class);
        $this->app->bind(RaffleRepositoryInterface::class, RaffleRepository::class);
        $this->app->bind(TicketFilterRepositoryInterface::class, TicketFilterRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AwardCampaignRepositoryInterface::class, AwardCampaignRepository::class);
        $this->app->bind(AwardRepositoryInterface::class, AwardRepository::class);
        $this->app->bind(CollaboratorRepositoryInterface::class, CollaboratorRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(SaleCampaignRepositoryInterface::class, SaleCampaignRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
        $this->app->bind(SocialMediaRepositoryInterface::class, SocialMediaRepository::class);
        $this->app->bind(CustomizationRepositoryInterface::class, CustomizationRepository::class);
        $this->app->bind(TicketRepositoryInterface::class, TicketRepository::class);
        $this->app->bind(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(StatisticsRepositoryInterface::class, StatisticsRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
