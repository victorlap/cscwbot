<?php

namespace App\Http\Controllers;

use App\Conversations\ExampleConversation;

class BotManController extends Controller
{

    protected $botman;

    public function __construct()
    {
        $this->botman = app('botman');
    }

    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        $this->botman->listen();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('tinker');
    }
}
