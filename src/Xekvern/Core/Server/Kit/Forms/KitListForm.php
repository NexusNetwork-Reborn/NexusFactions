<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Kit\Forms;

use Xekvern\Core\Server\Kit\SacredKit;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;

class KitListForm extends MenuForm {

    /**
     * KitListForm constructor.
     *
     * @param NexusPlayer $player
     * @param array $kits
     */
    public function __construct(NexusPlayer $player, array $kits) {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Kits";
        $text = "Select a kit.";
        $options = [];
        foreach($kits as $kit) {
            if(!$player->hasPermission("permission." . strtolower($kit->getName()))) {
                $options[] = new MenuOption($kit->getName() . "\n" . TextFormat::BOLD . TextFormat::RED . "LOCKED");
                continue;
            }
            $cooldown = $player->getDataSession()->getKitCooldown($kit);
            $cooldown = $kit->getCooldown() - (time() - $cooldown);
            if($cooldown > 0) {
                $days = floor($cooldown / 86400);
                $hours = (int)($cooldown / 3600) % 24;
                $minutes = ((int)($cooldown / 60) % 60);
                $seconds = $cooldown % 60;
                $time = $days. "d $hours" . "h $minutes" . "m $seconds" . "s";
                $options[] = new MenuOption($kit->getName() . "\n" . TextFormat::RED . "CD: $time");
            }
            else {
                if($kit instanceof SacredKit) {
                    $tier = $player->getDataSession()->getSacredKitTier($kit);
                    $options[] = new MenuOption($kit->getName() . "\n" . TextFormat::BOLD . TextFormat::GREEN . "READY (TIER $tier)");
                    continue;
                }
                $options[] = new MenuOption($kit->getName() . "\n" . TextFormat::BOLD . TextFormat::GREEN . "READY");
            }
        }
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     *
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $name = explode("\n", $this->getOption($selectedOption)->getText())[0];
        $kitManager = Nexus::getInstance()->getServerManager()->getKitHandler();
        $kit = $kitManager->getKitByName($name);
        $player->sendForm(new KitPreviewForm($player, $kit));
    }
}