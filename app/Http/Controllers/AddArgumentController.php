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

        $this->botman->reply(ListViewpointsController::listViewpoints($this->botman->getMessage()->getRecipient()));

        $conversation = new AskViewpointConversation($argument);
        $this->addArgument($conversation->getViepoint());
    }

    public function addArgument($viewpoint)
    {
        Argument::create([
            'argument' => $this->argument,
            'viewpoint_id' => $viewpoint,
            'author' => $this->user->getUsername()
        ]);

        try {
            $this->botman->say(
                sprintf(
                    "<@%s> added an argument: \"%s\" for viewpoint %s.",
                    $this->user->getUsername(),
                    $this->argument,
                    $viewpoint
                ),
                $this->botman->getMessage()->getRecipient()
            );
        } catch (BotManException $exception) {
            Log::error($exception->getMessage());
        }
    }
}

class AskViewpointConversation extends Conversation
{
    protected $argument;
    protected $viewpoint;

    public function askViewpoint()
    {
        $this->ask('Hello! What is the *ID* of the viewpoint for your argument?', function(Answer $answer) {
            $this->viewpoint = $answer->getText();
        });
    }

    public function getViepoint() {
        return $this->viewpoint;
    }

    public function __construct($argument) {
        $this->argument = $argument;
    }

    public function run()
    {
        $this->askViewpoint();
    }
}
