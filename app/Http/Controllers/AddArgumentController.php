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

        $this->ask($question, function (Answer $answer) {
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
