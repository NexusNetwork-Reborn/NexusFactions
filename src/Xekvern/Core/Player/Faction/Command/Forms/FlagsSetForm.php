<?php
declare(strict_types=1);

namespace Xekvern\Core\Player\Faction\Command\Forms;

use Xekvern\Core\Player\Faction\FactionException;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\MenuForm;
use libs\form\MenuOption;
use libs\utils\UtilsException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FlagsSetForm extends MenuForm {

    /** @var string */
    private $permission;

    /**
     * FlagsSetForm constructor.
     *
     * @param string $permission
     */
    public function __construct(string $permission) {
        $title = TextFormat::BOLD . TextFormat::AQUA . $permission;
        $options = [];
        $this->permission = $permission;
        $options[] = new MenuOption("Recruit");
        $options[] = new MenuOption("Member");
        $options[] = new MenuOption("Officer");
        $options[] = new MenuOption("Leader");
        parent::__construct($title, "Which role would you like to allow?", $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     *
     * @throws UtilsException
     * @throws FactionException
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $gang = $player->getDataSession()->getFaction();
        if($gang === null) {
            $player->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        $gang->getPermissionsModule()->setValue($this->permission, $selectedOption);
        $player->sendMessage(Translation::getMessage("roleFlagSet", [
            "name" => TextFormat::YELLOW . $this->permission,
            "role" => TextFormat::LIGHT_PURPLE . $this->getOption($selectedOption)->getText()
        ]));
    }
}