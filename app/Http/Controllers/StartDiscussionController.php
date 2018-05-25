<?php

namespace App\Http\Controllers;

use App\Discussion;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Exceptions\Core\BadMethodCallException;
use BotMan\BotMan\Interfaces\UserInterface;
use Illuminate\Support\Facades\Log;

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
            $response = $this->bot->sendRequest('channels.create', [
                'name' => '_discussion'
            ]);
        } catch (BadMethodCallException $exception) {
            $this->respondError();
            return;
        }

        Log::info('got channel create response', $response->getContent());
        Log::info('json decoded', json_decode($response->getContent(), true));

        $response = json_decode($response->getContent());
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
        } catch (BotManException $e) {
            $this->respondError();
            return;
        }
    }

    public function respondError()
    {
        $this->bot->reply("Something went wrong while trying to create your channel, sorry!");
    }
}
