<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\Block;
use pocketmine\block\utils\DyeColor;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\World\Tile\Generator;
use Xekvern\Core\Utils\Utils;

class AbilityScroll extends ClickableItem
{

    const ABILITYSCROLL = "Ability Scroll";

    /**
     * SpeedAbilityScroll constructor.
     */
    public function __construct(int $abilityType)
    {
        switch ($abilityType) {
            case 0:
                $ability = [
                    "name" => TextFormat::GOLD . "Haste ",
                    "description" => "A quick way to mine",
                    "effect" => "gives the user haste 10 for 15 seconds",
                    "cooldown" => "60s",
                    "dye" => DyeColor::ORANGE
                ];
                break;
            case 1:
                $ability = [
                    "name" => TextFormat::RED . "Regen ",
                    "description" => "A quick way to heal",
                    "effect" => "gives the user regeneration 15 for 2 seconds",
                    "cooldown" => "30s",
                    "dye" => DyeColor::RED
                ];
                break;
            case 2:
                $ability = [
                    "name" => TextFormat::DARK_PURPLE . "Resist ",
                    "description" => "A quick way to resist death",
                    "effect" => "gives the user resistance 5 for 3 seconds",
                    "cooldown" => "60s",
                    "dye" => DyeColor::PURPLE
                ];
                break;
            case 3:
                $ability = [
                    "name" => TextFormat::BLUE . "Speed ",
                    "description" => "A quick way to escape",
                    "effect" => "gives the user speed 10 for 3 seconds",
                    "cooldown" => "60s",
                    "dye" => DyeColor::BLUE
                ];
                break;
            case 4:
                $ability = [
                    "name" => TextFormat::LIGHT_PURPLE . "Strength ",
                    "description" => "A quick way to kill",
                    "effect" => "gives the user strength 3 for 3 seconds",
                    "cooldown" => "30s",
                    "dye" => DyeColor::MAGENTA
                ];
                break;
        }
        $customName = TextFormat::RESET . TextFormat::BOLD . $ability["name"] . TextFormat::AQUA . "Ability Scroll";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GOLD . $ability["description"];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . $ability["effect"];
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Cooldown: " . TextFormat::BOLD . TextFormat::RED . $ability["cooldown"];
        parent::__construct(VanillaItems::DYE()->setColor($ability["dye"]), $customName, $lore, [], [
            self::ABILITYSCROLL => new IntTag($abilityType)
        ]);
    }


    /**
     * @param NexusPlayer $player
     * @param Inventory $inventory
     * @param Item $item
     * @param CompoundTag $tag
     * @param int $face
     * @param Block $blockClicked
     *
     * @throws TranslatonException
     */
    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void
    {
        $abilityType = $tag->getInt(self::ABILITYSCROLL);
        $abilities = [
            [
                "name" => "Haste",
                "color" => TextFormat::GOLD,
                "effect" => VanillaEffects::HASTE(),
                "amplifier" => 9,
                "duration" => 300,
                "cooldown" => 60,
                "getLastUsedMethod" => "getLastHasteAbilityScroll",
                "setLastUsedMethod" => "setLastHasteAbilityScroll",
                "cooldownMessage" => TextFormat::GOLD . "Haste " . TextFormat::AQUA . "Ability Scroll"
            ],
            [
                "name" => "Regen",
                "color" => TextFormat::RED,
                "effect" => VanillaEffects::REGENERATION(),
                "amplifier" => 14,
                "duration" => 40,
                "cooldown" => 60,
                "getLastUsedMethod" => "getLastRegenAbilityScroll",
                "setLastUsedMethod" => "setLastRegenAbilityScroll",
                "cooldownMessage" => TextFormat::RED . "Regen " . TextFormat::AQUA . "Ability Scroll"
            ],
            [
                "name" => "Resist",
                "color" => TextFormat::DARK_PURPLE,
                "effect" => VanillaEffects::RESISTANCE(),
                "amplifier" => 4,
                "duration" => 60,
                "cooldown" => 60,
                "getLastUsedMethod" => "getLastResistAbilityScroll",
                "setLastUsedMethod" => "setLastResistAbilityScroll",
                "cooldownMessage" => TextFormat::DARK_PURPLE . "Resist " . TextFormat::AQUA . "Ability Scroll"
            ],
            [
                "name" => "Speed",
                "color" => TextFormat::BLUE,
                "effect" => VanillaEffects::SPEED(),
                "amplifier" => 9,
                "duration" => 60,
                "cooldown" => 60,
                "getLastUsedMethod" => "getLastSpeedAbilityScroll",
                "setLastUsedMethod" => "setLastSpeedAbilityScroll",
                "cooldownMessage" => TextFormat::BLUE . "Speed " . TextFormat::AQUA . "Ability Scroll"
            ],
            [
                "name" => "Strength",
                "color" => TextFormat::LIGHT_PURPLE,
                "effect" => VanillaEffects::STRENGTH(),
                "amplifier" => 2,
                "duration" => 60,
                "cooldown" => 30,
                "getLastUsedMethod" => "getLastStrengthAbilityScroll",
                "setLastUsedMethod" => "setLastStrengthAbilityScroll",
                "cooldownMessage" => TextFormat::LIGHT_PURPLE . "Strength " . TextFormat::AQUA . "Ability Scroll"
            ]
        ];
        $ability = $abilities[$abilityType];
        $cooldown = $ability["cooldown"] - (time() - $player->{$ability["getLastUsedMethod"]}());
        if ($cooldown > 0) {
            $player->sendMessage(Translation::getMessage("actionCooldown", [
                "amount" => TextFormat::RED . $cooldown
            ]));
            return;
        }
        $player->{$ability["setLastUsedMethod"]}();
        $player->getEffects()->add(new EffectInstance($ability["effect"], $ability["duration"], $ability["amplifier"], true));
        $player->sendMessage(TextFormat::RESET . TextFormat::GREEN . "Successfully used " . $ability["color"] . $ability["name"] . TextFormat::AQUA . " Ability Scroll");
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }
}
