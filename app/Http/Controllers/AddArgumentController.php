<?php

namespace App\Http\Controllers;

use App\Argument;
use App\Discussion;
use App\Viewpoint;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class AddArgumentController extends Controller
{
    /**
     * @param BotMan $bot
     * @param string $argument
     */
    public function __invoke($bot, $argument)
    {
        if ($argument == 'list' || $argument == 'rate') {
            return;
        }

        $bot->startConversation(new AskViewpointConversation($bot->getMessage()->getRecipient(), $argument, $bot->getUser()));
    }
}

class AskViewpointConversation extends Conversation
{
    protected $channel;
    protected $argument;
    protected $viewpoint;
    protected $author;
    protected $first_attempt = true;

    public function askViewpoint()
    {

        $discussion = Discussion::where('discussion_channel', $this->channel)->first();
        if ($discussion->state !== 'add_arguments') {
            $this->say('You need to be in the debating round to add arguments. Use `/round help` to see possible commands for this round.');
            return true;
        }

        $buttons = [];
        foreach ($discussion->viewpoints as $viewpoint) {
            $buttons[] = Button::create($viewpoint->viewpoint)->value($viewpoint->id);
        }
        $question = Question::create('What is the viewpoint for your argument?')
            ->callbackId('choose_viewpoint')
            ->addButtons($buttons);
        $question = Question::create('Do you want to start rating the arguments?')
            ->callbackId('create_database')
            ->addButtons([
                Button::create('Yes please!')->value('start'),
                Button::create('No')->value('stop'),
            ]);
        $this->ask($question, function (Answer $answer) {
            $this->viewpoint = $answer->getValue();
            $this->addArgument();
        });
    }

    public function addArgument()
    {
        $discussion = Discussion::where('discussion_channel', $this->channel)->first();

        $viewpoint = Viewpoint::findByNameOrId($this->viewpoint, $discussion->id);

        if ($viewpoint) {

            Argument::create([
                'argument' => $this->argument,
                'viewpoint_id' => $viewpoint->id,
                'author' => $this->author->getUsername()
            ]);

            try {
                $this->getBot()->say(
                    sprintf(
                        "<@%s> added an argument: \"%s\" for viewpoint %s.",
                        $this->author->getUsername(),
                        $this->argument,
                        $viewpoint->viewpoint
                    ),
                    $discussion->discussion_channel
                );
            } catch (BotManException $exception) {
            }

            return true;
        } else {
            $this->first_attempt = false;
            $this->say("Invalid viewpoint, try again.");
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

    public function __construct($channel, $argument, $author)
    {
        $this->channel = $channel;
        $this->argument = $argument;
        $this->author = $author;
    }

    public function run()
    {
        $this->askViewpoint();
    }
}
