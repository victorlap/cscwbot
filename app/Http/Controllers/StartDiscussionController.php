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
    protected $botman;

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
        $this->botman = $bot;
        $this->name = $name;
        $this->user = $bot->getUser();

        $bot->reply("Got it. Give me a few seconds to get that done...");

        if(!$this->createSlackChannel()) {
            $this->respondError();
            return;
        }

        $this->createDiscussion();

        try {
            $this->botman->say(
                sprintf(
                    "%s started a new discussion, help solve the issue in %s",
                    $this->user->getUsername(),
                    $this->channel->name
                ),
                $this->botman->getMessage()->getRecipient()
            );
        } catch (BotManException $e) {
        }
    }

    public function createDiscussion()
    {
        Discussion::create([
            'name' => $this->name,
            'originating_channel' => $this->botman->getMessage()->getRecipient(),
            'discussion_channel' => $this->channel->id
        ]);
    }

    public function createSlackChannel()
    {
        try {
            $channelName = str_limit('_discussion_' . Discussion::count(), 20);

            /** @var ResponseInterface $response */
            $response = app(Slack::class)->createChannel($channelName);

            $response = json_decode((string)$response->getBody());

            if(!$response->ok) {
                return false;
            }

            $this->channel = $response->channel;

            $this->bot = $this->botman->sendRequest('auth.test');
            $this->bot = json_decode($this->bot->getContent());
            if(!$this->bot->ok) {
                return false;
            }

            app(Slack::class)->joinChannel(
                $this->channel->id,
                $this->bot->user_id
            );

        } catch (RequestException | BotManException $exception) {
            Log::error($exception->getMessage());
            return false;
        }

        try {
            $this->botman->say(
                sprintf(
                    "%s started a new discussion, help solve the issue in %s",
                    $this->user->getUsername(),
                    $this->channel->name
                ),
                $this->channel->id
            );

        } catch (BotManException $exception) {
            Log::error($exception->getMessage());
            return false;
        }

        return true;
    }

    public function respondError()
    {
        $this->botman->reply("Something went wrong while trying to create your channel, sorry!");
    }
}
