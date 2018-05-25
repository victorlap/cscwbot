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

class StartDiscussionController extends Controller
{
    /** @var BotMan */
    protected $bot;

    /** @var UserInterface */
    protected $user;

    /** @var string */
    protected $name;

    /** @var \stdClass */
    protected $channel;

    /**
     * @param Botman $bot
     * @param string $name
     */
    public function __invoke($bot, $name)
    {
        $this->bot = $bot;
        $this->name = $name;
        $this->user = $bot->getUser();

        $bot->reply("Got it. Give me a few seconds to get that done...");

        $this->createSlackChannel();
        $this->createDiscussion();

        $bot->reply(
            sprintf(
                "%s started a new discussion, help solve the issue in %s",
                $this->user->getUsername(),
                $this->channel->name
            )
        );
    }

    public function createDiscussion()
    {
        Discussion::create([
            'name' => $this->name,
            'originating_channel' => $this->bot->getMessage()->getRecipient(),
            'discussion_channel' => $this->channel->id
        ]);
    }

    public function createSlackChannel()
    {
        try {
            $channelName = str_limit('_discussion_' . str_slug($this->name, '_'), 20);

            /** @var ResponseInterface $response */
            $response = app(Slack::class)->createChannel($channelName);
        } catch (RequestException $exception) {
            $this->respondError();
            Log::error($exception->getMessage());
            return;
        }

        $response = json_decode((string)$response->getBody());
        $this->channel = $response->channel;

        try {
            $this->bot->say(
                sprintf(
                    "%s started a new discussion, help solve the issue in %s",
                    $this->user->getUsername(),
                    $this->channel->name
                ),
                $response->channel->id
            );
        } catch (BotManException $exception) {
            $this->respondError();
            Log::error($exception->getMessage());
        }
    }

    public function respondError()
    {
        $this->bot->reply("Something went wrong while trying to create your channel, sorry!");
    }
}
