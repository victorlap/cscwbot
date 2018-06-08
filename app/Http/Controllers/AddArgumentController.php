<?php

namespace App\Http\Controllers;

use App\Argument;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
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

        $json_string = "{
    \"text\": \"Would you like to play a game?\",
    \"attachments\": [
        {
            \"text\": \"Choose a game to play\",
            \"fallback\": \"You are unable to choose a game\",
            \"callback_id\": \"wopr_game\",
            \"color\": \"#3AA3E3\",
            \"attachment_type\": \"default\",
            \"actions\": [
                {
                    \"name\": \"game\",
                    \"text\": \"Chess\",
                    \"type\": \"button\",
                    \"value\": \"chess\"
                },
                {
                    \"name\": \"game\",
                    \"text\": \"Falken's Maze\",
                    \"type\": \"button\",
                    \"value\": \"maze\"
                },
                {
                    \"name\": \"game\",
                    \"text\": \"Thermonuclear War\",
                    \"style\": \"danger\",
                    \"type\": \"button\",
                    \"value\": \"war\",
                    \"confirm\": {
                        \"title\": \"Are you sure?\",
                        \"text\": \"Wouldn't you prefer a good game of chess?\",
                        \"ok_text\": \"Yes\",
                        \"dismiss_text\": \"No\"
                    }
                }
            ]
        }
    ]
}";

//        $question = Question::create('Do you need a database?')
//            ->fallback('Unable to create a new database')
//            ->callbackId('create_database')
//            ->addButtons([
//                Button::create('Of course')->value('yes'),
//                Button::create('Hell no!')->value('no'),
//            ]);

        $this->botman->ask("Mooi werk?", function (Answer $answer) {
            // Detect if button was clicked:
            if ($answer->isInteractiveMessageReply()) {
                $selectedValue = $answer->getValue(); // will be either 'yes' or 'no'
                $selectedText = $answer->getText(); // will be either 'Of course' or 'Hell no!'

                $this->say("Your choice: " . $selectedValue);
            }
        });

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
