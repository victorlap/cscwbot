<?php

namespace App\Http\Controllers;

use App\Argument;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Slack\Extensions\Menu;
use Illuminate\Support\Facades\Log;

class AddArgumentController extends Controller
{

    protected $botman;

    protected $bot;

    protected $user;

    protected $viewpoint;

    protected $name;

    protected $channel;

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

        // Inside your conversation
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

        $vart = "{\n    \"text\": \"Would you like to play a game?\",\n    \"attachments\": [\n        {\n            \"text\": \"Choose a game to play\",\n            \"fallback\": \"You are unable to choose a game\",\n            \"callback_id\": \"wopr_game\",\n            \"color\": \"#3AA3E3\",\n            \"attachment_type\": \"default\",\n            \"actions\": [\n                {\n                    \"name\": \"game\",\n                    \"text\": \"Chess\",\n                    \"type\": \"button\",\n                    \"value\": \"chess\"\n                },\n                {\n                    \"name\": \"game\",\n                    \"text\": \"Falken's Maze\",\n                    \"type\": \"button\",\n                    \"value\": \"maze\"\n                },\n                {\n                    \"name\": \"game\",\n                    \"text\": \"Thermonuclear War\",\n                    \"style\": \"danger\",\n                    \"type\": \"button\",\n                    \"value\": \"war\",\n                    \"confirm\": {\n                        \"title\": \"Are you sure?\",\n                        \"text\": \"Wouldn't you prefer a good game of chess?\",\n                        \"ok_text\": \"Yes\",\n                        \"dismiss_text\": \"No\"\n                    }\n                }\n            ]\n        }\n    ]\n}";

        $this->botman->ask($vart, function (Answer $answer) {
            $selectedOptions = $answer->getValue();
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
