<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Task\TeleportTask;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Utils\LevelUtils;

class WildCommand extends Command {

    /**
     * WildCommand constructor.
     */
    public function __construct() {
        parent::__construct("wild", "Teleport into the wilderness.", "/wild");
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
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $world = $sender->getServer()->getWorldManager()->getWorldByName(Faction::CLAIM_WORLD);
        $x = mt_rand(100, 8000);
        $z = mt_rand(100, 8000);
        $world->orderChunkPopulation($x >> 4, $z >> 4, null)->onCompletion(function(Chunk $chunk) use ($sender, $world, $x, $z) : void {
            $y = $world->getHighestBlockAt($x, $z);
            Nexus::getInstance()->getScheduler()->scheduleRepeatingTask(new TeleportTask($sender, new Position($x, $y, $z, $world), 5), 20);
        }, function() : void {
            // Chunk generation failed.
        });
    }
}