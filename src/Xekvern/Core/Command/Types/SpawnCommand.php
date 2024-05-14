<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Task\TeleportTask;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\world\format\Chunk;
use Xekvern\Core\Nexus;

class SpawnCommand extends Command {

    /**
     * SpawnCommand constructor.
     */
    public function __construct() {
        parent::__construct("spawn", "Teleport to spawn.", "/spawn");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender instanceof NexusPlayer) {
            return;
        }
        $world = $sender->getServer()->getWorldManager()->getDefaultWorld();
        $pos = $sender->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation();
        $world->orderChunkPopulation($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4, null)->onCompletion(function() use ($sender, $pos) : void {
            Nexus::getInstance()->getScheduler()->scheduleRepeatingTask(new TeleportTask($sender, $pos, 5), 20);
        }, function() : void {
            // Chunk generation failed.
        });
        return;
    }
}