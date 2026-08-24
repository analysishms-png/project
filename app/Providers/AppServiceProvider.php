<?php

namespace App\Providers;

use App\Models\Companyreg as Company;
use App\Services\ResilientCacheManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Redis-resilient cache: every Cache::store('redis') resolve falls
        // back to the file store while the Redis server is unreachable.
        $this->app->extend('cache', function ($manager, $app) {
            return new ResilientCacheManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Use Bootstrap 5 pagination styling (project already uses Bootstrap).
        Paginator::useBootstrapFive();

        View::composer('property.layouts.header', function ($view) {
            // $user = Auth::user();
            // if ($user) {
            //     $mail = $user->email;
            //     $u_name = $user->u_name;
            //     $propertyid = $user->propertyid;
            //     $company = DB::table('company')->where('email', $mail)->where('propertyid', $propertyid)->where('u_name', $u_name)->first();
            //     if ($company) {
            //         $view->with('user', $company);
            //     }
            // }

            $companies = \App\Helpers\MasterDataCache::headerCompanies(Auth::user()->propertyid);
            $view->with('companies', $companies);
        });
    }
}
