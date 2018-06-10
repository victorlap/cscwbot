<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteResultsController extends Controller
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

        $bot->reply($this->listVoteResults($this->botman->getMessage()->getRecipient()));
    }

    public static function listVoteResults($channel)
    {

        $discussion = Discussion::where('discussion_channel', $channel)->first();
        if ($discussion->state !== 'voting') {
            return 'You need to be in round 3 to see the voting results.';
        } else {
            $viewpoints = $discussion->viewpoints;
            $viewpoints_string = '';
            foreach ($viewpoints as $viewpoint) {
                $votes = DB::table('votes')->where('viewpoint_id', $viewpoint->id)->exists();
                $viewpoints_string .= sprintf(
                    "\nID: *%s* - *%s* with *%s* vote(s).",
                    $viewpoint->id,
                    $viewpoint->viewpoint,
                    $votes
                );
            }

            return (
            sprintf(
                "The viewpoints and their votes are: %s",
                $viewpoints_string
            )
            );
        }
    }
}