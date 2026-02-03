<?php

namespace Practicecore\Commands;

use Practicecore\Command;

class BotDuelCommand extends Command {
    public function __construct() {
        // Set permission here
        $this->setPermission('your_permission_name');
    }
}