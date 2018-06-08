<?php

namespace App\Http\Controllers;

use App\Argument;
use App\Discussion;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class RateArgumentsController extends Controller
{

    protected $botman;

    protected $bot;

    protected $user;

    protected $viewpoint;

    protected $argument;

    protected $channel;

    /**
     * @param BotMan $bot
     */
    public function __invoke($bot)
    {
        $this->botman = $bot;
        $this->argument = $argument;
        $this->user = $bot->getUser();

        $this->botman->startConversation(new RateArgumentsConversation($this->botman->getMessage()->getRecipient(), $argument, $bot->getUser()));
    }
}

class RateArgumentsConversation extends Conversation
{
    protected $channel;
    protected $arguments;
    protected $active_argument = 0;
    protected $author;

    public function rateArgument($argument)
    {

        $discussion = Discussion::where('discussion_channel', $this->channel)->first();
        if ($discussion->state !== 'rate_arguments') {
            $this->say('You need to be in round 2 to rate arguments.');
            return true;
        }

        $this->ask('What is the ID of the viewpoint for your argument? Type `stop` if you want to cancel. ' . $list, function(Answer $answer) {
            $this->viewpoint = $answer->getText();
            $this->addArgument();
        });
    }

    public function addArgument()
    {

        // Request possible IDs
        $discussion = Discussion::where('discussion_channel', $this->channel)->first();
        $viewpoints = $discussion->viewpoints;
        $viewpoints_array = [];

        foreach ($viewpoints as $viewpoint) {
            array_push($viewpoints_array, $viewpoint->id);
        }

        if (in_array($this->viewpoint, $viewpoints_array) && $discussion->state === 'rate_arguments') {

            Argument::create([
                'argument' => $this->argument,
                'viewpoint_id' => $this->viewpoint,
                'author' => $this->author->getUsername()
            ]);

            $this->say(
                sprintf(
                    "<@%s> added an argument: \"%s\" for viewpoint %s.",
                    $this->author->getUsername(),
                    $this->argument,
                    $this->viewpoint
                )
            );
            return true;
        } else {
            $this->first_attempt = false;
            $this->say("Invalid ID, try again.");
            $this->askViewpoint();
        }
    }

    public function stopsConversation(IncomingMessage $message)
    {
        if ($message->getText() == 'stop') {
            return true;
        }

        return false;
    }

    public function __construct($arguments) {
        $this->arguments = $arguments;
    }

    public function run()
    {
        $this->rateArguments();
    }
}
