<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\Types\Head;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;

class GuillotineEnchantment extends Enchantment
{

    /**
     * GuillotineEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Guillotine", Rarity::MYTHIC, "Have a chance to obtain your opponent's head.", self::DEATH, ItemFlags::SWORD, 10);
        $this->callable = function (PlayerDeathEvent $event, int $level) {
            $cause = $event->getPlayer()->getLastDamageCause();
            /** @var NexusPlayer $player */
            $player = $event->getPlayer();
            if ($cause instanceof EntityDamageByEntityEvent) {
                $damager = $cause->getDamager();
                if (!$damager instanceof NexusPlayer) {
                    return;
                }
                $random = mt_rand(1, 10);
                if ($level >= $random) {
                    $event->setDrops(array_merge($event->getDrops(), [(new Head($player))->getItemForm()]));
                }
            }
        };
    }
}