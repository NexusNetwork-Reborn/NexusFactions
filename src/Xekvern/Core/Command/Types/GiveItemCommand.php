<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Server\Item\Types\CustomTag;
use Xekvern\Core\Server\Item\Types\Soul;
use Xekvern\Core\Server\Item\Types\HolyBox;
use Xekvern\Core\Server\Item\Types\KOTHStarter;
use Xekvern\Core\Server\Item\Types\MonthlyCrate;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Types\AttributeShard;
use Xekvern\Core\Server\Item\Types\EnchantmentScroll;
use Xekvern\Core\Server\Item\Types\Lootbox;
use Xekvern\Core\Server\Item\Types\SacredStone;
use Xekvern\Core\Server\Item\Types\SellWand;
use Xekvern\Core\Server\Item\Types\CosmeticShard;
use Xekvern\Core\Server\Item\Types\AbilityScroll;

class GiveItemCommand extends Command {

    const BOSSES = [
        "Alien",
        "CorruptedKing"
    ];

    /**
     * GiveItemCommand constructor.
     */
    public function __construct() {
        parent::__construct("giveitem", "Give item to a player.", "/giveitem <player:target> <item>");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof ConsoleCommandSender or $sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            if(!isset($args[1])) {
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
                return;
            }
            $player = $this->getCore()->getServer()->getPlayerByPrefix($args[0]);
            if(!$player instanceof NexusPlayer) {
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                return;
            }
            switch($args[1]) {
                case "holybox":
                    $kits = $this->getCore()->getServerManager()->getKitHandler()->getSacredKits();
                    $kit = $kits[array_rand($kits)];
                    if($kit === null) {
                        $sender->sendMessage(Translation::getMessage("invalidKit"));   
                    }
                    $player->getInventory()->addItem((new HolyBox($kit))->getItemForm());
                    break;
                case "soul":
                    if(!isset($args[2])) {
                        $amount = 1;
                    }
                    else {
                        $amount = is_numeric($args[2]) ? (int)$args[2] : 1;
                    }
                    $player->getInventory()->addItem((new Soul())->getItemForm()->setCount($amount));
                    break;
                case "abilityscroll":
                    if(!isset($args[2])) {
                        $amount = 1;
                    }
                    else {
                        $amount = is_numeric($args[2]) ? (int)$args[2] : 1;
                    }
                    $player->getInventory()->addItem((new AbilityScroll(random_int(0,4)))->getItemForm()->setCount($amount));
                    break;
                case "cosmeticshard":
                    if(!isset($args[2])) {
                        $amount = 1;
                    }
                    else {
                        $amount = is_numeric($args[2]) ? (int)$args[2] : 1;
                    }
                    $player->getInventory()->addItem((new CosmeticShard(random_int(0,1),random_int(0, 22)))->getItemForm()->setCount(1));
                    break;
                case "attributeshard":
                    if(!isset($args[2])) {
                        $amount = 1;
                    }
                    else {
                        $amount = is_numeric($args[2]) ? (int)$args[2] : 1;
                    }
                    $player->getInventory()->addItem((new AttributeShard(random_int(0,2)))->getItemForm()->setCount(1));
                    break;
                case "customTag":
                    if(!isset($args[2])) {
                        $amount = 1;
                    }
                    else {
                        $amount = is_numeric($args[2]) ? (int)$args[2] : 1;
                    }
                    $player->getInventory()->addItem((new CustomTag())->getItemForm()->setCount($amount));
                    break;
                case "scroll":
                    if(!isset($args[2])) {
                        $amount = 1;
                    }
                    else {
                        $amount = is_numeric($args[2]) ? (int)$args[2] : 1;
                    }
                    $player->getInventory()->addItem((new EnchantmentScroll())->getItemForm());
                    break;
                case "wand":
                    if(!isset($args[2])) {
                        $amount = 1;
                    }
                    else {
                        $amount = is_numeric($args[2]) ? (int)$args[2] : 1;
                    }
                    $player->getInventory()->addItem((new SellWand())->getItemForm());
                    break;
                case "kothstarter":
                    if(!isset($args[2])) {
                        $amount = 1;
                    }
                    else {
                        $amount = is_numeric($args[2]) ? (int)$args[2] : 1;
                    }
                    $player->getInventory()->addItem((new KOTHStarter())->getItemForm()->setCount($amount));
                    break;
                case "monthly":
                    $player->getInventory()->addItem((new MonthlyCrate())->getItemForm());
                    break;
                case "sacredstone":
                    $player->getInventory()->addItem((new SacredStone())->getItemForm());
                    break;
                case "sotwlootbox":
                    $player->getInventory()->addItem((new Lootbox("SOTW", TextFormat::BOLD . TextFormat::RED . "SOTW New Chapter"))->getItemForm());
                    break;
                case "husklootbox":
                    $player->getInventory()->addItem((new Lootbox("Husk", TextFormat::BOLD . TextFormat::DARK_GRAY. "Husky"))->getItemForm());
                    break;
                default:
                    $sender->sendMessage(Translation::getMessage("usageMessage", [
                        "usage" => $this->getUsage()
                    ]));

                    break;
            }
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}
