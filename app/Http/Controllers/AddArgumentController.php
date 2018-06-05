<?php

namespace App\Http\Controllers;

use App\Argument;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
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
        $this->name = $name;
        $this->user = $bot->getUser();

        $bot->reply("Hello, your argument is being added.");
        $this->addArgument($viewpoint, $name);
    }

    public function addArgument($name)
    {

        Argument::create([
            'argument' => $name,
            'viewpoint_id' => $this->viewpoint,
            'author' => $this->user->getUsername()
        ]);

        try {
            $this->botman->say(
                sprintf(
                    "<@%s> added an argument: \"%s\".",
                    $this->user->getUsername(),
                    $this->name
                ),
                $this->botman->getMessage()->getRecipient()
            );
        } catch (BotManException $exception) {
            Log::error($exception->getMessage());
        }

    }
}
