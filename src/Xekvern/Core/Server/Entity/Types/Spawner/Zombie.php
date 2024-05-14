<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Entity\Types\Spawner;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Xekvern\Core\Server\Entity\Types\SpawnerEntity;

class Zombie extends SpawnerEntity
{
	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(1.8, 0.6);
	}

	public static function getNetworkTypeId(): string
	{
		return EntityIds::ZOMBIE;
	}


	public function getName(): string
	{
		return "Zombie";
	}

	public function getDrops(): array
	{
		return [
			VanillaItems::ROTTEN_FLESH()->setCount(mt_rand(1, 2)),
		];
	}

	public function getXpDropAmount(): int
	{
		return 6;
	}
}
