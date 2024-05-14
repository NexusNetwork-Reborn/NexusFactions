<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Quest\Types;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Quest\Quest;
use Xekvern\Core\Translation\Translation;

class KillQuest extends Quest {

    /**
     * SellQuest constructor.
     * @param string $name
     * @param string $description
     * @param int $targetValue
     * @param int $difficulty
     */
    public function __construct(string $name, string $description, int $targetValue, int $difficulty) {
        $callable = function (EntityDamageByEntityEvent $event) {
            $player = $event->getEntity();
            if (!$player instanceof NexusPlayer) {
                return;
            }
            $killer = $event->getDamager();
            if (!$killer instanceof NexusPlayer) {
                return;
            }
            if ($player->getHealth() > $event->getFinalDamage()) {
                return;
            }
            $session = Nexus::getInstance()->getPlayerManager()->getQuestHandler()->getSession($killer);
            if ($session->getQuestProgress($this) === -1) {
                return;
            }
            $session->updateQuestProgress($this);
            if ($session->getQuestProgress($this) >= $this->targetValue) {
                $player->getDataSession()->addQuestPoints($this->getDifficulty());
                $player->sendMessage(Translation::getMessage("questComplete", ["name" => TextFormat::YELLOW . $this->name, "amount" => TextFormat::LIGHT_PURPLE . $this->difficulty]));
            }
        };
        parent::__construct($name, $description, self::KILL, $targetValue, $difficulty, $callable);
    }
}