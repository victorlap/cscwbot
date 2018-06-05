<?php

namespace App\Http\Controllers;

use App\Discussion;
use BotMan\BotMan\BotMan;

class GotoRoundController extends Controller
{
    public function __invoke(BotMan $bot, $state)
    {
        /** @var Discussion $discussion */
        $discussion = Discussion::where('discussion_channel', $bot->getMessage()->getRecipient())->first();

        if(!$discussion) {
            $bot->reply("It looks like you are not in a discussion channel. \n Switching rounds can only be done while in a discussion.");
            return;
        }

        if(!$discussion->isValidState($state)) {
            $bot->reply(sprintf("%s is not a valid round!", $state));
            return;
        }

        if(!$discussion->canMoveState($state)) {
            $bot->reply(sprintf("Can not move to round %s while in %s", $state, $discussion->state));
            return;
        }

        $discussion->update(['state' => $state]);

        $bot->reply(sprintf("Moved to round %s", $state));
    }
}
