<?php

namespace App\Http\Controllers;

use App\Argument;
use App\Discussion;
use App\Vote;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $this->vote();
    }

    public function vote()
    {
        try {
            $discussion = Discussion::where('discussion_channel', $this->botman->getMessage()->getRecipient())->first();

            Vote::create([
                'discussion_id' => $discussion->id,
                'viewpoint_id' => $this->viewpoint,
                'author' => $this->user->getUsername()
            ]);

        } catch (RequestException | BotManException $exception) {
            Log::error($exception->getMessage());
            return false;
        }

        return true;
    }
}
