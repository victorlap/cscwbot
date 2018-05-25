<?php
use App\Http\Controllers\HelpController;
use App\Http\Controllers\StartDiscussionController;

$botman = resolve('botman');

$botman->hears('/discussion help', HelpController::class);
$botman->hears('/discussion {name}', StartDiscussionController::class);
