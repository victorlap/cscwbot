<?php

use App\Http\Controllers\AddArgumentController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\ListViewpointsController;
use App\Http\Controllers\AddViewpointController;
use App\Http\Controllers\StartDiscussionController;

$botman = resolve('botman');

$botman->hears('/discussion help', HelpController::class);
$botman->hears('/discussion {name}', StartDiscussionController::class);

$botman->hears('/viewpoint {name}', AddViewpointController::class);
$botman->hears('/viewpoints', ListViewpointsController::class);

$botman->hears('/argument {name}', AddArgumentController::class);
