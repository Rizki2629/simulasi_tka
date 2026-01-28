<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
        Schema::defaultStringLength(191);

        // When running behind ngrok (or another HTTPS reverse proxy), Laravel may
        // see the request as HTTP and generate redirect URLs with http://. That
        // can cause confusing behavior in browsers (blank/loop). Force HTTPS.
        if (! $this->app->runningInConsole()) {
            $request = request();
            $host = (string) $request->getHost();
            $forwardedProto = (string) $request->header('x-forwarded-proto', '');

            if (Str::contains($forwardedProto, 'https') || Str::endsWith($host, 'ngrok-free.app')) {
                URL::forceScheme('https');
                URL::forceRootUrl('https://'.$host);
            }
        }
    }
}
