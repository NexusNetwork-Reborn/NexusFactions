<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Quest\Types;

use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Quest\Quest;
use Xekvern\Core\Server\Price\Event\ItemSellEvent;
use Xekvern\Core\Translation\Translation;

class SellQuest extends Quest {

    /** @var Item */
    private $item = null;

    /**
     * SellQuest constructor.
     * @param string $name
     * @param string $description
     * @param int $targetValue
     * @param int $difficulty
     * @param Item|null $item
     */
    public function __construct(string $name, string $description, int $targetValue, int $difficulty, ?Item $item = null) {
        $this->item = $item;
        $callable = function (ItemSellEvent $event) {
            $player = $event->getPlayer();
            if (!$player instanceof NexusPlayer) {
                return;
            }
            if ($this->item !== null) {
                $item = $event->getItem();
                if (!$item->equals($this->item)) {
                    return;
                }
            }
            $session = Nexus::getInstance()->getPlayerManager()->getQuestHandler()->getSession($player);
            if ($session->getQuestProgress($this) === -1) {
                return;
            }
            $session->updateQuestProgress($this, $event->getProfit());
            if ($session->getQuestProgress($this) >= $this->targetValue) {
                $player->getDataSession()->addQuestPoints($this->getDifficulty());
                $player->sendMessage(Translation::getMessage("questComplete", ["name" => TextFormat::YELLOW . $this->name, "amount" => TextFormat::LIGHT_PURPLE . $this->difficulty]));
            }
        };
        parent::__construct($name, $description, self::SELL, $targetValue, $difficulty, $callable);
    }
}