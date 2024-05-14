<?php

declare(strict_types=1);

namespace Xekvern\Core\Player;

use muqsit\invmenu\InvMenu;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\block\BlockLegacyMetadata;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use Xekvern\Core\Command\Forms\StaffTeleportForm;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Provider\Event\PlayerLoadEvent;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Translation\Translation;

class PlayerEvents implements Listener
{

    /** @var Nexus */
    private $core;

    /** @var int[] */
    private $chat = [];

    /** @var int[] */
    private $command = [];

    /** @var string[][] */
    private $oldBlocks = [];

    /** @var int[] */
    protected $times = [];

    /** @var int[] */
    protected $lastMoved = [];
    /**
     * PlayerEvents constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
    }

    /**
     * @priority NORMAL
     * @param PlayerCreationEvent $event
     */
    public function onPlayerCreation(PlayerCreationEvent $event): void
    {
        $event->setPlayerClass(NexusPlayer::class);
    }

    /**
     * @param PlayerLoadEvent $event
     *
     * @throws NexusException
     */
    public function onPlayerLoad(PlayerLoadEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $this->times[$event->getPlayer()->getUniqueId()->toString()] = time();
        $world = $this->core->getServer()->getWorldManager()->getDefaultWorld();
        //if (date("l") !== "Friday") {
            //$player->addFloatingText(new Position(121.2542, 107.9198, -49.7925, $world), "Mystery", "§c§lVoldemort is currently away\n \n§r§fUnfortunately, he won't be back until Friday\nHe will be happy to hear from you if you bring §l§3Souls§r§f when he is back!");
        //}
        $info = implode(TextFormat::RESET . "\n", [
            TextFormat::BOLD . TextFormat::AQUA . "NeXus" . TextFormat::DARK_AQUA . "PE " . TextFormat::RESET . TextFormat::GRAY . "OP Factions",
            " ",
            TextFormat::WHITE . "Welcome to " . TextFormat::BOLD . TextFormat::RED . "Season " . ItemHandler::getRomanNumber(Nexus::SEASON) . TextFormat::RESET . TextFormat::WHITE . " of OP Factions",
            " ",
            TextFormat::WHITE . "Start your amazing adventures by joining a faction or",
            TextFormat::WHITE . "form your own using " . TextFormat::BOLD . TextFormat::AQUA . "/f create",
            " ",
            TextFormat::WHITE . "Use your Once Kit by using the command " . TextFormat::BOLD . TextFormat::AQUA . "/kit",
            TextFormat::WHITE . "and do " . TextFormat::BOLD . TextFormat::AQUA . "/wild" . TextFormat::RESET . TextFormat::WHITE . " to get to the wilderness.",
        ]);
        $mapInfo = implode(TextFormat::RESET . "\n", [
            TextFormat::BOLD . TextFormat::AQUA . "Map Information",
            " ",
            TextFormat::AQUA . "Faction Size: " . TextFormat::WHITE . "20",
            TextFormat::AQUA . "Maximum Allies: " . TextFormat::WHITE . "1",
            TextFormat::AQUA . "Maximum Border: " . TextFormat::WHITE . "15,000 x 15,000",
        ]);
        $linksInfo = implode(TextFormat::RESET . "\n", [
            TextFormat::BOLD . TextFormat::AQUA . "Server Links",
            " ",
            TextFormat::AQUA . "Store: " . TextFormat::WHITE . "store.nexuspe.net",
            TextFormat::AQUA . "Vote: " . TextFormat::WHITE . "vote.nexuspe.net",
            TextFormat::AQUA . "Discord: " . TextFormat::WHITE . "discord.nexuspe.net",
        ]);
        $topRewards = implode(TextFormat::RESET . "\n", [
            TextFormat::BOLD . TextFormat::DARK_RED . "TOP FACTION REWARDS",
            " ",    
            TextFormat::YELLOW . "#1 " . TextFormat::WHITE . "$50 Via PayPal & $100 Buycraft",
            TextFormat::YELLOW . "#2 " . TextFormat::WHITE . "$30 Via PayPal & $80 Buycraft",
            TextFormat::YELLOW . "#3 " . TextFormat::WHITE . "$15 Via PayPal & $30 Buycraft",
            " ",
            TextFormat::RESET . TextFormat::RED . "(Rewards are based on the amount of STR)",
            TextFormat::RESET . TextFormat::GRAY . "Rewards are handed out at discord.nexuspe.net",
        ]);
        $player->addFloatingText(new Position(-5.4952, 57.3568, -258.4813, $world), "Info", $info);
        //$player->addFloatingText(new Position(-40.4951, 50.344, -262.495, $world), "PVP", TextFormat::BOLD . TextFormat::RED . "WARZONE" . TextFormat::RESET . TextFormat::RED . "\nPvP is enabled below.");
        $player->addFloatingText(new Position(-40.4951, 50.344, -262.495, $world), "Spawner", TextFormat::BOLD . TextFormat::YELLOW . "Public Grinder" . TextFormat::RESET . TextFormat::WHITE . "\nMakes good money and EXP!");
        $player->addFloatingText(new Position(15.9103, 57.1746, -241.0098, $world), "Rewards", $topRewards);
        //$player->addFloatingText(new Position(21.5226, 128.4148, -203.5076, $world), "MapInfo", $mapInfo);
        //$player->addFloatingText(new Position(7.4743, 128.4148, -203.5523, $world), "LinksInfo", $linksInfo);
        $month = date("F", time());
        $customName = TextFormat::RESET . TextFormat::OBFUSCATED . TextFormat::BOLD . TextFormat::RED . "|" . TextFormat::GOLD . "|" . TextFormat::YELLOW . "|" . TextFormat::GREEN . "|" . TextFormat::AQUA . "|" . TextFormat::LIGHT_PURPLE . "|" . TextFormat::RESET . TextFormat::WHITE . TextFormat::BOLD . " $month Crate";
        $player->addFloatingText(new Position(3.5061, 131.274, -168.4577, $world), "MonthlyCrate", $customName . "\n" . TextFormat::RESET . TextFormat::RED . "Purchase @ store.nexuspe.net");
    }

