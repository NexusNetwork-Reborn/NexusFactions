<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\Block;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\World\Tile\Generator;
use Xekvern\Core\Utils\Utils;

class Recon extends ClickableItem {

    const RECON = "Recon";

    /**
     * SacredStone constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::DARK_RED . TextFormat::BOLD . "Recon";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Get information about the current claim";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "you are standing in.";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Cooldown: " . TextFormat::BOLD . TextFormat::RED . "N/A";
        parent::__construct(ExtraVanillaItems::MAP(), $customName, $lore, [], [
            self::RECON => new StringTag(self::RECON)
        ]);    
    }

    /**
     * @param NexusPlayer $player
     * @param Inventory $inventory
     * @param Item $item
     * @param CompoundTag $tag
     * @param int $face
     * @param Block $blockClicked
     *
     * @throws TranslatonException
     */
    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void {
        if(!$player->isLoaded()) {
            return;
        }
        $world = $player->getWorld();
        if($world === null) {
            return;
        }
        if($world->getFolderName() === "spawn" or $world->getFolderName() === "bossarena") {
            $player->sendMessage(Translation::getMessage("notInPvP"));
            return;
        }
        $chunk = $world->getOrLoadChunkAtPosition($player->getEyePos());
        if($chunk === null) {
            return;
        }
        $pFaction = $player->getDataSession()->getFaction();
        if($pFaction === null) {
            $player->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        $claim = Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getClaimInPosition($player->getPosition());
        if($claim === null) {
            $player->sendMessage(Translation::getMessage("mustBeInEnemyClaim"));
            return;
        }
        $faction = $claim->getFaction();
        if($faction->isInFaction($player->getName()) or $faction->isAlly($pFaction)) {
            $player->sendMessage(Translation::getMessage("mustBeInEnemyClaim"));
            return;
        }
        $spawners = 0;
        $generators = 0;
        foreach($chunk->getTiles() as $tile) {
            if($tile instanceof Generator) {
                $generators += $tile->getStack();
            }
        }
        $value = $claim->getValue();
        $player->sendMessage(Utils::createPrefix(TextFormat::DARK_RED, "Recon"));
        $player->sendMessage(TextFormat::GREEN . "  X: " . $claim->getChunkX());
        $player->sendMessage(TextFormat::GREEN . "  Y: " . $claim->getChunkZ());
        $player->sendMessage(TextFormat::GREEN . "  Faction: " . $claim->getFaction()->getName());
        $player->sendMessage(TextFormat::GREEN . "  Value: " . $value);
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }
}