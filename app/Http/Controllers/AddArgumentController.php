<?php

namespace App\Http\Controllers;

use App\Argument;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Messages\Attachments\Attachment;
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

    protected $name;

    protected $channel;

    protected $email;

    /**
     * @param BotMan $bot
     * @param string $viewpoint
     * @param string $name
     */
    public function __invoke($bot, $viewpoint, $name)
    {
        $this->botman = $bot;
        $this->viewpoint = $viewpoint;
        $this->name = $name;
        $this->user = $bot->getUser();

//        $this->addArgument($viewpoint, $name);

//        $this->botman->ask('One more thing - what is your email?', function(Answer $answer) {
//            // Save result
//            $this->email = $answer->getText();
//
//            $this->say('Great - that is all we need: ' . $this->email);
//        });

        try {
            $this->botman->sendRequest('chat.PostMessage', [
                'text' => 'Want to play a game?',
            ]);
        } catch (BotManException $exception) {
            Log::error($exception->getMessage());
        }

//        $this->botman->say("Your choice: " . json_decode($response));
//
//        $this->botman->ask("Mooi werk?", function (Answer $answer) {
//            // Detect if button was clicked:
//            if ($answer->isInteractiveMessageReply()) {
//                $selectedValue = $answer->getValue(); // will be either 'yes' or 'no'
//                $selectedText = $answer->getText(); // will be either 'Of course' or 'Hell no!'
//
//                $this->say("Your choice: " . $selectedText);
//            }
//        });

    }

    public function addArgument($viewpoint, $name)
    {

        Argument::create([
            'argument' => $name,
            'viewpoint_id' => $viewpoint,
            'author' => $this->user->getUsername()
        ]);

        try {
            $this->botman->say(
                sprintf(
                    "<@%s> added an argument: \"%s\" for viewpoint %s.",
                    $this->user->getUsername(),
                    $this->name,
                    $viewpoint
                ),
                $this->botman->getMessage()->getRecipient()
            );
        } catch (BotManException $exception) {
            Log::error($exception->getMessage());
        }

    }
}
