<?php

namespace App\Providers;

use App\Clients\Slack;
use App\Http\Middleware\LogMiddleware;
use BotMan\BotMan\BotMan;
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
                'base_uri' => 'https://slack.com/api/',
                'verify' => false
            ]);
            return new Slack($client, $app['config']['botman']['slack']);
        });

        $logEverything = new LogMiddleware();
        /** @var BotMan $botman */
        $botman = resolve('botman');
        $botman->middleware->received($logEverything);
        $botman->middleware->sending($logEverything);
    }
}
