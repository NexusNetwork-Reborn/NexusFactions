<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Price\Event\ItemSellEvent;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Rank\Rank;

class SellCommand extends Command {

    /**
     * RewardsCommand constructor.
     */
    public function __construct() {
        parent::__construct("sell", "Sell items", "/sell <hand|all|auto>", ["sa"]);
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
            $inventory = $sender->getInventory();
            $sellables = $this->getCore()->getServerManager()->getPriceHandler()->getSellables();
            if($commandLabel === "sa") {
                $this->sellAll($sender);
                return;
            }
            if(!isset($args[0])) {
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));        
                return;
            }
            switch($args[0]) {
                case "auto":
                    if(!$sender->hasPermission("permission.autosell")) {
                        $sender->sendMessage(Translation::getMessage("noPermission"));       
                        return;
                    }
                    $sender->setAutoSelling(!$sender->isAutoSelling());
                    $sender->sendMessage(Translation::getMessage("autoSellToggle"));
                    return;
                    break;
                case "hand":
                    $item = $inventory->getItemInHand();
                    $sellable = false;
                    $entry = null;
                    if(isset($sellables[$item->getTypeId()])) {
                        $entry = $sellables[$item->getTypeId()];
                        if($entry->equal($item)) {
                            $sellable = true;
                        }
                    }
                    if($sellable === false) {
                        $sender->sendMessage(Translation::getMessage("nothingSellable"));
                        return;
                    }
                    $count = $item->getCount();
                    $price = $count * $entry->getSellPrice();
                    $inventory->removeItem($item);
                    $sender->getDataSession()->addToBalance($price);
                    $event = new ItemSellEvent($sender, $item, $price);
                    $event->call();
                    $item = $entry->getName();
                    $sender->sendMessage(Translation::getMessage("sell", [
                        "amount" => TextFormat::GREEN . number_format((int)$count),
                        "item" => TextFormat::DARK_GREEN . $item,
                        "price" => TextFormat::LIGHT_PURPLE . "$" . number_format((int)$price),
                    ]));
                    return;
                    break;
                case "all":
                    $this->sellAll($sender);
                    break;
            }
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }

    private function sellAll(NexusPlayer $sender): void {
        $inventory = $sender->getInventory();
        $sellables = $this->getCore()->getServerManager()->getPriceHandler()->getSellables();
        $content = $sender->getInventory()->getContents();
        /** @var Item[] $items */
        $items = [];
        $sellable = false;
        $entries = [];
        foreach($content as $item) {
            if(!isset($sellables[$item->getTypeId()])) {
                continue;
            }
            $entry = $sellables[$item->getTypeId()];
            if(!$entry->equal($item)) {
                continue;
            }
            if($sellable === false) {
                $sellable = true;
            }
            if(!isset($entries[$entry->getName()])) {
                $entries[$entry->getName()] = $entry;
                $items[$entry->getName()] = $item;
            }
            else {
                $items[$entry->getName()]->setCount($items[$entry->getName()]->getCount() + $item->getCount());
            }
        }
        if($sellable === false and $sender->isAutoSelling() === false) {
            $sender->sendMessage(Translation::getMessage("nothingSellable"));
            return;
        }
        $price = 0;
        $boost = 1;
        $rankBoosts = [
            Rank::HOPLITE => 0.05,
            Rank::PRINCE => 0.10,
            Rank::TITAN => 0.12,
            Rank::SPARTAN => 0.15,
            Rank::KING => 0.25,
            Rank::DEITY => 0.40,
            Rank::TRIAL_MODERATOR => 0.05,
            Rank::MODERATOR => 0.10,
            Rank::SENIOR_MODERATOR => 0.15,
            Rank::ADMIN => 0.20,
            Rank::SENIOR_ADMIN => 0.25,
            Rank::MANAGER => 0.30,
            Rank::OWNER => 0.35,
            Rank::YOUTUBER => 0.15,
            Rank::FAMOUS => 0.35,
        ];
        if ($sender->getDataSession()->getFaction() !== null) { 
            $file = Nexus::getInstance()->getPlayerManager()->getCombatHandler()->getOutpostData();
            $actual = $file->get("actual", null);
            if($sender->getDataSession()->getFaction()->getName() === $actual) {
                $boost += 0.5;
            }
        }
        $rankIdentifier = $sender->getDataSession()->getRank()->getIdentifier();
        if (isset($rankBoosts[$rankIdentifier])) {
            $boost += $rankBoosts[$rankIdentifier];
        }
        foreach($entries as $entry) {
            $item = $items[$entry->getName()];
            $price += (int)($item->getCount() * $entry->getSellPrice() * $boost);
            $inventory->removeItem($item);
            $event = new ItemSellEvent($sender, $item, $price);
            $event->call();
            if($sender->isAutoSelling()) {
                continue;
            }
            $sender->sendMessage(Translation::getMessage("sell", [
                "amount" => TextFormat::GREEN . number_format($item->getCount()),
                "item" => TextFormat::DARK_GREEN . $entry->getName(),
                "price" => TextFormat::LIGHT_PURPLE . "$" . number_format((int)$item->getCount() * $entry->getSellPrice()) . " (" . "x" . $boost . ")",
            ]));
        }
        if(!$sender->isAutoSelling()) {
            $sender->sendMessage(TextFormat::YELLOW . TextFormat::BOLD. "TIP: " . TextFormat::RESET . TextFormat::YELLOW . "Use /sa as a shortcut to sell all your items.");
        }
        $sender->getDataSession()->addToBalance($price);
        return;
    }
}