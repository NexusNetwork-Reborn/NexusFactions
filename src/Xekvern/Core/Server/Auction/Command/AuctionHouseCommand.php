<?php

namespace Xekvern\Core\Server\Auction\Command;

use Xekvern\Core\Server\Auction\AuctionEntry;
use Xekvern\Core\Server\Auction\Inventory\AuctionPageInventory;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Auction\Inventory\AuctionEntryInventory;

class AuctionHouseCommand extends Command
{

    /**
     * AuctionHouseCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("auctionhouse", "Open auction house menu", "/ah sell <price: int> [amount: int]", ["ah"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof NexusPlayer) {
            if (isset($args[0])) {
                if ($args[0] === "sell") {
                    $handler = Nexus::getInstance()->getServerManager()->getAuctionHandler();
                    $item = $sender->getInventory()->getItemInHand();
                    if (!isset($args[1])) {
                        $sender->sendMessage(Translation::getMessage("usageMessage", [
                            "usage" => $this->getUsage()
                        ]));
                        return;
                    }
                    $buyPrice = (int)$args[1];
                    if ((!is_numeric($args[1])) or $args[1] <= 0) {
                        $sender->sendMessage(Translation::getMessage("invalidAmount"));
                        $sender->sendMessage(Translation::getMessage("usageMessage", [
                            "usage" => $this->getUsage()
                        ]));
                        return;
                    }
                    if ($item->isNull()) {
                        $sender->sendMessage(Translation::getMessage("invalidItem"));
                        return;
                    }
                    $sender->getInventory()->setItemInHand(VanillaItems::AIR());
                    (new AuctionEntryInventory($item, $buyPrice))->send($sender);
                   // $sender->getInventory()->setItemInHand(VanillaItems::AIR());
                   // $handler->addAuction(new AuctionEntry($item, $item->getCount(), $sender->getName(), $this->getCore()->getServerManager()->getAuctionHandler()->getNewIdentifier(), time(), $buyPrice));
                    //$name = $item->hasCustomName() ? $item->getCustomName() : $item->getName();
                    //$name .= TextFormat::RESET . TextFormat::GRAY . " * " . TextFormat::WHITE . $item->getCount();
                    //$sender->sendMessage(Translation::getMessage("addAuctionEntry", [
                    //    "item" => $name,
                    //    "price" => TextFormat::LIGHT_PURPLE . "$" . number_format($buyPrice)
                    //]));
                    return;
                }
            }
            $inventory = new AuctionPageInventory();
            $inventory->send($sender);
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}