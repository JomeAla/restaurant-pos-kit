<?php

namespace App\Providers;

use App\Rules\SafeEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

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
        Validator::extend('safe_email', function ($attribute, $value, $parameters, $validator) {
            $value = str_replace(["\r", "\n"], '', $value);
            return filter_var($value, FILTER_VALIDATE_EMAIL);
        }, 'The :attribute must be a valid email address.');
    }
}
