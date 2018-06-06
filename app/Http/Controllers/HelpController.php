<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;

class HelpController extends Controller
{
    public function __invoke()
    {
        /** @var BotMan $bot */
        $bot = app('botman');

        $bot->reply("
Here is a quick guide of what I can do:
• use `/viewpoint list` to list all viewpoints in a discussion
• use `/viewpoint {name}` to add a viewpoint when in a discussion
• use `/argument {argument}` to add arguments when in a discussion
• use `/discussion {name}` in a channel to create a new discussion
• use `/discussion help` to display this help text
        ");
    }
}
