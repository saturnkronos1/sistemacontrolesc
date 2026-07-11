<?php

namespace App\Providers;

use App\Events\CicloActivado;
use App\Listeners\ClonarGrupos;
use App\Listeners\ClonarPeriodosEvaluacion;
use App\Listeners\PromoverAlumnos;
use App\Models\CicloEscolar;
use App\Policies\CicloEscolarPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerEvents();
        $this->registerPolicies();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Register domain events.
     */
    protected function registerEvents(): void
    {
        Event::listen(
            CicloActivado::class,
            ClonarPeriodosEvaluacion::class,
        );

        Event::listen(
            CicloActivado::class,
            ClonarGrupos::class,
        );

        Event::listen(
            CicloActivado::class,
            PromoverAlumnos::class,
        );
    }

    /**
     * Register model policies.
     */
    protected function registerPolicies(): void
    {
        Gate::policy(CicloEscolar::class, CicloEscolarPolicy::class);
    }
}
