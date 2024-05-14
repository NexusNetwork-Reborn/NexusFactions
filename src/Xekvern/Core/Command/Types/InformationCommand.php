<?php

declare(strict_types=1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class InformationCommand extends Command {

    /**
     * AddMoneyCommand constructor.
     */
    public function __construct() {
        parent::__construct("information", "Learn about how to play the server.", "/information <topic: string>", ["info"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!isset($args[0])) {
            $sender->sendMessage(TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "Server Information");
            $sender->sendMessage(TextFormat::RED . " /info essentials " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About how you can get started.");
            $sender->sendMessage(TextFormat::GOLD . " /info pass " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About our new pass system.");
            $sender->sendMessage(TextFormat::YELLOW . " /info levels " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About ranking up.");
            $sender->sendMessage(TextFormat::GREEN . " /info enchanting " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About enchanting.");
            $sender->sendMessage(TextFormat::BLUE . " /info sacredkits " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About sacred kits.");
            $sender->sendMessage(TextFormat::DARK_PURPLE . " /info features " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About what features we offer.");
            return;
        }
        switch($args[0]) {
            case "essentials":
                $sender->sendMessage(TextFormat::RED . TextFormat::BOLD . "Essentials Information");
                $sender->sendMessage(TextFormat::GRAY . "Welcome to NeXus OP Faction. You are probably wondering how you can become OP. You can start off by claiming the §4§lOnce §r§7Kit. It will include an OP kit with a §4§lCoal Mining Generator§r§7 for you to use. Start exploring by doing §e/wild§7 where you can discover and raid other people's bases and also create your own.");
                break;
            case "pass":
                $sender->sendMessage(TextFormat::GOLD . TextFormat::BOLD . "Pass Information");
                $sender->sendMessage(TextFormat::GRAY . "Passes is basically a §bquest§7 system. To manage your pass, do §e/pass§7. There are §c80 §7tiers that for you to complete. As you complete more tiers, it will continue to get harder and harder. The rewards will also get better. There are 2 different type of rewards. §aRegular§7 rewards and §dPremium§7 rewards. Regular rewards can be access by all players but Premium rewards can only be accessed if you have purchased the §6Premium Pass§7 on our buycraft store. If you complete all 80 tiers with the Premium Pass, you'll have a chance at obtaining a §l§cM§6O§eN§aT§bH§dL§5Y§r§7 crate!");
                break;
            case "levels":
                $sender->sendMessage(TextFormat::YELLOW . TextFormat::BOLD . "Level Information");
                $sender->sendMessage(TextFormat::GRAY . "In our server, you can obtain rewards through leveling up. You will need to level up to start unlockocking more features, shop items, and more offers from §l§3Voldemort§r§7! You can rank up by doing §e/rankup§7!");
                break;
            case "enchanting":
                $sender->sendMessage(TextFormat::GREEN . TextFormat::BOLD . "Enchanting Information");
                $sender->sendMessage(TextFormat::GRAY . "You're probably clueless about how to get enchantments on our server. You can get enchantments many of ways. The main way is to trade XP for enchantment books. You can do that by tapping an §denchanting table§7. You get one book per §a5000 XP§7. You apply an enchantment by dragging an item on top of a book. If you want to take off an enchantment, you can trade a book to the §2§lAlchemist§r§7 for an §5§lEnchantment Remover§r§7. You remove the enchantment by tapping the item you want enchantments removed against the Alchemist with an enchantment remover in your inventory. You'll have a chance to get it back as an §1§lEnchantment Crystal§r§7. Enchantment Crystal can set an enchantment to an item but it won't increase the level.");
                break;
            case "sacredkits":
                $sender->sendMessage(TextFormat::BLUE . TextFormat::BOLD . "Sacred Kits Information");
                $sender->sendMessage(TextFormat::GRAY . "Sacred Kits are OP kits that you can get through opening §e§lHoly Boxes§r§7. You can manage your kits by doing §e/skit§7. Holy Boxes are obtained through opening §c§lSacred Stones§r§7. Sacred Stones are obtained through §bmining stone§7 and also sacred alls that are automatically executed every 150 votes. Sacred Stones have a 1 in 5 chance of uncovering a holy box. Sacred Kits can also be leveled up. The max level of each Sacred Kit varies from 3 to 5.");
                break;
            case "features":
                $sender->sendMessage(TextFormat::DARK_PURPLE . TextFormat::BOLD . "Features Information");
                $sender->sendMessage(TextFormat::LIGHT_PURPLE . "Basic list of features you should know:");
                $sender->sendMessage(TextFormat::GRAY . "Bounty, Crates, Bosses, Coin-flipping, Lottery/Pot, Bosses, Auctions, Monthly Crates, Spawners, Mining and Auto Generators, KOTH, Envoy, Lucky Blocks, Trading, Vaults, and Anti-cheat");
                $sender->sendMessage(TextFormat::LIGHT_PURPLE . "Basic list of command you should know:");
                $sender->sendMessage(TextFormat::GRAY . "/crates, /pvp, /boss, /envoys, /inbox, /kit, /koth, /list, /lobby, /pass, /ping, /pvphud, /rankup, /shop, /repair, /rename, /rules, /sell, /skit, /tag, /trade, /trash, /vote, /wild, /withdraw, and /xyz");
                break;
            default:
                $sender->sendMessage(TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "Server Information");
                $sender->sendMessage(TextFormat::RED . " /info essentials " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About how you can get started.");
                $sender->sendMessage(TextFormat::GOLD . " /info pass " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About our new pass system.");
                $sender->sendMessage(TextFormat::YELLOW . " /info ranks " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About ranking up.");
                $sender->sendMessage(TextFormat::GREEN . " /info enchanting " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About enchanting.");
                $sender->sendMessage(TextFormat::BLUE . " /info sacredkits " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About sacred kits.");
                $sender->sendMessage(TextFormat::DARK_PURPLE . " /info features " . TextFormat::DARK_AQUA . "- " . TextFormat::GRAY . "About what features we offer.");
                break;
        }
    }
}
