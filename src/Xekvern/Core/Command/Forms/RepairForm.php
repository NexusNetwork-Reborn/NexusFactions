<?php

namespace Xekvern\Core\Command\Forms;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\ModalForm;
use pocketmine\item\Durable;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilUseSound;

class RepairForm extends ModalForm {

    /** @var int */
    private $cost;

    /**
     * RepairForm constructor.
     *
     * @param Player $player
     */
    public function __construct(Player $player) {
        $item = $player->getInventory()->getItemInHand();
        $levels = 0;
        foreach($item->getEnchantments() as $enchantment) {
            $levels = $levels + $enchantment->getLevel();
        }
        $damage = $item->getDamage();
        if($levels == 0) {
            $cost = $damage * 1;
        }
        else {
            $factor = $levels * 1;
            $cost = $item->getDamage() * $factor;
        }
        $this->cost = $cost;
        $title = TextFormat::BOLD . TextFormat::AQUA . "Repair";
        $text = "Would you like to repair the item you currently are holding? The cost will be $$cost.";
        parent::__construct($title, $text);
    }

    /**
     * @param Player $player
     * @param bool $choice
     *
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, bool $choice): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if($choice == true) {
            if($player->getDataSession()->getBalance() >= $this->cost) {
                $item = $player->getInventory()->getItemInHand();
                if(!$item instanceof Durable) {
                    $player->sendMessage(Translation::RED . "Your item must be durable!");
                    return;
                }
                $player->getInventory()->setItemInHand($item->setDamage(0));
                $player->getDataSession()->subtractFromBalance($this->cost);
                $player->sendMessage(Translation::getMessage("successRepair"));
                $player->getWorld()->addSound($player->getEyePos(), new AnvilUseSound());
                return;
            }
            $player->sendMessage(Translation::getMessage("notEnoughMoney"));
            return;
        }
        return;
    }
}