<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Claims;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Faction\Command\Task\OverclaimTask;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;

class OverClaimSubCommand extends SubCommand
{

    /**
     * OverClaimSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("overclaim", "/faction overclaim");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (!$sender->isLoaded()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if ($sender->getWorld()->getFolderName() !== Faction::CLAIM_WORLD) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (Nexus::getInstance()->isInGracePeriod()) {
            $sender->sendTitle(TextFormat::BOLD . TextFormat::RED . "Grace Period", TextFormat::GRAY . "You can't do this action while on grace period!");
            $sender->sendMessage(Translation::RED . "You can't use this while the server is on grace period!");
            $sender->playErrorSound();
            return;
        }
        $faction = $sender->getDataSession()->getFaction();
        $factionHandler = $this->getCore()->getPlayerManager()->getFactionHandler();
        if ($faction === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        if (count($faction->getMembers()) < Faction::MEMBERS_NEEDED_TO_CLAIM) {
            $sender->sendMessage(Translation::getMessage("notEnoughFactionMembersToClaim"));
            return;
        }
        if (!$faction->getPermissionsModule()->hasPermission($sender, PermissionsModule::PERMISSION_CLAIM)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (($claim = $factionHandler->getClaimInPosition($sender->getPosition())) === null) {
            $sender->sendMessage(Translation::getMessage("notClaimed"));
            return;
        }
        if ($claim->getFaction()->getStrength() >= $faction->getStrength()) {
            $sender->sendMessage(Translation::getMessage("notEnoughStrength"));
            return;
        }
        if (count($factionHandler->getClaimsOf($faction)) >= $faction->getClaimLimit()) {
            $sender->sendMessage(Translation::getMessage("maxClaims"));
            return;
        }
        Nexus::getInstance()->getScheduler()->scheduleRepeatingTask(new OverclaimTask($sender, $claim->getFaction()), 20);
        $sender->sendMessage(Translation::GREEN . "You have now started overclaiming the claim of " . TextFormat::YELLOW . $claim->getFaction()->getName());
    }
}
