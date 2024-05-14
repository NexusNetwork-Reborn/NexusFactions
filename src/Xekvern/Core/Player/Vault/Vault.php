<?php

namespace Xekvern\Core\Player\Vault;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Vault\Forms\VaultListForm;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\item\Item;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

class Vault {

    /** @var string */
    private $owner;

    /** @var InvMenu */
    private $menu;

    /** @var int */
    private $id;

    /** @var ?string` */
    private $alias;

    /**
     * Vault constructor.
     *
     * @param string $owner
     * @param int $id
     * @param string|null $alias
     * @param string $items
     */
    public function __construct(string $owner, int $id, ?string $alias = null, string $items = "") {
        $this->owner = $owner;
        $this->id = $id;
        $this->alias = $alias;
        $this->menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $this->menu->setName(TextFormat::YELLOW . "PV $id ($alias)");
        $this->menu->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            if($this->owner === $player->getName()) {
                return $transaction->continue();
            }
            if($player->hasPermission("permission.admin") or $player->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
                return $transaction->continue();
            }
            return $transaction->discard();
        });
        if(!empty($items)) {
            $contents = Nexus::getInstance()->getPlayerManager()->getVaultHandler()->getVaultContents($items);
            $this->menu->getInventory()->setContents($contents);
        }
        $this->menu->setInventoryCloseListener(function(NexusPlayer $player, InvMenuInventory $inventory) use($id): void {
            $items = Nexus::getInstance()->getPlayerManager()->getVaultHandler()->saveVault($inventory);
            $stmt = Nexus::getInstance()->getMySQLProvider()->getDatabase()->prepare("REPLACE INTO vaults(username, id, alias, items) VALUES(?, ?, ?, ?)");
            $stmt->bind_param("siss", $this->owner, $id, $this->alias, $items);
            $stmt->execute();
            $stmt->close();
        });
    }

    /**
     * @return string
     */
    public function getOwner(): string {
        return $this->owner;
    }

    /**
     * @return InvMenu
     */
    public function getMenu(): InvMenu {
        return $this->menu;
    }

    /**
     * @param string|null $alias
     */
    public function setAlias(?string $alias): void {
        $this->alias = $alias;
        $this->menu->setName(TextFormat::YELLOW . "PV $this->id ($alias)");
    }

    /**
     * @return string|null
     */
    public function getAlias(): ?string {
        return $this->alias;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }
}