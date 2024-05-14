<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\ItemHandler;

class GodCommand extends Command {

    /**
     * GodCommand constructor.
     */
    public function __construct() {
        parent::__construct("god", "Make xClqut a god.", "/god");
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
        if(!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $enchantments = ItemHandler::getEnchantments();
        $items = [
            VanillaItems::DIAMOND_HELMET(),
            VanillaItems::DIAMOND_CHESTPLATE(),
            VanillaItems::DIAMOND_LEGGINGS(),
            VanillaItems::DIAMOND_BOOTS(),
            VanillaItems::DIAMOND_SWORD(),
            VanillaItems::DIAMOND_PICKAXE(),
            VanillaItems::BOW(),
        ];
        $newItems = [];
        /** @var Item $item */
        foreach($items as $item) {
            foreach($enchantments as $enchantment) {
                if(ItemHandler::canEnchant($item, $enchantment)) {
                    $item->addEnchantment(new EnchantmentInstance($enchantment, $enchantment->getMaxLevel()));
                }
            }
            $newItems[] = ItemHandler::setLoreForItem($item);
        }
        foreach($newItems as $item) {
            $sender->getInventory()->addItem($item);
        }
    }
}
