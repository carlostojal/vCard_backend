<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use App\Models\Passport\AuthCode;
use App\Models\Passport\Client;
use App\Models\Passport\PersonalAccessClient;
use App\Models\Passport\RefreshToken;
use App\Models\Passport\Token;
use App\Models\Vcard;
use App\Policies\VcardPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Vcard::class => VcardPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        // Passport::routes();
        //
        // Passport::loadKeysFrom('./secrets/oauth');
        // // Passport::hashClientSecrets();
        // //
        // // Passport::tokensExpireIn(now()->addDays(15));
        // // Passport::refreshTokensExpireIn(now()->addDays(30));
        // // Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        // Passport::useTokenModel(Token::class);
        // Passport::useRefreshTokenModel(RefreshToken::class);
        // Passport::useAuthCodeModel(AuthCode::class);
        // // Passport::useClientModel(User::class);
        // Passport::usePersonalAccessClientModel(PersonalAccessClient::class);
    }

}
