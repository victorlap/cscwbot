<?php

namespace App\Http\Controllers;

use App\Clients\Slack;
use App\Viewpoint;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Interfaces\UserInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class AddViewpointController extends Controller
{
    protected $botman;

    protected $bot;

    protected $user;

    protected $name;

    protected $channel;

    /**
     * @param BotMan $bot
     * @param string $name
     */
    public function __invoke($bot, $name)
    {
        $this->botman = $bot;
        $this->name = $name;
        $this->user = $bot->getUser();

        $bot->reply("Hello, your viewpoint is being added.");
        $this->addViewpoint($name);

    }

    public function addViewpoint($name)
    {
        Viewpoint::create([
            'viewpoint' => $name,
            'author' => $this->user->getUsername()
        ]);
    }
}