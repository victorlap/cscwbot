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
• use `/argument rate` to rate the arguments
• use `/argument list` to see all arguments
• use `/argument {argument}` to add arguments 
• use `/discussion {name}` to create a new discussion
• use `/discussion end {viewpoint}` to end a discussion
• use `/discussion help` to display this help text
• use `/goto {round}` to move to a different round
• use `/viewpoint list` to list all viewpoints
• use `/viewpoint {name}` to add a viewpoint
• use `/vote result` to see the results of the vote
• use `/vote {viewpoint}` to vote for a specific viewpoint
        ");
    }
}
