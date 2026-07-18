<?php

namespace App\Providers;

use App\Finance\Integrations\ProjectFinanceIntegration;
use App\Finance\Listeners\ProjectRegistrationPaymentToFinance;
use App\Payments\Events\RegistrationPaymentRecorded;
use App\Projects\Contracts\ProjectFinanceGateway;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProjectFinanceGateway::class, ProjectFinanceIntegration::class);
    }

    public function boot(): void
    {
        Event::listen(RegistrationPaymentRecorded::class, ProjectRegistrationPaymentToFinance::class);

        $this->loadRoutesFrom(base_path('routes/modules/finance-web.php'));
    }
}
