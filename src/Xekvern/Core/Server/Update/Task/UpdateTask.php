<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Update\Task;

use libs\muqsit\arithmexp\Util;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\NexusException;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Utils\Utils;

class UpdateTask extends Task {

    /** @var Nexus */
    private $core;

    /** @var NexusPlayer[] */
    private $players;
    
    /**
     * UpdateTask constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $this->players = $core->getServer()->getOnlinePlayers();
    }

    /**
     * @param int $tick
     *
     * @throws NexusException
     */
    public function onRun(): void {
        if(empty($this->players)) {
            $this->players = $this->core->getServer()->getOnlinePlayers();
        }
        $player = array_shift($this->players);
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if($player->isOnline() === false) {
            return;
        }
        if($player->isLoaded() === false) {
            return;
        }
        try {
            $this->core->getServerManager()->getUpdateHandler()->updateScoreboard($player);
            $hp = round($player->getHealth(), 1);
            if($player->getCESession()->isHidingHealth()) {
                $hp = TextFormat::OBFUSCATED . $hp . TextFormat::RESET;
            }
            $player->setScoreTag(TextFormat::WHITE . $hp . TextFormat::RED . TextFormat::BOLD . " HP " . TextFormat::RESET . TextFormat::DARK_GRAY . "| " . TextFormat::BOLD . Utils::formatPing($player->getNetworkSession()->getPing()) . "ms");
            $info = implode(TextFormat::RESET . "\n", [
                TextFormat::BOLD . TextFormat::AQUA . "NeXus" . TextFormat::DARK_AQUA . "PE " . TextFormat::RESET . TextFormat::GRAY . "OP Factions",
                " ",
                TextFormat::WHITE . "Welcome to " . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Season ". ItemHandler::getRomanNumber(Nexus::SEASON) . TextFormat::RESET . TextFormat::WHITE . " of OP Factions",
                " ",
                TextFormat::WHITE . "Start your amazing adventures by joining a faction or",
                TextFormat::WHITE . "form your own using " . TextFormat::BOLD . TextFormat::AQUA . "/f create",
                " ",
                TextFormat::WHITE . "Use your Once Kit by using the command " . TextFormat::BOLD . TextFormat::AQUA . "/kit",
                TextFormat::WHITE . "and do " . TextFormat::BOLD . TextFormat::AQUA . "/wild" . TextFormat::RESET . TextFormat::WHITE . " to get to the wilderness.",
                " ",
                TextFormat::BOLD . TextFormat::DARK_RED . "GRACE PERIOD ENDS IN:",
                TextFormat::RED . Utils::secondsToTime($this->core->getGracePeriod())
            ]);
            $text = $player->getFloatingText("Info");
            if (Nexus::getInstance()->isInGracePeriod()) {
                $text->update($info);
                $text->sendChangesTo($player);
            } 
        }catch (\Error){}
    }
}