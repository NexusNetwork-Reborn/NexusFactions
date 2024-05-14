<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Utils;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Opaque;
use pocketmine\block\UnknownBlock;
use pocketmine\utils\CloningRegistryTrait;

/**
 * @method static SpawnerBlock MONSTER_SPAWNER()
 * @method static UnknownElement UNKNOWN_ELEMENT()
 */
final class ExtraVanillaBlocks{
	use CloningRegistryTrait;

	private function __construct(){
		//NOOP
	}

	protected static function register(string $name, Block $block) : void{
		self::_registryRegister($name, $block);
	}

	/**
	 * @return Block[]
	 * @phpstan-return array<string, Block>
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var Block[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup() : void{
		//If you want, store this ID somewhere for later, but you can always get it by doing ExtraVanillaBlocks::TARGET()->getTypeId()
		$targetTypeId = BlockTypeIds::newId();
		self::register("target", new Opaque(new BlockIdentifier($targetTypeId), "Target", new BlockTypeInfo(BlockBreakInfo::instant())));
	}
}