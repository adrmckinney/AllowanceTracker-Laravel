<?php

namespace App\Providers;

use App\Data\Entities\User\UserPolicy;
use App\Models\Chore;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserChore;
use App\Policies\ChorePolicy;
use App\Policies\PermissionPolicy;
use App\Policies\UserChorePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,
        Permission::class => PermissionPolicy::class,
        Chore::class => ChorePolicy::class,
        UserChore::class => UserChorePolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }

    // private function onAuthenticated(User $user)
    // {
    //     // inject AuthUser into all classes that extend AbstractUseCase
    //     $this->app->resolving(Controller::class, function ($useCase) use ($user) {
    //         $useCase->setAuthUser($user);
    //     });
    // }
}
