<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Vault\Command\SubCommands;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class ViewSubCommand extends SubCommand {

    /**
     * ViewSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("view", "/pv view <player>");
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

        //temporary disable
        if($sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            $sender->sendMessage(TextFormat::DARK_RED . "TEMPORARILY DISABLED!");
            return;
        }
        if((!$sender->hasPermission("permission.admin")) and (!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $target = $args[1];
        $vaults = $this->getCore()->getPlayerManager()->getVaultHandler()->getVaultsFor($target);
        if(empty($vaults)) {
            $sender->sendMessage(Translation::getMessage("invalidPlayer"));
            return;
        }
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        foreach($vaults as $id => $vault) {
            $menu->getInventory()->setItem($id - 1, VanillaBlocks::CHEST()->asItem()->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "PV $id (" . $vault->getAlias() . ")"));
        }
        $menu->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction) use($vaults): void {
            $action = $transaction->getAction();
            $player = $transaction->getPlayer();
            $number = $action->getSlot() + 1;
            if(!$player instanceof NexusPlayer) {
                return;
            }
            if($transaction->getItemClicked()->getTypeId() !== VanillaBlocks::AIR()->asItem()->getTypeId()) {
                $vault = $vaults[$number];
                $player->removeCurrentWindow();
                $player->sendDelayedWindow($vault->getMenu());
            }
        }));
        $menu->send($sender);
    }
}