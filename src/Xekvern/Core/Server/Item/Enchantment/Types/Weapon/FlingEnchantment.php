<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\EffectIds;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Translation\Translation;

class FlingEnchantment extends Enchantment
{

    /**
     * FlingEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Fling", Rarity::RARE, "Have a chance to send someone in the air and have a higher chance to do so depending on the level of the enchant.", self::DAMAGE, ItemFlags::SWORD, 5);
        $this->callable = function (EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if ((!$damager instanceof NexusPlayer) or (!$entity instanceof NexusPlayer))  {
                return;
            }
            $random = mt_rand(1, 400);
			$chance = $level * 2;
            if($chance >= $random) {
				$entity->setMotion($entity->getMotion()->add(0, 1 + (0.1 * $level), 0));
                $entity->sendTitle(TextFormat::BOLD . TextFormat::RED . "FLUNG!");
                $entity->getEffects()->add(new EffectInstance(EffectIdMap::getInstance()->fromId(EffectIds::BLINDNESS), $level * 10, 1));
                $damager->sendMessage(Translation::GREEN . "Fling " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}