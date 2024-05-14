<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command;

use Xekvern\Core\Command\Utils\Command; 
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use Xekvern\Core\Player\Faction\Command\SubCommands\Admin\ForceDeleteSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\ChatSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Claims\ClaimNearSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Claims\ClaimSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Claims\OverClaimSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Claims\UnclaimNearSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Claims\UnclaimSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Currency\DepositSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Currency\WithdrawSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\HelpSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Homes\HomeSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Homes\SetHomeSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\InfoSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\JoinSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\LeaveSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Management\AnnounceSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Management\CreateSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Management\DisbandSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Management\FlagsSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Management\InviteSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Management\KickSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Management\PayoutSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\MapSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Relations\AllySubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Relations\UnallySubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Roles\DemoteSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Roles\LeaderSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\Roles\PromoteSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\TlSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\TopSubCommand;
use Xekvern\Core\Player\Faction\Command\SubCommands\VaultSubCommand;

class FactionCommand extends Command {

    /**
     * FactionCommand constructor.
     */
    public function __construct() {
        $subCommands = [
            new ForceDeleteSubCommand(),
            new CreateSubCommand(),
            new DisbandSubCommand(),
            new LeaveSubCommand(),
            new JoinSubCommand(),
            new KickSubCommand(),
            new TlSubCommand(),
            new TopSubCommand(),
            new MapSubCommand(),
            new HelpSubCommand(),
            new DepositSubCommand(),
            new WithdrawSubCommand(),
            //new ClaimNearSubCommand(),
            new ClaimSubCommand(),
            new UnclaimSubCommand(),
            new UnclaimNearSubCommand(),
            new HomeSubCommand(),
            new SetHomeSubCommand(),
            new AnnounceSubCommand(),
            new FlagsSubCommand(),
            new InviteSubCommand(),
            new InfoSubCommand(),
            new PayoutSubCommand(),
            new AllySubCommand(),
            new UnallySubCommand(),
            new DemoteSubCommand(),
            new LeaderSubCommand(),
            new PromoteSubCommand(),
            new ChatSubCommand(),
            //new VaultSubCommand(),
            new OverClaimSubCommand(),
        ];
        foreach($subCommands as $subComm) {
            $this->addSubCommand(new $subComm);
        }
        parent::__construct("faction", "Manage faction", "/faction help <1-6>", ["f"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(isset($args[0])) {
            $subCommand = $this->getSubCommand($args[0]);
            if($subCommand !== null) {
                $subCommand->execute($sender, $commandLabel, $args);
                return;
            }
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $sender->sendMessage(Translation::getMessage("usageMessage", [
            "usage" => $this->getUsage()
        ]));
    }
}