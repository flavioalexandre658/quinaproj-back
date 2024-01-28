<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TicketFilterController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\RaffleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AwardCampaignController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\CollaboratorController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SaleCampaignController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SocialMediaController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CustomizationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatisticsController;

use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::post('/create/user', [UserController::class, 'create']);

Route::post('/create/payment', [PaymentController::class, 'create']);
Route::get('/payments', [PaymentController::class, 'getAll']);
Route::get('/payment/{id}', [PaymentController::class, 'get']);
Route::get('/payment/transaction/{id}', [PaymentController::class, 'getByTransaction']);
Route::get('/payment/campaign/{id}', [PaymentController::class, 'getByCampaignId']);
Route::get('/payment/collaborator/{id}', [PaymentController::class, 'getByCollaboratorId']);
Route::get('/payment/user/{id}', [PaymentController::class, 'getByUserId']);
Route::get('/payment/campaign/{campaignId}/collaborator/{collaboratorId}', [PaymentController::class, 'getByCampaignAndCollaboratorId']);
Route::get('/payment/campaign/{campaignId}/user/{userId}', [PaymentController::class, 'getByCampaignAndUserId']);

Route::post('/create/collaborator', [CollaboratorController::class, 'create']);
Route::get('/collaborators', [CollaboratorController::class, 'getAll']);
Route::get('/collaborators/campaign/{id}', [CollaboratorController::class, 'getByIntervalDate']);

Route::get('/collaborator/{id}', [CollaboratorController::class, 'get']);
Route::get('/collaborator/uuid/{id}', [CollaboratorController::class, 'getByUuid']);
Route::get('/collaborators/campaigns/{id}/{phone?}', [CollaboratorController::class, 'getByCampaignId']);
Route::get('/campaign/{id}/collaborators/tickets/{status}', [CollaboratorController::class, 'getTicketsCollaborators']);

Route::get('/campaigns', [CampaignController::class, 'getAll']);
Route::get('/campaign/{id}', [CampaignController::class, 'get']);
Route::get('/campaign/uuid/{uuid}', [CampaignController::class, 'getByUuid']);
Route::get('/campaign/slug/{slug}', [CampaignController::class, 'getBySlug']);

Route::post('/payment/checkout/pro', [MercadoPagoController::class, 'processPaymentCheckoutPro']);
Route::post('/payment/pix', [MercadoPagoController::class, 'processPaymentPix']);
Route::get('/mercadopago/payment/{id}', [MercadoPagoController::class, 'getPaymentById']);
Route::get('/mercadopago/preference/{id}', [MercadoPagoController::class, 'getPreferenceById']);
Route::post('/mercadopago/callback', [MercadoPagoController::class, 'callbackPayment']);
Route::post('/mercadopago/callback/preference', [MercadoPagoController::class, 'callbackPreference']);
Route::post('/mercadopago/ipn', [MercadoPagoController::class, 'ipn']);
Route::post('/mercadopago/auth/token', [MercadoPagoController::class, 'oAuthToken']);

Route::get('/active/account/{token}', [UserController::class, 'active']);
Route::get('/email/{email}/activation/{token}', [UserController::class, 'sendMailActivation']);

Route::get('/email/{email}/reset/password', [UserController::class, 'sendMailResetPassword']);
Route::put('/reset/password/{id}', [UserController::class, 'resetPassword']);

Route::post('/renew/token', [AuthController::class, 'renewToken']);

