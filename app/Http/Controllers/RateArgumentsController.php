<?php

namespace App\Http\Controllers;

use App\Argument;
use App\Discussion;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Illuminate\Support\Facades\Log;

class RateArgumentsController extends Controller
{

    protected $botman;

    protected $bot;

    protected $user;

    protected $viewpoint;

    protected $arguments = [];

    protected $channel;

    /**
     * @param BotMan $bot
     */
    public function __invoke($bot)
    {
        $this->botman = $bot;
        $this->user = $bot->getUser();
        $this->channel = $this->botman->getMessage()->getRecipient();

        $discussion = Discussion::where('discussion_channel', $this->channel)->first();
        $viewpoints = $discussion->viewpoints;
        foreach ($viewpoints as $viewpoint) {
            array_push($this->arguments, Argument::where('viewpoint_id', $viewpoint->id)->get());
        }

        $this->botman->startConversation(new RateArgumentsConversation($this->channel, $this->arguments));
    }
}

class RateArgumentsConversation extends Conversation
{
    protected $channel;
    protected $arguments;
    protected $active_argument = 0;
    protected $author;

    public function introduceRating()
    {
        $discussion = Discussion::where('discussion_channel', $this->channel)->first();
        if ($discussion->state !== 'rate_arguments') {
            $this->say('You need to be in round 2 to rate arguments.');
            return true;
        } else {
            $this->ask('Do you want to start rating the arguments? Type `start` to start voting and `stop` if you want to cancel.', function(Answer $answer) {
                if ($answer->getText() == 'start') {
//                    $this->say('You will now get all arguments for each viewpoint and you can score them... The options are [-1, 0, 1, 2].');
                    $this->rateArguments();
                }
            });
        }
    }

    public function rateArguments()
    {
        if ($this->active_argument >= count($this->arguments)) {
            $this->concludeRating();
        }

        $argument = $this->arguments->get($this->active_argument);
        $this->ask('Argument ' . ($this->active_argument + 1) . ': *' . $argument->argument . '*', function(Answer $answer) {
            if ($answer->getText() == '-1' || '0' || '1' || '2') {
                $this->active_argument += 1;
            } else {
                $this->rateArguments();
            }
        });
    }

    public function concludeRating() {
        $this->say('Thank you for rating the arguments. When moving to the voting round, you will get an overview of all arguments.');
        return true;
    }

    public function stopsConversation(IncomingMessage $message)
    {
        if ($message->getText() == 'stop') {
            return true;
        }

        return false;
    }

    public function __construct($channel, $arguments) {
        $this->channel = $channel;
        $this->arguments = $arguments;
    }

    public function run()
    {
        $this->introduceRating();
    }
}
