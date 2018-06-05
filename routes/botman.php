<?php

use App\Http\Controllers\AddArgumentController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\StartDiscussionController;

$botman = resolve('botman');

$botman->hears('/discussion help', HelpController::class);
$botman->hears('/discussion {name}', StartDiscussionController::class);

$botman->hears('/argument {name}', AddArgumentController::class);