    /**
     * @priority HIGHEST
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $event->setJoinMessage("");
        
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }

        $player->setFlying(false);
        $player->setAllowFlight(false);

        $currentXp = $player->getXpManager()->getCurrentTotalXp();
        $maxXp = 0x7fffffff;
        $minXp = -0x80000000;
        $player->getXpManager()->setCurrentTotalXp(max($minXp, min($currentXp, $maxXp)));

        $osMapping = [
            1 => "Android", 2 => "iOS", 3 => "Mac", 4 => "Amazon", 5 => "GearVR",
            6 => "Hololens", 7 => "Windows 10", 8 => "Windows 32", 9 => "Dedicated",
            10 => "TVOS", 11 => "PS4", 12 => "Nintendo", 13 => "Xbox", 14 => "Windows Phone"
        ];
        $os = $osMapping[$player->getPlayerInfo()->getExtraData()["DeviceOS"]] ?? "Unknown";
        $player->setOS($os);

        $hp = round($player->getHealth(), 1);   
        $player->setScoreTag(TextFormat::WHITE . $hp . TextFormat::RED . TextFormat::BOLD . " HP" . TextFormat::RESET . TextFormat::DARK_GRAY . " | " . TextFormat::GRAY . $player->getOS());
        
        $player->sendTitle(TextFormat::BOLD . TextFormat::GREEN . "Loading...", TextFormat::GRAY . "Fetching your data right now!", 20, 600, 20);
        $this->core->getMySQLProvider()->getLoadQueue()->addToQueue($player);
    }

    /**
     * @priority NORMAL
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $event->setQuitMessage("");
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }

        $uuid = $player->getUniqueId()->toString();
        if (isset($this->lastMoved[$uuid])) {
            $diff = time() - $this->lastMoved[$uuid];
            if ($diff >= 300) {
                $this->times[$uuid] = ($this->times[$uuid] ?? time()) + $diff;
            }
            unset($this->lastMoved[$uuid]);
        }

        if (isset($this->times[$uuid])) {
            $old = $player->getDataSession()->getOnlineTime();
            $player->getDataSession()->setOnlineTime($old + (time() - $this->times[$uuid]));
            unset($this->times[$uuid]);
        }

        if ($player->isLoaded()) {
            $session = $player->getDataSession();
            $session->saveData();
            $this->core->getSessionManager()->setCESession($player->getCESession());
        }

        if ($player->isInStaffMode()) {
            foreach ($player->getStaffModeInventory() as $item) {
                $player->getDataSession()->addToInbox($item);
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param PlayerExperienceChangeEvent $event
     */
    public function onPlayerExperienceChange(PlayerExperienceChangeEvent $event): void
    {
        $player = $event->getEntity();
        if (!($player instanceof NexusPlayer)) {
            return;
        }

        $currentTotalXp = $player->getXpManager()->getCurrentTotalXp();
        $maxInt32 = 0x7fffffff;
        $minInt32 = -0x80000000;

        if ($currentTotalXp > $maxInt32 || $currentTotalXp < $minInt32) {
            $event->cancel();
        }
    }

