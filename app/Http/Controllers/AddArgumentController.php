<?php

namespace App\Http\Controllers;

use App\Argument;
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

        $this->botman->startConversation(new AddAgrumentConversation);

//        $this->addArgument($viewpoint, $name);

//        $this->botman->ask('One more thing - what is your email?', function(Answer $answer) {
//            // Save result
//            $this->email = $answer->getText();
//
//            $this->say('Great - that is all we need: ' . $this->email);
//        });

//        try {
//            $question = Question::create('Do you need a database?')
//                ->fallback('Unable to create a new database')
//                ->callbackId('create_database')
//                ->addButtons([
//                    Button::create('Of course')->value('yes'),
//                    Button::create('Hell no!')->value('no'),
//                ]);
//
//            $response = $this->botman->sendRequest('chat.postMessage', $question);
//            Log::debug(json_decode($response));
//        } catch (BotManException $exception) {
//            Log::error($exception->getMessage());
//        }

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

class AddAgrumentConversation extends Conversation
{
    protected $firstname;

    protected $email;

    public function askForDatabase()
    {
        $question = Question::create('Would you like to play a game?')
            ->callbackId('game_selection')
            ->addAction(
                Menu::create('Pick a game...')
                    ->name('games_list')
                    ->options([
                        [
                            'text' => 'Hearts',
                            'value' => 'hearts',
                        ],
                        [
                            'text' => 'Bridge',
                            'value' => 'bridge',
                        ],
                        [
                            'text' => 'Poker',
                            'value' => 'poker',
                        ]
                    ])
            );

        $this->ask($question, function (Answer $answer) {
            // Detect if button was clicked:
            if ($answer->isInteractiveMessageReply()) {
                $selectedValue = $answer->getValue(); // will be either 'yes' or 'no'
                $selectedText = $answer->getText(); // will be either 'Of course' or 'Hell no!'
            }
        });
    }

    public function askFirstname()
    {
        $this->ask('Hello! What is your firstname?', function(Answer $answer) {
            // Save result
            $this->firstname = $answer->getText();

            $this->say('Nice to meet you '.$this->firstname);
            $this->askEmail();
        });
    }

    public function askEmail()
    {
        $this->ask('One more thing - what is your email?', function(Answer $answer) {
            // Save result
            $this->email = $answer->getText();

            $this->say('Great - that is all we need, '.$this->firstname);
        });
    }

    public function run()
    {
        // This will be called immediately
        $this->askForDatabase();
    }
}