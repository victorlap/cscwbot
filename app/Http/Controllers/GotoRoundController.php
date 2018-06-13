<?php

namespace App\Http\Controllers;

use App\Discussion;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;

class GotoRoundController extends Controller
{
    public function __invoke(BotMan $bot, $state)
    {
        /** @var Discussion $discussion */
        $channel = $bot->getMessage()->getRecipient();
        $discussion = Discussion::where('discussion_channel', $channel)->first();

        if (!$discussion) {
            $bot->reply("It looks like you are not in a discussion channel. \n Switching rounds can only be done while in a discussion.");
            return;
        }

        if (!$discussion->isValidState($state)) {
            $bot->reply(sprintf("%s is not a valid round!", $state));
            return;
        }

        if (!$discussion->canMoveState($state)) {
            $bot->reply(sprintf("Can not move to round %s while in %s", $state, $discussion->state));
            return;
        }

        $discussion->update(['state' => $state]);

        try {
            if($discussion->state_name) {
                $bot->say(sprintf("The %s has begun!", $discussion->state_name), $channel);
            }

            if($discussion->state == Discussion::STATE_RATE_ARGUMENTS) {
                $bot->say("The following arguments will be rated by each participant in this round:", $channel);
                $bot->say(ListArgumentsController::listArguments($channel), $channel);
            }

            if($discussion->state == Discussion::STATE_VOTING) {
                $bot->say(ListArgumentsController::listArguments($channel), $channel);
            }
        } catch (BotManException $e) {
        }
    }
}
