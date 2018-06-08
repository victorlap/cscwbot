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

        $this->botman->reply($this->listViewpoints($this->botman->getMessage()->getRecipient()));

    }

    public static function listViewpoints($channel)
    {

        $discussion = Discussion::where('discussion_channel', $channel)->first();
        $viewpoints = $discussion->viewpoints;

        Log::debug('Number of viewpoints =  ' . $viewpoints->count());

        $viewpoints_string = '';
        foreach ($viewpoints as $viewpoint) {
            $viewpoints_string .= sprintf(
                "ID: *%s* - *%s* by <@%s>",
                $viewpoint->id,
                $viewpoint->viewpoint,
                $viewpoint->author
            );
        }

        return (
            sprintf(
                "There are %s viewpoint(s) for this discussion. The viewpoints are: %s",
                $viewpoints->count(),
                $viewpoints_string
            )
        );

    }
}
