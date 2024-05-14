<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Claims;

use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;

class UnclaimNearSubCommand extends SubCommand {

    /**
     * UnclaimSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("unclaimnear", "/faction unclaimnear <chunks> ");
        $this->registerArgument(0, new IntegerArgument("chunks"));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (!$sender->isLoaded()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $senderFaction = $sender->getDataSession()->getFaction();
        if($senderFaction === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        if(!$senderFaction->getPermissionsModule()->hasPermission($sender, PermissionsModule::PERMISSION_CLAIM)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $chunks = (int)$args[1];
        if($chunks <= 0) {
            $sender->sendMessage(Translation::getMessage("invalidAmount"));
            return;
        }
        if($chunks > 25) {
            $sender->sendMessage(Translation::RED . "You can only claim up to a 25-chunk range!");
            return;
        }
        $sender->sendMessage(TextFormat::GREEN . "Unclaiming...");
        $chunkX = $sender->getPosition()->getX() >> 4;
        $chunkZ = $sender->getPosition()->getZ() >> 4;
        $factionManager = $this->getCore()->getPlayerManager()->getFactionHandler();
        for($i = $chunkX - $chunks; $i <= $chunkX + $chunks; $i++) {
            for($j = $chunkZ - $chunks; $j <= $chunkZ + $chunks; $j++) {
                $claim = $factionManager->getClaimByHash(World::chunkHash($i, $j));
                if($claim === null) {
                    $sender->sendMessage(TextFormat::RED . "Failed to unclaim chunk (X = " . ($i << 4) . ", Z = " . ($j << 4) .") (Reason: Not claimed)");
                    continue;
                }
                if(!$claim->getFaction()->isInFaction($sender->getName())) {
                    $sender->sendMessage(TextFormat::RED . "Failed to unclaim chunk (X = " . ($i << 4) . ", Z = " . ($j << 4) .") (Reason: Don't own claim)");
                    continue;
                }
                $sender->sendMessage(TextFormat::GREEN . "Successfully unclaimed chunk (X = " . ($i << 4) . ", Z = " . ($j << 4) .")");
                $factionManager->removeClaim($claim);
            }
        }
        $sender->sendMessage(TextFormat::GREEN . "Unclaiming finished.");
    }
}