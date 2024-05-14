<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Mining;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;

class HasteEnchantment extends Enchantment
{

    /**
     * HasteEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Haste", Rarity::UNCOMMON, "Obtain haste.", self::BREAK, ItemFlags::DIG, 5);
        $this->callable = function (BlockBreakEvent $event, int $level) {
            $player = $event->getPlayer();
            if ($level > 3) {
                $level = 2;
            }
            if ((!$player->getEffects()->has(VanillaEffects::HASTE()))) {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::HASTE(), 50, $level));
            } else if ($player->getEffects()->get(VanillaEffects::HASTE())->getAmplifier() < $level) {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::HASTE(), 50, $level));
            }
            return;
        };
    }
}
