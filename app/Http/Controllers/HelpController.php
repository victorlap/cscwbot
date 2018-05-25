<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function __invoke()
    {
        /** @var BotMan $bot */
        $bot = app('botman');

        $bot->reply("
        Here is a quick guide of what I can do: \n
        • use `/discussion {name}` in a channel to create a new discussion \n
        • use `/discussion add {argument}` to add arguments when in a discussion \n
        • use `/discussion help` to display this help text
        ");
    }
}
