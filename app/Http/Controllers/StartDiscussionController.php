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

        // Let the other commands resolve this one
        if ($name == 'help' || starts_with($name, 'end ')) {
            return;
        }

        if (!$this->createSlackChannel()) {
            $this->respondError();
            return;
        }

        $this->createDiscussion();
        $this->giveInfoInNewChannel();
        $this->giveInfoInOriginatingChannel();
    }

    public function createDiscussion()
    {
        Discussion::create([
            'name' => $this->name,
            'originating_channel' => $this->botman->getMessage()->getRecipient(),
            'discussion_channel' => $this->channel->id,
            'author' => $this->user->getUsername()
        ]);
    }

    public function createSlackChannel()
    {
        try {
            $channelName = str_limit('_discussion_' . Discussion::count(), 20);

            /** @var ResponseInterface $response */
            $response = app(Slack::class)->createChannel($channelName);

            $response = json_decode((string)$response->getBody());

            if (!$response->ok) {
                return false;
            }

            $this->channel = $response->channel;

            $this->bot = $this->botman->sendRequest('auth.test');

            $this->bot = json_decode($this->bot->getContent());
            if (!$this->bot->ok) {
                return false;
            }

            app(Slack::class)->joinChannel(
                $this->channel->id,
                $this->bot->user_id
            );
            app(Slack::class)->joinChannel(
                $this->channel->id,
                $this->user->getId()
            );

        } catch (RequestException | BotManException $exception) {
            Log::error($exception->getMessage());
            return false;
        }

        return true;
    }

    public function respondError()
    {
        $this->botman->reply("Something went wrong while trying to create your channel, sorry!");
    }

    public function giveInfoInOriginatingChannel(): void
    {
        try {
            $this->botman->say(
                sprintf(
                    "<@%s> started a new discussion about \"%s\", help solve the issue in <#%s|%s>!",
                    $this->user->getId(),
                    $this->name,
                    $this->channel->id,
                    $this->channel->name
                ),
                $this->botman->getMessage()->getRecipient()
            );
        } catch (BotManException $e) {
        }
    }

    public function giveInfoInNewChannel(): void
    {
        try {
            $this->botman->say(
                sprintf(
                    "<@%s> started a new discussion about \"%s\", help solve the issue!",
                    $this->user->getId(),
                    $this->name
                ),
                $this->channel->id
            );
            $this->botman->say(
                "
use `/argument list` to see a list of arguments
use `/viewpoint list` to see a list of viewpoints

Discussions happen in three rounds.

Debating round:
use `/viewpoint {viewpoint}` to add viewpoints 
use `/argument {argument}` to add arguments

Rating round: 
use `/argument rate` to rate all arguments

Voting round: 
use `/vote {viewpoint}` to vote for a specific viewpoint 

When everyone is done voting, you can close the channel using `/discussion end {viewpoint}`, you must provide a winning viewpoint which gets communicated to the originating channel.
            ",
                $this->channel->id
            );

            $this->botman->say(
                "The debating round has begun! discuss away!",
                $this->channel->id
            );

        } catch (BotManException $exception) {
            Log::error($exception->getMessage());
        }
    }
}
