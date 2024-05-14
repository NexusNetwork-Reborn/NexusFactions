<?php

namespace Xekvern\Core\Server\Watchdog\Handler;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Server\Watchdog\Task\CheatLogTask;
use pocketmine\event\Event;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

abstract class Handler
{

    const VIOLATION_LIMIT = 20;

    /** @var int[] */
    protected $violations = [];

    /** @var int[] */
    protected $violationTimes = [];

    /** @var Nexus */
    protected $core;

    /**
     * Handler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
    }

    /**
     * @param NexusPlayer $player
     * @param Event $event
     */
    abstract public function check(NexusPlayer $player, Event $event): void;

    /**
     * @param NexusPlayer $player
     * @param string $cheat
     *
     * @return bool
     */
    public function handleViolations(NexusPlayer $player, string $cheat): bool
    {
        if (isset($this->violationTimes[$player->getUniqueId()->toString()])) {
            if (time() === $this->violationTimes[$player->getUniqueId()->toString()]) {
                return false;
            }
        }
        if (!isset($this->violations[$player->getUniqueId()->toString()])) {
            $this->violations[$player->getUniqueId()->toString()] = 0;
        }
        if (++$this->violations[$player->getUniqueId()->toString()] >= self::VIOLATION_LIMIT) {
            $this->violations[$player->getUniqueId()->toString()] = 0;
            $this->core->getScheduler()->scheduleDelayedTask(new class($player, $cheat) extends Task {

                /** @var NexusPlayer */
                private $player;

                /** @var string */
                private $cheat;

                /**
                 *  constructor.
                 *
                 * @param NexusPlayer $player
                 * @param string $cheat
                 */
                public function __construct(NexusPlayer $player, string $cheat)
                {
                    $this->player = $player;
                    $this->cheat = $cheat;
                }

                /**
                 * @param int $currentTick
                 */
                public function onRun(): void
                {
                    if ($this->player->isOnline() === false) {
                        return;
                    }
                    Server::getInstance()->broadcastMessage(Translation::getMessage("antiCheatKickBroadcast", [
                        "name" => TextFormat::RED . $this->player->getName(),
                        "reason" => TextFormat::YELLOW . $this->cheat
                    ]));
                    $this->player->close(null, Translation::getMessage("antiCheatKickMessage", [
                        "reason" => TextFormat::YELLOW . $this->cheat
                    ]));
                }
            }, 20);
        } else {
            $this->alert($player, $cheat);
            $this->violationTimes[$player->getUniqueId()->toString()] = time();
        }
        return true;
    }

    /**
     * @param NexusPlayer $player
     * @param string $cheat
     */
    public function alert(NexusPlayer $player, string $cheat): void
    {
        $violations = $this->violations[$player->getUniqueId()->toString()];
        $message = TextFormat::DARK_GRAY . "[" . TextFormat::AQUA . TextFormat::BOLD . "BOOP" . TextFormat::RESET . TextFormat::DARK_GRAY . "] " . TextFormat::YELLOW . "Suspicious activity from {$player->getName()}! Possible cheat: $cheat [V $violations]";
        foreach ($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
            if ($onlinePlayer->isLoaded() === false) {
                continue;
            }
            if ($onlinePlayer->getDataSession()->getRank()->getIdentifier() < Rank::TRIAL_MODERATOR or $onlinePlayer->getDataSession()->getRank()->getIdentifier() > Rank::OWNER) {
                continue;
            }
            $onlinePlayer->sendMessage($message);
        }
        $this->core->getLogger()->info($message);
        $this->core->getServer()->getAsyncPool()->increaseSize(2);
        $this->core->getServer()->getAsyncPool()->submitTaskToWorker(new CheatLogTask("[Faction] " . date("[n/j/Y][G:i:s]", time()) . TextFormat::clean($message)), 1);
    }

    public function resetViolations(): void
    {
        $this->violations = [];
        $this->violationTimes = [];
    }
}