    /**
     * @priority HIGHEST
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $uuid = $player->getUniqueId()->toString();
        $to = $event->getTo();
        $from = $event->getFrom();
        $world = $player->getWorld();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if (!$player->isLoaded()) {
            $event->cancel();
            return;
        }
        if ($to->getYaw() !== $from->getYaw() or $to->getPitch() !== $from->getPitch()) {
            if (isset($this->lastMoved[$uuid]) and isset($this->times[$uuid])) {
                $diff = (time() - $this->lastMoved[$uuid]) - 300;
                if ($diff >= 300) {
                    $this->times[$uuid] = $this->times[$uuid] + $diff;
                } else {
                    $this->lastMoved[$uuid] = time();
                }
            }
            $this->lastMoved[$uuid] = time();
        }
        $x = abs($player->getPosition()->getFloorX());
        $y = abs($player->getPosition()->getFloorY());
        $z = abs($player->getPosition()->getFloorZ());
        if ($world->getDisplayName() === "warzone") {
            $max = Nexus::WARZONE_BORDER;
        } else {
            $max = Nexus::WILDERNESS_BORDER;
        }
        $minX = -$max;
        $maxX = $max;
        $minZ = -$max;
        $maxZ = $max;
        if ($x >= $maxX and $z >= $maxZ) {
            $player->teleport(new Position($maxX - 1, $player->getPosition()->getY(), $maxZ - 1, $player->getWorld()));
        } elseif ($x <= $minX and $z <= $minZ) {
            $player->teleport(new Position($minX + 1, $player->getPosition()->getY(), $minZ + 1, $player->getWorld()));
        } elseif ($x >= $maxX) {
            $player->teleport(new Position($maxX - 1, $player->getPosition()->getY(), $z, $player->getWorld()));
        } elseif ($z >= $maxZ) {
            $player->teleport(new Position($x, $player->getPosition()->getY(), $maxZ - 1, $player->getWorld()));
        } elseif ($x <= $minX) {
            $player->teleport(new Position($minX + 1, $player->getPosition()->getY(), $z, $player->getWorld()));
        } elseif ($z <= $minZ) {
            $player->teleport(new Position($x, $player->getPosition()->getY(), $minZ + 1, $player->getWorld()));
        }
        if ($to->getFloorY() > 256) {
            return;
        }
        if ($to->getFloorY() <= 0) {
            return;
        }
        if (($x > ($maxX - 5) or $x < ($minX + 5) or $z > ($maxZ - 5) or $z < ($minZ + 5)) and (!$from->floor()->equals($to->floor()))) {
            $this->updateBorders($player, $to, $max);
        }
    }

    /**
     * @param NexusPlayer $player
     * @param Position $newPosition
     */
    public function updateBorders(NexusPlayer $player, Position $newPosition, int $max): void
    {
        $x = $newPosition->getFloorX();
        $y = $newPosition->getFloorY();
        $z = $newPosition->getFloorZ();
        $world = $player->getWorld();
        if ($world === null) {
            return;
        }
        $oldBlocks = [];
        if (isset($this->oldBlocks[$player->getUniqueId()->toString()])) {
            $oldBlocks = $this->oldBlocks[$player->getUniqueId()->toString()];
        }
        $this->oldBlocks[$player->getUniqueId()->toString()] = [];
        $blocks = [];
        $minX = -$max;
        $maxX = $max;
        $minZ = -$max;
        $maxZ = $max;
        $border = $maxX;
        if ($x > ($maxX - 5) or $x < ($minX + 5)) {
            if ($x < 0) {
                $border = $minX;
            }
            for ($i = $y - 1; $i <= $y + 2; $i++) {
                for ($j = $z - 2; $j <= $z + 2; $j++) {
                    if ($i >= 256) {
                        break;
                    }
                    $vector = new Vector3($border, $i, $j);
                    if (($border < 0 and $j < $border) or ($border > 0 and $j > $border)) {
                        continue;
                    }
                    if ($world->getBlock($vector)->isSolid()) {
                        continue;
                    }
                    if (isset($blocks[World::blockHash($border, $i, $j)])) {
                        continue;
                    }
                    $blocks[World::blockHash($border, $i, $j)] = VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::RED());
                }
            }
        }
        $border = $maxZ;
        if ($z > ($maxZ - 5) or $z < ($minZ + 5)) {
            if ($z < 0) {
                $border = $minZ;
            }
            for ($i = $y - 1; $i <= $y + 2; $i++) {
                for ($j = $x - 2; $j <= $x + 2; $j++) {
                    if ($i >= 256) {
                        break;
                    }
                    $vector = new Vector3($j, $i, $border);
                    if (($border < 0 and $j < $border) or ($border > 0 and $j > $border)) {
                        continue;
                    }
                    if ($world->getBlock($vector)->isSolid()) {
                        continue;
                    }
                    if (isset($blocks[World::blockHash($j, $i, $border)])) {
                        continue;
                    }
                    $blocks[World::blockHash($j, $i, $border)] = VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::RED());
                }
            }
        }
        $this->oldBlocks[$player->getUniqueId()->toString()] = array_merge($this->oldBlocks[$player->getUniqueId()->toString()], array_keys($blocks));
        foreach ($oldBlocks as $hash) {
            if (!isset($blocks[$hash])) {
                $blocks[$hash] = VanillaBlocks::AIR();
            }
        }
        if (empty($blocks)) {
            return;
        }
        foreach ($blocks as $hash => $block) {
            World::getBlockXYZ($hash, $x, $y, $z);
            $packet = new UpdateBlockPacket();
            $packet->blockPosition = new BlockPosition($x, $y, $z);
            $packet->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($block->getStateId());
            $player->getNetworkSession()->sendDataPacket($packet);
        }
    }

    /**
     * @priority LOW
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if ($player->isInStaffMode()) {
            $event->cancel();
            return;
        }
        if ($player->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            return;
        }
        $block = $event->getTransaction();
        $world = $player->getWorld();
        if ($world === null) {
            return;
        }
        if ($world->getDisplayName() !== Faction::CLAIM_WORLD) {
            $event->cancel();
            return;
        }
        $x = abs($player->getPosition()->getFloorX());
        $z = abs($player->getPosition()->getFloorZ());
        if ($x >= Nexus::WILDERNESS_BORDER or $z >= Nexus::WILDERNESS_BORDER) {
            $event->cancel();
        }
    }

    /**
     * @priority LOW
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if ($player->isInStaffMode()) {
            $event->cancel();
            return;
        }
        if ($player->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            return;
        }
        $block = $event->getBlock();
        $world = $player->getWorld();
        if ($world === null) {
            return;
        }
        if ($world->getDisplayName() !== Faction::CLAIM_WORLD) {
            $event->cancel();
            return;
        }
        $x = abs($player->getPosition()->getFloorX());
        $z = abs($player->getPosition()->getFloorZ());
        if ($x >= Nexus::WILDERNESS_BORDER or $z >= Nexus::WILDERNESS_BORDER) {
            $event->cancel();
        }
    }

    /**
     * @param PlayerExhaustEvent $event
     */
    public function onPlayerExhaust(PlayerExhaustEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if (!$player->isTagged()) {
            $event->cancel();
            return;
        }
    }

    /**
     * @param InventoryTransactionEvent $event
     */
    public function onInvTransaction(InventoryTransactionEvent $event): void
    {
        $player = $event->getTransaction()->getSource();
        if ($player instanceof NexusPlayer) {
            if ($player->isInStaffMode()) {
                $event->cancel();
                return;
            }
        }
    }

    /**
     * @param PlayerDropItemEvent $event
     */
    public function onItemDrop(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player instanceof NexusPlayer) {
            if ($player->isInStaffMode()) {
                $event->cancel();
                return;
            }
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @handleCancelled 
     */
    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if ($player instanceof NexusPlayer) {
            if ($player->isInStaffMode()) {
                if ($block->getTypeId() === BlockTypeIds::CHEST) {
                    $event->cancel();
                    return;
                }
            }
        }
    }

    /**
     * @param EntityItemPickupEvent $event
     */
    public function onItemPickUp(EntityItemPickupEvent $event)
    {
        $player = $event->getEntity();
        if ($player instanceof NexusPlayer) {
            if ($player->isInStaffMode()) {
                $event->cancel();
                return;
            }
        }
    }

    /** 
     * @param BlockPlaceEvent $event
     */
    public function onPlaceHead(BlockPlaceEvent $event): void
    {
        if ($event->getItem()->getTypeId() == VanillaBlocks::MOB_HEAD()->getTypeId()) {
            $event->cancel();
        }
    }

    /**
     * @param CommandEvent $event
     */
    public function onCommandPreProcess(CommandEvent $event): void
    {
        $player = $event->getSender();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $commands = [
            "/f",
            "/tp",
            "/teleport"
        ];
        $announcementHandler = $this->core->getServerManager()->getAnnouncementHandler();
        if ($announcementHandler->getRestarter()->getRestartProgress() <= 5) {
            $event->cancel();
            $player->sendMessage(Translation::getMessage("restartingSoon"));
            return;
        }
        if (in_array(explode(" ", $event->getCommand())[0], $commands)) {
            if ($player->isInStaffMode()) {
                $player->sendMessage(TextFormat::BOLD . TextFormat::RED . "(WARNING) You are not allowed to use this command while in staff mode!");
                $player->playErrorSound();
                $event->cancel();
                return;
            }
        }
        if (!isset($this->command[$player->getUniqueId()->toString()])) {
            $this->command[$player->getUniqueId()->toString()] = time();
            return;
        }
        if (time() - $this->command[$player->getUniqueId()->toString()] >= 3) {
            $this->command[$player->getUniqueId()->toString()] = time();
            return;
        }
        $seconds = 3 - (time() - $this->command[$player->getUniqueId()->toString()]);
        $player->sendMessage(Translation::getMessage("actionCooldown", [
            "amount" => TextFormat::RED . $seconds
        ]));
        $event->cancel();
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onPlayerChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if (!$player->isLoaded()) {
            return;
        }
        if ($player->getDataSession()->getRank()->getIdentifier() >= Rank::SPARTAN) {
            return;
        }
        if ($player->getDataSession()->getRank()->getIdentifier() < Rank::SPARTAN) {
            if (!isset($this->chat[$player->getUniqueId()->getBytes()])) {
                $this->chat[$player->getUniqueId()->getBytes()] = time();
                return;
            }
            if (time() - $this->chat[$player->getUniqueId()->getBytes()] >= 3) {
                $this->chat[$player->getUniqueId()->getBytes()] = time();
                return;
            }
            $seconds = 3 - (time() - $this->chat[$player->getUniqueId()->getBytes()]);
            $player->sendMessage(Translation::getMessage("messageCooldown", [
                "amount" => TextFormat::RED . $seconds
            ]));
            $event->cancel();
        }
    }

    /**
     * @param PlayerItemUseEvent $event
     * @handleCancelled 
     */
    public function onItemUse(PlayerItemUseEvent $event)
    {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if ($player->isInStaffMode()) {
            $item = $event->getItem();
            switch ($item->getTypeId()) {
                case VanillaBlocks::ICE()->getTypeId():
                    $player->sendMessage(Translation::ORANGE . "You must tap a player with this item to freeze/unfreeze them!");
                    break;
                case ItemTypeIds::COMPASS:
                    $player->sendForm(new StaffTeleportForm($player));
                    break;
                case ItemTypeIds::CLOCK:
                    $event->cancel();
                    $randomPlayer = $this->core->getServer()->getOnlinePlayers()[array_rand($this->core->getServer()->getOnlinePlayers())];
                    if ($randomPlayer instanceof NexusPlayer) {
                        $player->teleport($randomPlayer->getPosition()->asPosition());
                    }
                    break;
                case VanillaBlocks::CHEST()->getTypeId():
                    $player->sendMessage(Translation::ORANGE . "Hit a player to see it's inventory.");
                    break;
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param EntityDamageEvent $event
     * @handleCancelled
     */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        if (!($entity instanceof NexusPlayer)) {
            return;
        }
        if ($event->getCause() === EntityDamageEvent::CAUSE_FALL && $entity->justLoaded()) {
            $event->cancel();
            return;
        }
        if ($entity->isInStaffMode()) {
            $event->cancel();
            return;
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if ($damager instanceof NexusPlayer && $damager->isInStaffMode()) {
                $event->cancel();
                switch ($damager->getInventory()->getItemInHand()->getTypeId()) {
                    case VanillaBlocks::ICE()->asItem()->getTypeId():
                        $entity->setNoClientPredictions(!$entity->hasNoClientPredictions());
                        $damager->sendMessage($entity->hasNoClientPredictions() ? Translation::GREEN . "You have frozen " . TextFormat::YELLOW . $entity->getName() . "" : Translation::ORANGE . "You have no longer set " . TextFormat::YELLOW . $entity->getName() . TextFormat::GRAY . " frozen!");
                        break;
                    case VanillaBlocks::CHEST()->asItem()->getTypeId():
                        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
                        $menu->setName($entity->getName() . " Inventory");
                        $menu->setListener($menu->readonly());
                        foreach ($entity->getInventory()->getContents() as $item) {
                            $menu->getInventory()->addItem($item);
                        }
                        $menu->send($damager);
                        break;
                }
            }
        }
    }
}
