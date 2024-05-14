<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Entity\Types\Spawner;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Xekvern\Core\Server\Entity\Types\SpawnerEntity;

class Cow extends SpawnerEntity
{
	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(1.3, 0.9);
	}

	public static function getNetworkTypeId(): string
	{
		return EntityIds::COW;
	}


	public function getName(): string
	{
		return "Cow";
	}

	public function getXpDropAmount(): int
	{
		return 5;
	}

	public function getDrops(): array
	{
		return [
			VanillaItems::RAW_BEEF()->setCount(mt_rand(1, 3)),
			VanillaItems::LEATHER()->setCount(mt_rand(0, 2)),
		];
	}
}
