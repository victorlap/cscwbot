<?php

namespace App\Http\Controllers;

use App\Discussion;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Interfaces\UserInterface;
use Facades\App\Clients\Slack;
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
            /** @var ResponseInterface $response */
            $response = Slack::createChannel('_discussion');
        } catch (RequestException $exception) {
            Log::error($exception->getMessage());
            $this->respondError();
            return;
        }

        Log::info('json decoded', json_decode((string)$response->getBody(), true));

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
