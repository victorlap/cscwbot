<?php

namespace App\Providers;

use App\Clients\Slack;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['config'];
        $this->app->singleton(Slack::class, function ($app) {
            $client = new Client([
                'base_url' => 'https://slack.com/api',
            ]);
            return new Slack($client, $app['config']['botman']['slack']);
        });
    }
}
