<?php
declare(strict_types=1);

namespace Xekvern\Core\Player\Faction\Command\Forms;

use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FlagsMenuForm extends MenuForm {

    /** @var string[] */
    private $permissions;

    /**
     * FlagsMenuForm constructor.
     *
     * @param Faction $faction
     */
    public function __construct(Faction $faction) {
        $title = TextFormat::BOLD . TextFormat::AQUA . $faction->getName();
        $options = [];
        $permissions = $faction->getPermissionsModule()->getPermissions();
        foreach($permissions as $permission => $role) {
            switch($role) {
                case Faction::RECRUIT:
                    $role = "Recruit";
                    break;
                case Faction::MEMBER:
                    $role = "Member";
                    break;
                case Faction::OFFICER:
                    $role = "Officer";
                    break;
                case Faction::LEADER:
                    $role = "Leader";
                    break;
                default:
                    $role = "Unknown";
                    break;
            }
            $options[] = new MenuOption("$permission\n$role+");
            $this->permissions[] = $permission;
        }
        parent::__construct($title, "Choose a flag to edit.", $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $faction = $player->getDataSession()->getFaction();
        if($faction === null) {
            $player->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        $permission = $this->permissions[$selectedOption];
        $player->sendForm(new FlagsSetForm($permission));
    }
}