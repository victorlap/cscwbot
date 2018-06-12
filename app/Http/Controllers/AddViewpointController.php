<?php

namespace App\Http\Controllers;

use App\Discussion;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use Illuminate\Support\Facades\Log;

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

        if ($name == 'list') {
            return;
        }

        $this->addViewpoint($name);

    }

    public function addViewpoint($name)
    {

        $discussion = Discussion::where('discussion_channel', $this->botman->getMessage()->getRecipient())->first();
        Log::debug('Discussion ID =  ' . $discussion->id);

        $discussion->viewpoints()->create([
            'viewpoint' => $name,
            'author' => $this->user->getUsername(),
        ]);

        try {
            $this->botman->say(
                sprintf(
                    "@%s added a viewpoint: \"%s\".",
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
