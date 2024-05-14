<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Claims;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Utils\Claim;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;

class ClaimSubCommand extends SubCommand
{

    /**
     * ClaimSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("claim", "/faction claim");
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
        if ($sender->getWorld()->getDisplayName() !== Faction::CLAIM_WORLD) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $areaManager = Nexus::getInstance()->getServerManager()->getAreaHandler();
        $area = $areaManager->getAreaByPosition($sender->getPosition()->asPosition());
        if($area !== null) {
            if($area->getEditFlag() === false) {
                $sender->sendMessage(Translation::getMessage("noPermission"));
                return;
            }
        }
        $senderFaction = $sender->getDataSession()->getFaction();
        if ($senderFaction === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        if (!$senderFaction->getPermissionsModule()->hasPermission($sender, PermissionsModule::PERMISSION_CLAIM)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $factionManager = $this->getCore()->getPlayerManager()->getFactionHandler();
        if ($factionManager->getClaimInPosition($sender->getPosition()) !== null) {
            $sender->sendMessage(Translation::getMessage("inClaim"));
            return;
        }
        if (count($senderFaction->getMembers()) < Faction::MEMBERS_NEEDED_TO_CLAIM) {
            $sender->sendMessage(Translation::RED . "You do not have enough members to create claims for your faction.");
            return;
        }
        if (count($factionManager->getClaimsOf($senderFaction)) >= $senderFaction->getClaimLimit()) {
            $sender->sendMessage(Translation::getMessage("maxClaims"));
            return;
        }
        Nexus::getInstance()->getPlayerManager()->getFactionHandler()->addClaim(new Claim((int)$sender->getPosition()->getX() >> 4, (int)$sender->getPosition()->getZ() >> 4, $senderFaction));
        $sender->sendMessage(Translation::getMessage("claimSuccess"));
    }
}
