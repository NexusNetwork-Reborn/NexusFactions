<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Combat;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Combat\Boss\Task\SpawnBossTask;
use Xekvern\Core\Player\Combat\Boss\Types\Alien;
use Xekvern\Core\Player\Combat\Boss\Types\CorruptedKing;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Entity\Utils\IEManager;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\WorldCreationOptions;
use Xekvern\Core\Player\Combat\Boss\Types\Minion;
use Xekvern\Core\Player\Combat\Boss\Types\WitherMinion;
use Xekvern\Core\Player\Combat\Koth\KOTHArena;
use Xekvern\Core\Player\Combat\Outpost\OutpostArena;
use Xekvern\Core\Player\Combat\Outpost\Task\OutpostHeartbeatTask;
use Xekvern\Core\Translation\Translation;

class CombatHandler
{

    /** @var Nexus */
    private $core;

    /** @var CombatEvents */
    private $listener;

    /** @var null|KOTHArena */
    private $kothGame = null;

    /** @var KOTHArena */
    private $kothArena;

    /** @var OutpostArena */
    private $outpostArena;

    /** @var int[] */
    private $setBountyCooldown = [];

    /**
     * CombatHandler constructor.
     *
     * @param Nexus $core
     *
     * @throws BossException
     * @throws KOTHException
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
        $this->listener = new CombatEvents($core);
        $core->getServer()->getPluginManager()->registerEvents($this->listener, $core);
        //$core->getScheduler()->scheduleRepeatingTask(new SpawnBossTask(), 20);
        $this->init();
        $this->outpostArena = new OutpostArena($core);
        $this->core->getScheduler()->scheduleRepeatingTask(new OutpostHeartbeatTask($this), 20);
        $this->kothArena = new KOTHArena("KOTH", new Position(978, 5, 285, $this->core->getServer()->getWorldManager()->getWorldByName("warzone")), new Position(323, 255,  289, $this->core->getServer()->getWorldManager()->getWorldByName("warzone")), 300);
    }

    /**
     * @throws BossException
     * @throws KOTHException
     */
    public function init(): void
    {
        $entityFactory = EntityFactory::getInstance();
        $entityFactory->register(Alien::class, function (World $world, CompoundTag $nbt): Alien {
            $manager = new IEManager(Nexus::getInstance(), "alien.png");
            $skin = $manager->skin;
            return new Alien(EntityDataHelper::parseLocation($nbt, $world), $skin);
        }, ["Alien"]);
        $entityFactory->register(CorruptedKing::class, function (World $world, CompoundTag $nbt): CorruptedKing {
            $manager = new IEManager(Nexus::getInstance(), "corruptedKing.png");
            $skin = $manager->skin;
            return new CorruptedKing(EntityDataHelper::parseLocation($nbt, $world), $skin);
        }, ["CorruptedKing"]);
        $entityFactory->register(Minion::class, function (World $world, CompoundTag $nbt): Minion {
            $manager = new IEManager(Nexus::getInstance(), "minion.png");
            $skin = $manager->skin;
            return new Minion(EntityDataHelper::parseLocation($nbt, $world), $skin);
        }, ["Minion"]);
        $entityFactory->register(WitherMinion::class, function (World $world, CompoundTag $nbt): WitherMinion {
            return new WitherMinion(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["WitherMinion"]);
        if ($this->core->getServer()->getWorldManager()->getWorldByName("warzone") === null) {
            $this->core->getServer()->getWorldManager()->generateWorld("warzone", new WorldCreationOptions());
            echo "World PVP has not been found, creating new base world \n";
        }
    }

    /**
     * @return OutpostArena
     */
    public function getOutpostArena(): OutpostArena
    {
        return $this->outpostArena;
    }

    /**
     * @return Config
     */
    public function getOutpostData(): Config
    {
        return new Config(Nexus::getInstance()->getDataFolder() . "outpost.json", Config::JSON);
    }

    /**
     * @return KOTHArena|null
     */
    public function getKOTHGame(): ?KOTHArena
    {
        return $this->kothGame;
    }

    public function initiateKOTHGame(): void
    {
        $this->kothGame = $this->kothArena;
    }

    /**
     * @throws TranslatonException
     */
    public function startKOTHGame(): void
    {
        if ($this->kothGame !== null) {
            $this->kothGame->setStarted(true);
        }
        $this->core->getServer()->broadcastMessage(TextFormat::DARK_BLUE . "[King of The Hill] " . TextFormat::RESET . TextFormat::YELLOW . "A KOTH Event has commenced! Use the command /pvp or /koth to get there!");
    }

    public function endKOTHGame(): void
    {
        $this->kothGame = null;
    }

    /**
     * @param NexusPlayer $player
     *
     * @return int
     */
    public function getSetBountyCooldownTime(NexusPlayer $player): int
    {
        if (!isset($this->setBountyCooldown[$player->getUniqueId()->toString()])) {
            $this->setBountyCooldown[$player->getUniqueId()->toString()] = time() - 60;
        }
        return $this->setBountyCooldown[$player->getUniqueId()->toString()];
    }

    /**
     * @param NexusPlayer $player
     */
    public function setSetBountyCooldownTime(NexusPlayer $player): void
    {
        $this->setBountyCooldown[$player->getUniqueId()->toString()] = time();
    }

    /**
     * @param NexusPlayer $player
     *
     * @return int
     */
    public function getGodAppleCooldown(NexusPlayer $player): int
    {
        $cd = -1;
        if (isset($this->listener->godAppleCooldown[$player->getUniqueId()->toString()])) {
            if ((90 - (time() - $this->listener->godAppleCooldown[$player->getUniqueId()->toString()])) >= 0) {
                $cd = 90 - (time() - $this->listener->godAppleCooldown[$player->getUniqueId()->toString()]);
            }
        }
        return $cd;
    }

    /**
     * @param NexusPlayer $player
     *
     * @return int
     */
    public function getGoldenAppleCooldown(NexusPlayer $player): int
    {
        $cd = -1;
        if (isset($this->listener->goldenAppleCooldown[$player->getUniqueId()->toString()])) {
            if ((8 - (time() - $this->listener->goldenAppleCooldown[$player->getUniqueId()->toString()])) >= 0) {
                $cd = 8 - (time() - $this->listener->goldenAppleCooldown[$player->getUniqueId()->toString()]);
            }
        }
        return $cd;
    }

    /**
     * @param NexusPlayer $player
     *
     * @return int
     */
    public function getEnderPearlCooldown(NexusPlayer $player): int
    {
        $cd = -1;
        if (isset($this->listener->enderPearlCooldown[$player->getUniqueId()->toString()])) {
            if ((16 - (time() - $this->listener->enderPearlCooldown[$player->getUniqueId()->toString()])) >= 0) {
                $cd = 16 - (time() - $this->listener->enderPearlCooldown[$player->getUniqueId()->toString()]);
            }
        }
        return $cd;
    }

    /**
     * @param NexusPlayer $player
     * @param int $cooldown
     */
    public function setEnderPearlCooldown(NexusPlayer $player, int $cooldown = 16)
    {
        $this->listener->enderPearlCooldown[$player->getUniqueId()->toString()] = time() - (16 - $cooldown);
    }

    public function spawnBoss(string $type, Location $location)
    {
        switch ($type) {
            case "Alien":
                $manager = new IEManager(Nexus::getInstance(), "alien.png");
                $skin = $manager->skin;
                $entity = new Alien($location, $skin);
                $entity->spawnToAll();
                //Server::getInstance()->broadcastMessage(TextFormat::BOLD . TextFormat::RED . "(!) " . TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "Alien " . TextFormat::RESET . TextFormat::GRAY . "has arrived at " . TextFormat::RED . "/boss");
                break;
            case "CorruptedKing":
                $manager = new IEManager(Nexus::getInstance(), "corruptedKing.png");
                $skin = $manager->skin;
                $entity = new CorruptedKing($location, $skin);
                $entity->spawnToAll();
                break;
        }
    }
}
