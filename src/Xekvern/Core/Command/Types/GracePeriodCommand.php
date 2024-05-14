<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Arguments\OnOrOffArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;

class GracePeriodCommand extends Command {

    /**
     * GracePeriodCommand constructor.
     */
    public function __construct() {
        parent::__construct("graceperiod", "Toggles server grace period", "/graceperiod <on|off>", ["gp"]);
        $this->registerArgument(0, new OnOrOffArgument("mode"));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if((!$sender instanceof NexusPlayer) or ((!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        switch($args[0]) {
            case "on":
                $core = Nexus::getInstance();
                if($core->isInGracePeriod()) {
                    $sender->sendMessage(Translation::RED . "The server is already on a grace period progress.");
                    return;
                }
                $core->setGracePeriod(true);
                foreach($core->getServer()->getOnlinePlayers() as $players) {
                    if($players instanceof NexusPlayer) {
                        $players->sendTitle(TextFormat::BOLD . TextFormat::DARK_PURPLE . "Grace Period", TextFormat::GRAY . "The grace period has begun!");
                        $players->playSound("mob.wither.death", 1.5, 1);
                    }
                }
                $sender->sendMessage(Translation::GREEN . "You have enabled the grace period for the entire server.");
                return;
            case "off":
                $core = Nexus::getInstance();
                if(!$core->isInGracePeriod()) {
                    $sender->sendMessage(Translation::RED . "The server is not even on a grace period progress.");
                    return;
                }
                $core->setGracePeriod(false);
                $sender->sendMessage(Translation::AQUA . "You have toggled off the grace period for the entire server.");
                return;
            default:
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
                $sender->playErrorSound(); 
                return;
        }
    }
}