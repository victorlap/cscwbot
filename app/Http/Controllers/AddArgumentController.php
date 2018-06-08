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

    protected $email;

    /**
     * @param BotMan $bot
     * @param string $argument
     */
    public function __invoke($bot, $argument)
    {
        $this->botman = $bot;
        $this->argument = $argument;
        $this->user = $bot->getUser();

        $result = $this->botman->startConversation(new AddArgumentConversation);
        $this->addArgument($result, $argument);
    }

    public function addArgument($viewpoint, $argument)
    {
        Argument::create([
            'argument' => $argument,
            'viewpoint_id' => $viewpoint,
            'author' => $this->user->getUsername()
        ]);

        try {
            $this->say(
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

class AddArgumentConversation extends Conversation
{
    protected $viewpoint;

    public function showViewpoints()
    {

        $discussion = Discussion::where('discussion_channel', $this->getMessage()->getRecipient())->first();
        $viewpoints = $discussion->viewpoints;
        $viewpoints_string = '';
        foreach ($viewpoints as $viewpoint) {
            $viewpoints_string .= sprintf(
                "ID: *%s* - *%s* by <@%s>",
                $viewpoint->id,
                $viewpoint->viewpoint,
                $viewpoint->author
            );
        }

        $this->say(
            sprintf(
                "The viewpoints (%s) are: %s.",
                $viewpoints->count(),
                $viewpoints_string
            )
        );
    }

    public function askViewpoint()
    {
        $this->ask('Hello! What is the *ID* of the viewpoint for your argument?', function(Answer $answer) {
            // Save result
            return $answer->getText();
        });
    }

    public function run()
    {
        // This will be called immediately
        $this->showViewpoints();
        $this->askViewpoint();
    }
}