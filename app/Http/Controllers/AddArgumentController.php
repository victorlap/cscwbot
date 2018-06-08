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
    protected $first_attempt = true;

    public function askViewpoint()
    {
        $this->ask('Hello! What is the ID of the viewpoint for your argument?', function(Answer $answer) {
            if ($this->first_attempt) {
                $this->say(ListViewpointsController::listViewpoints($this->channel));
            }
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

        if (in_array($this->viewpoint, $viewpoints_array)) {

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

    public function __construct($channel, $argument, $author) {
        $this->channel = $channel;
        $this->argument = $argument;
        $this->author = $author;
    }

    public function run()
    {
        $this->askViewpoint();
    }
}
