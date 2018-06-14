<?php

namespace App\Http\Controllers;

use App\Argument;
use App\Discussion;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Slack\SlackDriver;

class RateArgumentsController extends Controller
{
    /**
     * @param BotMan $bot
     */
    public function __invoke($bot)
    {

        $channel = $bot->getMessage()->getRecipient();

        $bot->startConversation(new RateArgumentsConversation($channel), $bot->getUser()->getId(), SlackDriver::class);
    }
}

class RateArgumentsConversation extends Conversation
{
    protected $channel;
    protected $arguments;
    protected $argument;
    protected $active_argument = 0;
    protected $author;

    public function introduceRating()
    {
        $discussion = Discussion::where('discussion_channel', $this->channel)->first();
        if ($discussion->state !== 'rate_arguments') {
            $this->say('You need to be in the rating round to rate arguments.');
            return true;
        } else {
            $question = Question::create('Do you want to start rating the arguments?')
                ->callbackId('create_database')
                ->addButtons([
                    Button::create('Yes please!')->value('start'),
                    Button::create('No')->value('stop'),
                ]);
            $this->ask($question, function (Answer $answer) {
                if ($answer->getValue() == 'start') {
                    $this->say('You can sore each argument from one of: [-1, 0, 1, 2].');
                    $this->rateArguments();
                }
            });
        }
    }

    public function rateArguments()
    {
        if ($this->active_argument >= count($this->arguments)) {
            $this->concludeRating();
            return true;
        }

        $this->argument = $this->arguments[$this->active_argument];
        $this->ask('Viewpoint: "' . $this->argument->viewpoint->viewpoint . '" - Argument ' . ($this->active_argument + 1) . ': *' . $this->argument->argument . '*', function (Answer $answer) {
            if ($answer->getText() === '-1' || $answer->getText() === '0' || $answer->getText() === '1' || $answer->getText() === '2') {
                $this->active_argument += 1;

                Argument::where('id', $this->argument->id)
                    ->increment('priority', intval($answer->getText()));

                $this->rateArguments();
            } else {
                $this->say('You can only rate using `-1`, `0`, `1` and `2`.');
                $this->rateArguments();
            }
        });

    }

    public function concludeRating()
    {
        $this->say(
            'Thank you for rating the arguments. When moving to the voting round, you will get an overview of all arguments.'
        );
    }

    public function stopsConversation(IncomingMessage $message)
    {
        if ($message->getText() == 'stop') {
            return true;
        }

        return false;
    }

    public function __construct($channel)
    {
        $this->channel = $channel;
        $discussion = Discussion::where('discussion_channel', $this->channel)->first();

        $this->arguments = Argument::whereIn('viewpoint_id', $discussion->viewpoints()->pluck('id'))->get();
    }

    public function run()
    {
        $this->introduceRating();
    }
}
