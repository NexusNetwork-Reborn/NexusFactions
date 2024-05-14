<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Quest\Types;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Quest\Quest;
use Xekvern\Core\Translation\Translation;

class BreakQuest extends Quest {

    /** @var int */
    private $block;

    /**
     * BreakQuest constructor.
     * @param string $name
     * @param string $description
     * @param int $targetValue
     * @param int $difficulty
     * @param int $blockId
     */
    public function __construct(string $name, string $description, int $targetValue, int $difficulty, int $blockId) {
        $this->block = $blockId;
        $callable = function (BlockBreakEvent $event) {
            $block = $event->getBlock();
            $player = $event->getPlayer();
            if (!$player instanceof NexusPlayer) {
                return;
            }
            if ($block->getTypeId() === $this->block) {
                $session = Nexus::getInstance()->getPlayerManager()->getQuestHandler()->getSession($player);
                if ($session->getQuestProgress($this) === -1) {
                    return;
                }
                $session->updateQuestProgress($this);
                if ($session->getQuestProgress($this) >= $this->targetValue) {
                    $player->getDataSession()->addQuestPoints($this->getDifficulty());
                    $player->sendMessage(Translation::getMessage("questComplete", ["name" => TextFormat::YELLOW . $this->name, "amount" => TextFormat::LIGHT_PURPLE . $this->difficulty]));
                }
            }
        };
        parent::__construct($name, $description, self::BREAK, $targetValue, $difficulty, $callable);
    }
}