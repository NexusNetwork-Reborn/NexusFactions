<?php

namespace Xekvern\Core\Provider\Task;

use pocketmine\scheduler\Task;
use Xekvern\Core\Nexus;

class DropTableTask extends Task
{

    public function onRun(): void
    {
        Nexus::getInstance()->getMySQLProvider()->getDatabase()->query("CREATE TABLE IF NOT EXISTS heartbeat(test VARCHAR(16));");
        Nexus::getInstance()->getMySQLProvider()->getDatabase()->query("DROP TABLE IF EXISTS heartbeat");
    }
}
