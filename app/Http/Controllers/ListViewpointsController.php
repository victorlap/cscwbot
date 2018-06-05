<?php

namespace App\Http\Controllers;

use App\Discussion;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class ListViewpointsController extends Controller
{
    protected $botman;

    protected $bot;

    protected $user;

    protected $name;

    protected $channel;

    /**
     * @param BotMan $bot
     */
    public function __invoke($bot)
    {
        $this->botman = $bot;
        $this->user = $bot->getUser();

        $bot->reply("Got it. Collecting the viewpoints now.");
        $this->listViewpoints();

    }

    public function listViewpoints()
    {

        $discussion = Discussion::where('discussion_channel', $this->botman->getMessage()->getRecipient())->first();
        $viewpoints = $discussion->viewpoints;

        Log::debug('Number of viewpoints =  ' . $viewpoints->count());

        $viewpoints_string = '';
        foreach ($viewpoints as $viewpoint) {
            $viewpoints_string .= '\n ' . $viewpoint->viewpoint . ' by ' . $viewpoint->author;
        }

        $this->botman->say(
            sprintf(
                "There are %s viewpoint(s) for this discussion. The viewpoints are: \n %s",
                $viewpoints->count(),
                $viewpoints_string
            ),
            $this->botman->getMessage()->getRecipient()
        );

    }
}
