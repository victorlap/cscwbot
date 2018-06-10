<?php

namespace App\Http\Controllers;

use App\Discussion;
use App\Vote;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use GuzzleHttp\Exception\RequestException;

class VoteController extends Controller
{
    protected $botman;

    protected $bot;

    protected $user;

    protected $viewpoint;

    protected $channel;

    /**
     * @param BotMan $bot
     * @param string $viewpoint
     */
    public function __invoke($bot, $viewpoint)
    {
        $this->botman = $bot;
        $this->user = $bot->getUser();
        $this->viewpoint = $viewpoint;
        $this->channel = $this->botman->getMessage()->getRecipient();

        // Let the other commands resolve this one
        if ($viewpoint == 'result') {
            return;
        }

        try {
            $discussion = Discussion::where('discussion_channel', $bot->getMessage()->getRecipient())->first();

            Vote::create([
                'discussion_id' => $discussion->id,
                'viewpoint_id' => $this->viewpoint,
                'author' => $this->user->getUsername()
            ]);

            $bot->reply("Thank you for your vote! Use `/vote result` to see the current standings.");

        } catch (RequestException | BotManException $exception) {
            $bot->reply("You have already voted for this discussion.");
            return false;
        }

        return true;
    }
}
