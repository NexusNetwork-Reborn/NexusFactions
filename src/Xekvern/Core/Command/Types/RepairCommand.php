<?php

declare(strict_types = 1);


namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Forms\RepairForm;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\item\Durable;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilUseSound;

class RepairCommand extends Command {

    /**
     * RepairCommand constructor.
     */
    public function __construct() {
        parent::__construct("repair", "Repair an item", "/repair", ["fix"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof NexusPlayer) {
            if(isset($args[0]) and $args[0] === "all") {
                if(!$sender->hasPermission("permission.fixall")) {
                    $sender->sendMessage(Translation::getMessage("noPermission"));                     
                    return;
                }
                $cooldown = 300 - (time() - $sender->getLastRepair());
                if($cooldown > 0) {
                    $sender->sendMessage(Translation::getMessage("actionCooldown", [
                        "amount" => TextFormat::RED . $cooldown
                    ]));                  
                    return;
                }
                $count = 0;
                foreach($sender->getInventory()->getContents() as $slot => $item) {
                    if($item instanceof Durable and $item->getDamage() > 0) {
                        $count++;
                        $sender->getInventory()->setItem($slot, $item->setDamage(0));
                    }
                }
                foreach($sender->getArmorInventory()->getContents() as $slot => $item) {
                    if($item instanceof Durable and $item->getDamage() > 0) {
                        $count++;
                        $sender->getArmorInventory()->setItem($slot, $item->setDamage(0));
                    }
                }
                if($count > 0) {
                    $sender->sendMessage(Translation::getMessage("successRepair", [
                        "amount" => $count
                    ]));
                    $sender->getWorld()->addSound($sender->getEyePos(), new AnvilUseSound());
                    $sender->setLastRepair();
                }
                else {
                    $sender->sendMessage(Translation::getMessage("nothingToRepair"));                    
                }
                return;
            }
            $item = $sender->getInventory()->getItemInHand();
            if(!$item instanceof Durable) {
                $sender->sendTitle(TextFormat::BOLD . TextFormat::RED . "Invalid Item", TextFormat::GRAY . "Your item must be durable!");
                $sender->sendMessage(Translation::getMessage("invalidItem"));                 
                return;
            }
            if(!$sender->hasPermission("permission.freefix")) {
                $sender->sendForm(new RepairForm($sender));
                return;
            }
            $sender->getInventory()->setItemInHand($item->setDamage(0));
            $sender->sendMessage(Translation::getMessage("successRepair", [
                "amount" => 1
            ]));
            $sender->broadcastSound(new AnvilUseSound());
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
    }
}