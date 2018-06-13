<?php

namespace App\Http\Controllers;

use App\Clients\Slack;
use App\Discussion;
use App\Viewpoint;
use App\Vote;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Interfaces\UserInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class EndDiscussionController extends Controller
{
    /** @var Botman */
    protected $botman;
    protected $viewpoint;

    /** @var UserInterface */
    protected $user;

    /** @var Discussion */
    protected $discussion;

    /**
     * @param BotMan $bot
     * @param string $name
     */
    public function __invoke($bot, $viewpoint)
    {
        $this->botman = $bot;
        $this->viewpoint = $viewpoint;
        $this->user = $bot->getUser();
        $this->discussion = Discussion::where('discussion_channel', $this->botman->getMessage()->getRecipient())->first();
        $this->viewpoint = Viewpoint::findByNameOrId($viewpoint, $this->discussion->id);

        if ($this->discussion->state !== 'voting') {
            $this->say('You need to be in the voting round to end the discussion. Use `/round help` to see possible commands for this round.');
            return true;
        }


        if (!$this->viewpoint) {
            $this->botman->reply("Invalid viewpoint, try listing viewpoints with /viewpoint list");
            return;
        }

        $this->discussion->close($this->viewpoint->id);

        $this->sendConclusionToChannel($this->discussion->discussion_channel);
        $this->sendConclusionToOriginatingChannel($this->discussion->originating_channel);
        $this->closeChannel();
    }

    protected function sendConclusionToChannel($channel)
    {
        try {
            $viewpoints = $this->discussion->viewpoints;
            $viewpoints_string = '';
            foreach ($viewpoints as $viewpoint) {
                $votes = Vote::where('viewpoint_id', $viewpoint->id)->exists();
                $votes = ($votes ? $votes : 0);
                $viewpoints_string .= sprintf(
                    "\nID: *%s* - *%s* with *%s* vote(s).",
                    $viewpoint->id,
                    $viewpoint->viewpoint,
                    $votes
                );
            }
            $this->botman->say(
                "The voting results were: \n" . $viewpoints_string,
                $channel
            );

            $this->botman->say(
                sprintf(
                    "<@%s> ended the discussion about \"%s\" with the following conclusion \"%s\".",
                    $this->user->getId(),
                    $this->discussion->name,
                    $this->viewpoint->viewpoint
                ),
                $channel
            );
        } catch (BotManException $exception) {
        }
    }

    protected function sendConclusionToOriginatingChannel($channel)
    {
        try {
            $this->botman->say(
                sprintf(
                    "<@%s> ended the discussion about \"%s\" with the following conclusion \"%s\". See the conlusion in <#%s|%s>",
                    $this->user->getId(),
                    $this->discussion->name,
                    $this->viewpoint->viewpoint,
                    $this->discussion->discussion_channel,
                    '_discussion_'. ($this->discussion->id+1)
                ),
                $channel
            );
        } catch (BotManException $exception) {
        }
    }


    protected function closeChannel()
    {
        try {
            /** @var ResponseInterface $response */
            app(Slack::class)->archiveChannel($this->discussion->discussion_channel);
        } catch (RequestException $exception) {
        }
    }
}
