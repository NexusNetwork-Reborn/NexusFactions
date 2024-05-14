<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\permission\DefaultPermissionNames;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\EnchantmentScroll;

class EnchantCommand extends Command
{

    /**
     * EnchantCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("enchant", "Add an enchantment to an item", "/enchant <enchantment> <level>");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ((!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) or (!$sender instanceof NexusPlayer)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $enchantment = ItemHandler::getEnchantment($args[0]);
        if ($enchantment === null) {
            $sender->sendMessage(Translation::getMessage("invalidEnchantment"));
            return;
        }
        $level = (int)$args[1];
        if ((!is_numeric($level)) or $enchantment->getMaxLevel() < $level) {
            $sender->sendMessage(Translation::getMessage("invalidAmount"));
            return;
        }
        $item = $sender->getInventory()->getItemInHand();
        if (ItemHandler::canEnchant($item, $enchantment) === false) {
            $sender->sendMessage(Translation::getMessage("invalidItem"));
            return;
        }
        $tag = $item->getNamedTag();
        if(isset($tag->getValue()[EnchantmentScroll::SCROLL_AMOUNT])) {
            $amount = $tag->getInt(EnchantmentScroll::SCROLL_AMOUNT);
            if(count($item->getEnchantments()) >= ItemHandler::MAX_ENCHANT_LIMIT) {
                $sender->sendMessage(Translation::RED . "You cannot add more enchantments to this item since it is on the max limit.");
                return;
            }
            if(count($item->getEnchantments()) >= $amount && !$item->hasEnchantment($enchantment) && count($item->getEnchantments()) >= ItemHandler::MAX_ENCHANT_LIMIT) {
                $tag->setInt(EnchantmentScroll::SCROLL_AMOUNT, $amount + 1);
            }
        } 
        $enchantment = new EnchantmentInstance($enchantment, $level);
        $item->addEnchantment($enchantment);
        $sender->getInventory()->setItemInHand(ItemHandler::setLoreForItem($item));
        $sender->sendMessage(Translation::getMessage("successAbuse"));
        return;
    }
}
