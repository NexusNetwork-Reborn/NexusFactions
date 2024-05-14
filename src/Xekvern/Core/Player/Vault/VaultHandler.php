<?php

namespace Xekvern\Core\Player\Vault;

use muqsit\invmenu\inventory\InvMenuInventory;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use Xekvern\Core\Nexus;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\NBT;
use const ZLIB_ENCODING_GZIP;
use function zlib_encode;
use function zlib_decode;

class VaultHandler {

    /** @var Nexus */
    private $core;

    /** @var Vault[][] */
    private $vaults = [];

    /** @var BigEndianNbtSerializer */
    private static $nbtWriter;

	private const TAG_INVENTORY = "Inventory";

    /**
     * VaultHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        self::$nbtWriter = new BigEndianNbtSerializer();
    }

    /**
     * @param Vault $vault
     */
    public function addVault(Vault $vault) {
        $connector = $this->core->getMySQLProvider()->getConnector();
        $connector->executeUpdate("INSERT INTO vaults(username, id, items) VALUES(?, ?, ?)", "sis", [$vault->getOwner(), $vault->getId(), ""]);
        $this->vaults[strtolower($vault->getOwner())][$vault->getId()] = $vault;
        $this->vaults[strtolower($vault->getOwner())][$vault->getAlias()] = $vault;
    }

    /**
     * @param string $owner
     *
     * @return Vault[]
     */
    public function getVaultsFor(string $owner): array {
        if(!isset($this->vaults[strtolower($owner)])) {
            $database = $this->core->getMySQLProvider()->getDatabase();
            $stmt = $database->prepare("SELECT username, id, alias, items FROM vaults WHERE username = ?");
            $stmt->bind_param("s", $owner);
            $stmt->bind_result($name, $id, $alias, $items);
            $stmt->execute();
            while($stmt->fetch()) {
                if($name === null) {
                    return [];
                }
                $this->vaults[strtolower($owner)][$id] = new Vault($name, $id, $alias, $items);
            }
            $stmt->close();
        }
        return $this->vaults[strtolower($owner)] ?? [];
    }

    /**
     * @param string $owner
     * @param int $identifier
     *
     * @return Vault|null
     */
    public function getVault(string $owner, int $identifier): ?Vault {
        if(!isset($this->vaults[strtolower($owner)])) {
            $database = $this->core->getMySQLProvider()->getDatabase();
            $stmt = $database->prepare("SELECT username, id, alias, items FROM vaults WHERE username = ?");
            $stmt->bind_param("s", $owner);
            $stmt->bind_result($name, $id, $alias, $items);
            $stmt->execute();
            while($stmt->fetch()) {
                if($name === null) {
                    return null;
                }
                $this->vaults[strtolower($owner)][$alias] = new Vault($name, $id, $alias, $items);
                $this->vaults[strtolower($owner)][$id] = new Vault($name, $id, $alias, $items);
            }
            $stmt->close();
        }
        return $this->vaults[strtolower($owner)][$identifier] ?? null;
    }

    /**
     * @param string $owner
     * @param string $identifier
     *
     * @return Vault|null
     */
    public function getVaultByAlias(string $owner, string $identifier): ?Vault {
        if(!isset($this->vaults[strtolower($owner)])) {
            $database = $this->core->getMySQLProvider()->getDatabase();
            $stmt = $database->prepare("SELECT username, id, alias, items FROM vaults WHERE username = ?");
            $stmt->bind_param("s", $owner);
            $stmt->bind_result($name, $id, $alias, $items);
            $stmt->execute();
            while($stmt->fetch()) {
                if($name === null) {
                    return null;
                }
                $this->vaults[strtolower($owner)][$alias] = new Vault($name, $id, $alias, $items);
                $this->vaults[strtolower($owner)][$id] = new Vault($name, $id, $alias, $items);
            }
            $stmt->close();
        }
        return $this->vaults[strtolower($owner)][$identifier] ?? null;
    }

    /**
     * @param Inventory $inventory
     * 
     * @return string   
     * @internal
     */
    public function saveVault(Inventory $inventory): string {
        $contents = [];
        foreach($inventory->getContents() as $slot => $item) {
            $contents[] = $item->nbtSerialize($slot);
        }
		return self::$nbtWriter->write(new TreeRoot(CompoundTag::create()->setTag(self::TAG_INVENTORY, new ListTag($contents, NBT::TAG_Compound))));
    }

    /**
     * @param string $data
     * 
     * @return Item[] $contents
     * @internal
     */
    public function getVaultContents(string $data): array {
        $contents = [];
		$inventoryTag = self::$nbtWriter->read($data)->mustGetCompoundTag()->getListTag(self::TAG_INVENTORY);
		/** @var CompoundTag $tag */
		foreach($inventoryTag as $tag) {
			$contents[$tag->getByte("Slot")] = Item::nbtDeserialize($tag);
		}
        return $contents;
    }
}