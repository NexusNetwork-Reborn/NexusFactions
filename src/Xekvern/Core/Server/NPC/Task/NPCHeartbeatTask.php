<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\NPC\Task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use Xekvern\Core\Server\NPC\NPCHandler;

class NPCHeartbeatTask extends Task
{

    /** @var NPCHandler */
    private $manager;

    /** @var Player[] */
    private $players;

    /**
     * NPCHeartbeatTask constructor.
     *
     * @param NPCHandler $manager
     */
    public function __construct(NPCHandler $manager)
    {
        $this->manager = $manager;
        $this->players = Server::getInstance()->getOnlinePlayers();
    }

    public function onRun(): void
    {
        if (empty($this->players)) {
            $this->players = Server::getInstance()->getOnlinePlayers();
            if (empty($this->players)) {
                return;
            }
            foreach ($this->manager->getNPCs() as $npc) {
                $npc->updateNameTag();
            }
            return;
        }
        $player = array_shift($this->players);
        if (!$player instanceof Player) {
            return;
        }
        if ($player->isOnline() === false) {
            return;
        }
        foreach ($this->manager->getNPCs() as $npc) {
            $npc->tick($player);
        }
    }
}