Route::middleware('auth:api')->group(function () {

    Route::get('/campaigns/user/uuid/{uuid}/{phone?}', [CampaignController::class, 'getByUserUuid']);

    Route::post('/create/customization', [CustomizationController::class, 'create']);
    Route::get('/customizations', [CustomizationController::class, 'getAll']);
    Route::get('/customization/{id}', [CustomizationController::class, 'get']);
    Route::get('/customization/user/uuid/{uuid}', [CustomizationController::class, 'getByUserUuid']);
    Route::put('/update/customization/{id}', [CustomizationController::class, 'update']);
    Route::delete('/delete/customization/{id}', [CustomizationController::class, 'delete']);


    Route::post('/create/payment/method', [PaymentMethodController::class, 'create']);
    Route::get('/payment/methods', [PaymentMethodController::class, 'getAll']);
    Route::get('/payment/method/{id}', [PaymentMethodController::class, 'get']);
    Route::get('/payment/method/user/uuid/{uuid}', [PaymentMethodController::class, 'getByUserUuid']);
    Route::put('/update/payment/method/{id}', [PaymentMethodController::class, 'update']);
    Route::delete('/delete/payment/method/{id}', [PaymentMethodController::class, 'delete']);

    Route::post('/create/campaign', [CampaignController::class, 'create']);
    Route::put('/update/campaign/{id}', [CampaignController::class, 'update']);
    Route::put('/publish/action/{id}', [CampaignController::class, 'publish']);
    Route::delete('/delete/campaign/{id}', [CampaignController::class, 'delete']);
    Route::delete('/delete/campaign/file/{id}', [CampaignController::class, 'deleteCampaignFile']);

    Route::post('/create/category', [CategoryController::class, 'create']);
    Route::get('/categories', [CategoryController::class, 'getAll']);
    Route::get('/category/{id}', [CategoryController::class, 'get']);
    Route::put('/update/category/{id}', [CategoryController::class, 'update']);
    Route::delete('/delete/category/{id}', [CategoryController::class, 'delete']);

    Route::post('/create/ticket/filter', [TicketFilterController::class, 'create']);
    Route::get('/ticket/filters', [TicketFilterController::class, 'getAll']);
    Route::get('/ticket/filter/{id}', [TicketFilterController::class, 'get']);
    Route::put('/update/ticket/filter/{id}', [TicketFilterController::class, 'update']);
    Route::delete('/delete/ticket/filter/{id}', [TicketFilterController::class, 'delete']);

    Route::post('/create/fee', [FeeController::class, 'create']);
    Route::get('/fees', [FeeController::class, 'getAll']);
    Route::get('/fee/{id}', [FeeController::class, 'get']);
    Route::put('/update/fee/{id}', [FeeController::class, 'update']);
    Route::delete('/delete/fee/{id}', [FeeController::class, 'delete']);

    Route::post('/create/raffle', [RaffleController::class, 'create']);
    Route::get('/raffles', [RaffleController::class, 'getAll']);
    Route::get('/raffle/{id}', [RaffleController::class, 'get']);
    Route::put('/update/raffle/{id}', [RaffleController::class, 'update']);
    Route::delete('/delete/raffle/{id}', [RaffleController::class, 'delete']);

    Route::get('/users', [UserController::class, 'getAll']);
    Route::get('/user/{id}', [UserController::class, 'get']);

    Route::get('/user/uuid/{id}', [UserController::class, 'getByUuid']);
    Route::put('/update/user/{id}', [UserController::class, 'update']);
    Route::put('/set/discount/{id}', [UserController::class, 'setDiscount']);
    Route::delete('/delete/user/{id}', [UserController::class, 'delete']);

    Route::post('/create/award/campaign', [AwardCampaignController::class, 'create']);
    Route::get('/award/campaigns', [AwardCampaignController::class, 'getAll']);
    Route::get('/award/campaign/{id}', [AwardCampaignController::class, 'getByCampaignId']);
    Route::put('/update/award/campaign/{id}', [AwardCampaignController::class, 'update']);
    Route::delete('/delete/award/campaign/{id}', [AwardCampaignController::class, 'delete']);

    Route::post('/create/award', [AwardController::class, 'create']);
    Route::get('/awards', [AwardController::class, 'getAll']);
    Route::get('/award/{id}', [AwardController::class, 'get']);
    Route::get('/awards/user/uuid/{uuid}', [AwardController::class, 'getByUserUuid']);
    Route::put('/update/award/{id}', [AwardController::class, 'update']);
    Route::delete('/delete/award/{id}', [AwardController::class, 'delete']);


    Route::put('/update/collaborator/{id}', [CollaboratorController::class, 'update']);
    Route::delete('/delete/collaborator/{id}', [CollaboratorController::class, 'delete']);


    Route::put('/set/status/transaction/{id}', [PaymentController::class, 'update']);
    Route::put('/update/payment/{id}', [PaymentController::class, 'update']);
    //Route::delete('/delete/payment/{id}', [PaymentController::class, 'delete']);

    Route::post('/create/sale/campaign', [SaleCampaignController::class, 'create']);
    Route::get('/sale/campaigns', [SaleCampaignController::class, 'getAll']);
    Route::get('/sale/campaign/{id}', [SaleCampaignController::class, 'getByCampaignId']);
    Route::put('/update/sale/campaign/{id}', [SaleCampaignController::class, 'update']);
    Route::delete('/delete/sale/campaign/{id}', [SaleCampaignController::class, 'delete']);

    Route::post('/create/sale', [SaleController::class, 'create']);
    Route::get('/sales', [SaleController::class, 'getAll']);
    Route::get('/sale/{id}', [SaleController::class, 'get']);
    Route::get('/sales/user/uuid/{uuid}', [SaleController::class, 'getByUserUuid']);
    Route::put('/update/sale/{id}', [SaleController::class, 'update']);
    Route::delete('/delete/sale/{id}', [SaleController::class, 'delete']);

    Route::post('/create/social/media', [SocialMediaController::class, 'create']);
    Route::get('/social/medias', [SocialMediaController::class, 'getAll']);
    Route::get('/social/media/{id}', [SocialMediaController::class, 'get']);
    Route::get('/social/media/user/uuid/{uuid}', [SocialMediaController::class, 'getByUserUuid']);
    Route::put('/update/social/media/{id}', [SocialMediaController::class, 'update']);
    Route::delete('/delete/social/media/{id}', [SocialMediaController::class, 'delete']);

    //Route::post('/create/ticket', [TicketController::class, 'create']);
    //Route::get('/tickets', [TicketController::class, 'getAll']);
    //Route::get('/ticket/{id}', [TicketController::class, 'get']);
    Route::get('/tickets/campaign/{id}/ticket/{number}', [TicketController::class, 'getByCampaignIdAndTicketNumber']);
    Route::get('/tickets/campaign/{id}', [TicketController::class, 'getByCampaignId']);
    //Route::get('/tickets/collaborator/{id}', [TicketController::class, 'getByCollaboratorId']);
    Route::get('/collaborator/paid/tickets/{id}', [TicketController::class, 'getByCollaboratorIdPaid']);
    //Route::put('/update/ticket/{id}', [TicketController::class, 'update']);
    //Route::delete('/delete/ticket/{id}', [TicketController::class, 'delete']);

    Route::post('/create/role', [RoleController::class, 'create']);
    Route::get('/roles', [RoleController::class, 'getAll']);
    Route::get('/role/{id}', [RoleController::class, 'get']);
    Route::put('/update/role/{id}', [RoleController::class, 'update']);
    Route::delete('/delete/role/{id}', [RoleController::class, 'delete']);

    Route::get('/users/payments/statistics', [StatisticsController::class, 'getUsersPaymentsStatistics']);
});
Route::any('/forbidden', function () {
    return response()->json(['message' => __('Invalid Token')], 401);
});

/*Route::middleware('auth:sanctum')->group(function () {

    //    Route::group(['middleware' => ['permission:users|super-admin']], function () {
    Route::get('/users', [UserController::class, 'getAll']);
    Route::get('/users/{id}', [UserController::class, 'get']);
    Route::post('/users', [UserController::class, 'create']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'delete']);
//    });
});*/
