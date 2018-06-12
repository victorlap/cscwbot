<?php

namespace App\Http\Controllers;

use App\Clients\Slack;
use App\Discussion;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Interfaces\UserInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class EndDiscussionController extends Controller
{
    /** @var Botman */
    protected $botman;
    protected $viewpoint;

    /** @var UserInterface */
    protected $user;

    /** @var Discussion */
    protected $discussion;

    /**
     * @param BotMan $bot
     * @param string $name
     */
    public function __invoke($bot, $viewpoint)
    {
        $this->botman = $bot;
        $this->viewpoint = $viewpoint;
        $this->user = $bot->getUser();
        $this->discussion = Discussion::where('discussion_channel', $this->botman->getMessage()->getRecipient())->first();
        Log::info('viewpoint', [$viewpoint, $this->discussion->viewpoints()->pluck('id')->toArray()]);

        if(! $this->discussion->viewpoints()->pluck('id')->has($viewpoint)) {
            $this->botman->reply("Invalid viewpoint id, try listing viewpoints with /viewpoint list");
            return;
        }

        $this->discussion->close($viewpoint);

        $this->sendConclusionToChannel($this->discussion->discussion_channel);
        $this->sendConclusionToChannel($this->discussion->originating_channel);
        $this->closeChannel();
    }

    protected function sendConclusionToChannel($channel)
    {
        try {
            $this->botman->say(
                sprintf(
                    "<@%s> ended the discussion about \"%s\" with the following conclusion \"%s\".",
                    $this->user->getId(),
                    $this->discussion->name,
                    $this->viewpoint
                ),
                $channel
            );
        } catch (BotManException $exception) {
        }
    }

    protected function closeChannel()
    {
        try {
            /** @var ResponseInterface $response */
            app(Slack::class)->archiveChannel($this->discussion->discussion_channel);
        } catch (RequestException $exception) {
        }
    }
}
