<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Entity;

use pocketmine\entity\Entity;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Entity\Types\Creeper;
use Xekvern\Core\Server\Entity\Types\Lightning;
use Xekvern\Core\Server\Entity\Types\PrimedTNT;
use Xekvern\Core\Server\Entity\Types\MessageEntity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use RuntimeException;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Entity\Types\Spawner\Blaze;
use Xekvern\Core\Server\Entity\Types\Spawner\Cow;
use Xekvern\Core\Server\Entity\Types\Spawner\IronGolem;
use Xekvern\Core\Server\Entity\Types\Spawner\Pig;
use Xekvern\Core\Server\Entity\Types\Spawner\Squid;
use Xekvern\Core\Server\Entity\Types\Spawner\Zombie;
use Xekvern\Core\Server\Entity\Types\SpawnerEntity;
use Xekvern\Core\Server\Entity\Utils\SpawnerNames;
use Xekvern\Core\Server\World\Utils\SpawnersItems;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class EntityHandler
{

    /** @var Nexus */
    private $core;

	private array $entities = [
		Blaze::class,
		Cow::class,
		IronGolem::class,
		Zombie::class,
		Pig::class,
		Squid::class,
		Zombie::class,
	];

    const LEGACY_ID_MAP = [
        EntityIds::BLAZE => 43,
        EntityIds::COW => 11,
        EntityIds::IRON_GOLEM => 20,
		EntityIds::ZOMBIE => 32,
        EntityIds::PIG => 12,
        EntityIds::SQUID => 17,
        EntityIds::CREEPER => 33,
    ];
    
	private array $entityIds = [];

    /**
     * EntityHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
        $core->getServer()->getPluginManager()->registerEvents(new EntityEvents($core), $core);
        $this->init();
    }

    public function init()
    {
        $entityFactory = EntityFactory::getInstance();
        $entityFactory->register(PrimedTNT::class, function (World $world, CompoundTag $nbt): PrimedTNT {
            return new PrimedTNT(EntityDataHelper::parseLocation($nbt, $world));
        }, ["PrimedTnt"]);
        $entityFactory->register(Creeper::class, function (World $world, CompoundTag $nbt): Creeper {
            return new Creeper(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["Creeper"]);
        $entityFactory->register(Lightning::class, function (World $world, CompoundTag $nbt): Lightning {
            return new Lightning(EntityDataHelper::parseLocation($nbt, $world));
        }, ["Lightning"]);
        $entityFactory->register(MessageEntity::class, function (World $world, CompoundTag $nbt): MessageEntity {
            return new MessageEntity(EntityDataHelper::parseLocation($nbt, $world));
        }, ["MessageEntity"]);
        foreach($this->entities as $entity) {
			EntityFactory::getInstance()->register($entity, function(World $world, CompoundTag $nbt) use($entity): SpawnerEntity {
				return new $entity(EntityDataHelper::parseLocation($nbt, $world), $nbt);
			}, [$entity::getNetworkTypeId()]);

			$this->entityIds[$entity::getNetworkTypeId()] = $entity;
		}
    }

	public function getEntityFor(string $entityTypeId, Location $location, $nbt) : SpawnerEntity {
		switch($entityTypeId) {
			case Blaze::getNetworkTypeId():
				return new Blaze($location, $nbt);
			case Cow::getNetworkTypeId():
				return new Cow($location, $nbt);
			case IronGolem::getNetworkTypeId():
				return new IronGolem($location, $nbt);
			case Pig::getNetworkTypeId():
				return new Pig($location, $nbt);
			case Squid::getNetworkTypeId():
				return new Squid($location, $nbt);
			case Zombie::getNetworkTypeId():
				return new Zombie($location, $nbt);
			default:
				throw new RuntimeException("Error at EntityHandler getEntityFor Func");
		}
	}

	public static function playSound(Entity $player, string $sound, $volume = 1, $pitch = 1, int $radius = 5): void {
		foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $p) {
			if ($p instanceof NexusPlayer) {
				if ($p->isOnline()) {
					$spk = new PlaySoundPacket();
					$spk->soundName = $sound;
					$spk->x = $p->getLocation()->getX();
					$spk->y = $p->getLocation()->getY();
					$spk->z = $p->getLocation()->getZ();
					$spk->volume = $volume;
					$spk->pitch = $pitch;
					$p->getNetworkSession()->sendDataPacket($spk);
				}
			}
		}
	}
}