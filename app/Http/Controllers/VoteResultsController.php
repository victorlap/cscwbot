<?php

namespace App\Http\Controllers;

use App\Discussion;
use App\Vote;

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
            return 'You need to be in the voting round to see the voting results.';
        } else {
            $viewpoints = $discussion->viewpoints;
            $viewpoints_string = '';
            foreach ($viewpoints as $viewpoint) {
                $votes = Vote::where('viewpoint_id', $viewpoint->id)->exists();
                $votes = ($votes ? $votes : 0);
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