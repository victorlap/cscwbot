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

        $this->listViewpoints();

    }

    public function listViewpoints()
    {

        $discussion = Discussion::where('discussion_channel', $this->botman->getMessage()->getRecipient())->first();
        $viewpoints = $discussion->viewpoints;

        Log::debug('Number of viewpoints =  ' . $viewpoints->count());

        $viewpoints_string = '';
        foreach ($viewpoints as $viewpoint) {
            $viewpoints_string .= sprintf(
                "\n*%s* by <@%s>",
                $viewpoint->viewpoint,
                $viewpoint->author
            );
        }

        $this->botman->reply(
            sprintf(
                "There are %s viewpoint(s) for this discussion. The viewpoints are: %s",
                $viewpoints->count(),
                $viewpoints_string
            )
        );

    }
}
