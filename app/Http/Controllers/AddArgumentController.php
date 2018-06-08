<?php

namespace App\Http\Controllers;

use App\Argument;
use App\Discussion;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Messages\Attachments\Attachment;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Slack\Extensions\Menu;
use Illuminate\Support\Facades\Log;
use Slack\Message\Message;

class AddArgumentController extends Controller
{

    protected $botman;

    protected $bot;

    protected $user;

    protected $viewpoint;

    protected $argument;

    protected $channel;

    /**
     * @param BotMan $bot
     * @param string $argument
     */
    public function __invoke($bot, $argument)
    {
        $this->botman = $bot;
        $this->argument = $argument;
        $this->user = $bot->getUser();

        $this->botman->startConversation(new AskViewpointConversation($this->botman->getMessage()->getRecipient(), $argument, $bot->getUser()));
    }
}

class AskViewpointConversation extends Conversation
{
    protected $channel;
    protected $argument;
    protected $viewpoint;
    protected $author;

    public function showViewpoints()
    {
        $this->say(ListViewpointsController::listViewpoints($this->channel));
    }

    public function askViewpoint()
    {
        $this->ask('Hello! What is the *ID* of the viewpoint for your argument?', function(Answer $answer) {
            $this->viewpoint = $answer->getText();
            $this->say('OK! I\'ll add it to the list');
        });
    }

    public function addArgument()
    {
        Argument::create([
            'argument' => $this->argument,
            'viewpoint_id' => $this->viewpoint,
            'author' => $this->author->getUsername()
        ]);

        try {
            $this->say(
                sprintf(
                    "<@%s> added an argument: \"%s\" for viewpoint %s.",
                    $this->author->getUsername(),
                    $this->argument,
                    $this->viewpoint
                ),
                $this->channel
            );
        } catch (BotManException $exception) {
            Log::error($exception->getMessage());
        }
    }


    public function __construct($channel, $argument, $author) {
        $this->channel = $channel;
        $this->argument = $argument;
        $this->author = $author;
    }

    public function run()
    {
        $this->showViewpoints();
        $this->askViewpoint();
        $this->addArgument();
    }
}
