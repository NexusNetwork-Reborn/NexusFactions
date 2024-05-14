<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Item\Task\EntityTextTask;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Item\ItemHandler;

class DrainEnchantment extends Enchantment
{

    /**
     * DrainEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Drain", Rarity::RARE, "Have a chance to steal health from your opponent.", self::DAMAGE, ItemFlags::SWORD, 5);
        $this->callable = function (EntityDamageByEntityEvent $event, int $level) {
            $damager = $event->getDamager();
            $maxHealth = $damager->getMaxHealth();
            if (!$damager instanceof NexusPlayer) {
                return;
            }
            if ($damager->getHealth() === $maxHealth) {
                return;
            }
            $random = mt_rand(1, 60);
            $chance = $level * 3;
            if ($chance >= $random) {
                $amount = $damager->getHealth() + ($event->getFinalDamage() * 0.15);
                if ($amount > $maxHealth) {
                    $damager->setHealth($maxHealth);
                    return;
                }
                $damager->setHealth($amount);
                $entity = $event->getEntity();
                if ($entity instanceof NexusPlayer and $entity->getCESession()->isDivineProtected()) {
                    return;
                }
                if ($entity instanceof Player) {
                    $entity->sendMessage(Translation::RED . "You've been drained.");
                }
                $damager->sendMessage(Translation::GREEN . "Drain " . ItemHandler::getRomanNumber($level) . " has Activated");
                return;
            }
        };
    }

}