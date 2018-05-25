<?php
use App\Http\Controllers\StartDiscussionController;

$botman = resolve('botman');

$botman->hears('/discussion start {name}', StartDiscussionController::class);
