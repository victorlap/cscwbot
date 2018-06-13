<?php

namespace App\Http\Controllers;

use App\Argument;
use App\Clients\Slack;
use App\Discussion;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Illuminate\Support\Facades\Log;

class RateArgumentsController extends Controller
{
    /**
     * @param BotMan $bot
     */
    public function __invoke($bot)
    {

        $channel = $bot->getMessage()->getRecipient();

        $bot->startConversation(new RateArgumentsConversation($channel));
    }
}

class RateArgumentsConversation extends Conversation
{
    protected $channel;
    protected $arguments;
    protected $argument;
    protected $active_argument = 0;
    protected $author;
    protected $toBeDeleted = [];

    public function introduceRating()
    {
        $discussion = Discussion::where('discussion_channel', $this->channel)->first();
        if ($discussion->state !== 'rate_arguments') {
            $this->say('You need to be in the rating round to rate arguments.');
            return true;
        } else {
            $this->ask('Do you want to start rating the arguments? Type `start` to start voting and `stop` if you want to cancel.', function (Answer $answer) {
                if ($answer->getText() == 'start') {
                    $this->reply('You can sore each argument from one of: [-1, 0, 1, 2].');
                    app(Slack::class)->deleteMessage($answer->getMessage()->getRecipient(), $answer->getMessage()->getPayload()->get('ts'));
                    $this->rateArguments();
                }
            });
        }
    }

    public function rateArguments()
    {
        if ($this->active_argument >= count($this->arguments)) {
            $this->concludeRating();
            return true;
        }

        $this->argument = $this->arguments[$this->active_argument];
        $this->ask('Viewpoint: "' . $this->argument->viewpoint->viewpoint . '" - Argument ' . ($this->active_argument + 1) . ': *' . $this->argument->argument . '*', function (Answer $answer) {
            $this->deleteMessages($answer);
            if ($answer->getText() === '-1' || $answer->getText() === '0' || $answer->getText() === '1' || $answer->getText() === '2') {
                $this->active_argument += 1;

                Argument::where('id', $this->argument->id)
                    ->increment('priority', intval($answer->getText()));

                $this->rateArguments();
            } else {
                $this->reply('You can only rate using `-1`, `0`, `1` and `2`.');
                $this->rateArguments();
            }
        });

    }

    public function concludeRating()
    {
        $this->say(
            'Thank you for rating the arguments. When moving to the voting round, you will get an overview of all arguments.'
        );
    }

    public function deleteMessages(Answer $answer)
    {
        $this->toBeDeleted[] = $answer->getMessage()->getPayload()->get('ts');

        foreach ($this->toBeDeleted as $ts) {
            app(Slack::class)->deleteMessage($answer->getMessage()->getRecipient(), $ts);
        }

    }

    /** Override */
    public function ask($question, $next, $additionalParameters = [])
    {
        $this->reply($question, $additionalParameters);
        $this->bot->storeConversation($this, $next, $question, $additionalParameters);

        return $this;
    }

    public function reply($message, $additionalParameters = [])
    {
        $reply = $this->bot->reply($message, $additionalParameters);

        $reply = json_decode($reply->getContent());

        Log::info('toBeDeleted[]', [$reply]);

        if(isset($reply->ts)) {
            $this->toBeDeleted[] = json_decode($reply->getContent())->ts;
        }

        return $this;
    }



    public function stopsConversation(IncomingMessage $message)
    {
        if ($message->getText() == 'stop') {
            return true;
        }

        return false;
    }

    public function __construct($channel)
    {
        $this->channel = $channel;
        $discussion = Discussion::where('discussion_channel', $this->channel)->first();

        $this->arguments = Argument::whereIn('viewpoint_id', $discussion->viewpoints()->pluck('id'))->get();
    }

    public function run()
    {
        $this->introduceRating();
    }
}
