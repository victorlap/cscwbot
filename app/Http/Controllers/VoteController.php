<?php

namespace App\Http\Controllers;

use App\Discussion;
use App\Viewpoint;
use App\Vote;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use GuzzleHttp\Exception\RequestException;

class VoteController extends Controller
{
    protected $botman;

    protected $bot;

    protected $user;

    protected $channel;

    /**
     * @param BotMan $bot
     * @param string $viewpoint
     */
    public function __invoke($bot, $viewpoint)
    {
        $this->botman = $bot;
        $this->user = $bot->getUser();
        $this->channel = $this->botman->getMessage()->getRecipient();

        // Let the other commands resolve this one
        if ($viewpoint == 'result') {
            return;
        }

        try {
            $discussion = Discussion::where('discussion_channel', $bot->getMessage()->getRecipient())->first();

            $viewpoint = Viewpoint::findByNameOrId($viewpoint, $discussion->id);

            if (!$viewpoint) {
                $this->botman->reply("Invalid viewpoint, try listing viewpoints with /viewpoint list");
                return;
            }

            if ($discussion->state !== 'voting') {
                $bot->reply('You need to be in the voting round to vote.');
                return;
            }

            Vote::create([
                'discussion_id' => $discussion->id,
                'viewpoint_id' => $viewpoint->id,
                'author' => $this->user->getUsername()
            ]);

            $bot->reply("Thank you for your vote! Use `/vote result` to see the current standings.");

            try {
                $bot->say(
                    sprintf('<@%s> just finished voting', $this->user->getUsername() ),
                    $discussion->discussion_channel
                );
            } catch (BotManException $exception) {
            }

        } catch (RequestException | \Exception $exception) {
            $bot->reply("You have already voted for this discussion.");
            return;
        }

        return;
    }
}
