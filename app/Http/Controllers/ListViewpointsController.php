<?php

namespace App\Http\Controllers;

use App\Clients\Slack;
use App\Viewpoint;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Interfaces\UserInterface;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class ListViewpointsController extends Controller
{
    protected $botman;

    protected $bot;

    protected $user;

    protected $name;

    protected $channel;

    protected $discussion;

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

        $discussion = DB::table('discussions')->where('discussion_channel', $this->botman->getMessage()->getRecipient())->first();
        $viewpoints = DB::table('viewpoints')->select('viewpoint', 'author')->where('discussion_id', $discussion->id)->get();

        Log::debug('Number of viewpoints =  ' . count($viewpoints));

        $this->discussion = $discussion->id;

        $viewpoints_string = '';
        foreach ($viewpoints as $viewpoint) {
            $viewpoints_string .= sprintf(
                "\n*%s* by <@%s>",
                $viewpoint->viewpoint,
                $viewpoint->author
            );
        }

        $this->botman->say(
            sprintf(
                "There are %s viewpoint(s) for this discussion. The viewpoints are: \n %s",
                count($viewpoints),
                $viewpoints_string
            ),
            $this->botman->getMessage()->getRecipient()
        );

    }
}
