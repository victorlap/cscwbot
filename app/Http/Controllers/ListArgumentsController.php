<?php

namespace App\Http\Controllers;

use App\Discussion;
use BotMan\BotMan\BotMan;

class ListArgumentsController extends Controller
{
    protected $botman;

    protected $bot;

    protected $user;

    protected $name;

    protected $channel;

    /**
     * @param BotMan $bot
     */
    public function __invoke($bot)
    {
        $this->botman = $bot;
        $this->user = $bot->getUser();

        $this->botman->reply($this->listViewpoints($this->botman->getMessage()->getRecipient()));

    }

    public static function listArguments($channel)
    {

        $discussion = Discussion::where('discussion_channel', $channel)->first();
        $viewpoints = $discussion->viewpoints;
        $viewpoints_string = '';
        foreach ($viewpoints as $viewpoint) {
            $arguments_string = '';
            foreach ($viewpoint->arguments as $argument) {
                $arguments_string .= sprintf(
                    "\n%s",
                    $argument->argument
                );
            }
            $viewpoints_string .= sprintf(
                "\n*%s*, supporting arguments:",
                $viewpoint->viewpoint,
                $arguments_string
            );
        }

        return (
        sprintf(
            "The viewpoints and their arguments are: %s",
            $viewpoints_string
        )
        );

    }
}
