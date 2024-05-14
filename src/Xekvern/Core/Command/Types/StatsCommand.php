<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use libs\muqsit\arithmexp\Util;
use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Utils\Utils;

class StatsCommand extends Command {

    /**
     * StatsCommand constructor.
     */
    public function __construct() {
        parent::__construct("stats", "Check server status.", "/stats");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $sender->sendMessage(TextFormat::AQUA . TextFormat::BOLD . "SERVER STATS");
        $sender->sendMessage(" ");
        $tps = Server::getInstance()->getTicksPerSecond();
        $average = Server::getInstance()->getTicksPerSecondAverage();
        $tpsColor = TextFormat::GREEN;
        if($tps < 17) {
            $tpsColor = TextFormat::GOLD;
        }
        elseif($tps < 12) {
            $tpsColor = TextFormat::RED;
        }
        $sender->sendMessage(TextFormat::YELLOW . "  Ticks per second: " . $tpsColor . "$tps (avg: $average)");
        $sender->sendMessage(TextFormat::YELLOW . "  Restarting in: " . TextFormat::WHITE . Utils::secondsToTime(Nexus::getInstance()->getServerManager()->getAnnouncementHandler()->getRestarter()->getRestartProgress()) . TextFormat::RED . "(approximately)");
    }
}