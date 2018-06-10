<?php

use App\Http\Controllers\AddArgumentController;
use App\Http\Controllers\AddViewpointController;
use App\Http\Controllers\EndDiscussionController;
use App\Http\Controllers\GotoRoundController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\ListArgumentsController;
use \App\Http\Controllers\RateArgumentsController;
use App\Http\Controllers\ListViewpointsController;
use App\Http\Controllers\StartDiscussionController;

/** @var \BotMan\BotMan\BotMan $botman */
$botman = resolve('botman');

$botman->hears('/discussion help', HelpController::class);
$botman->hears('/discussion end {viewpoint}', EndDiscussionController::class);
$botman->hears('/discussion {name}', StartDiscussionController::class);

$botman->hears('/viewpoint list', ListViewpointsController::class);
$botman->hears('/viewpoint {name}', AddViewpointController::class);

$botman->hears('/argument list', ListArgumentsController::class);
$botman->hears('/argument rate', RateArgumentsController::class);
$botman->hears('/argument {argument}', AddArgumentController::class);

$botman->hears('/argument list', ListArgumentsController::class);

$botman->hears('/goto {round}', GotoRoundController::class);
