<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Forms;

use libs\form\CustomForm;
use libs\form\element\Label;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\ItemHandler;

class CEInfoForm extends CustomForm
{

    /**
     * CEInfoForm constructor.
     *
     * @param Enchantment $enchantment
     */
    public function __construct(Enchantment $enchantment)
    {
        $title = TextFormat::BOLD . TextFormat::AQUA . $enchantment->getName();
        $elements = [];
        $elements[] = new Label($enchantment->getName(), ItemHandler::rarityToColor($enchantment->getRarity()) . TextFormat::BOLD . $enchantment->getName() . TextFormat::RESET . TextFormat::AQUA . "\nApplicable Items: " . TextFormat::WHITE . ItemHandler::flagToString($enchantment->getPrimaryItemFlags()) . TextFormat::AQUA . "\nMax Level: " . TextFormat::WHITE . $enchantment->getMaxLevel()  . TextFormat::AQUA . "\nDescription: " . TextFormat::WHITE . $enchantment->getDescription()  . TextFormat::AQUA . "\nRarity: " . TextFormat::WHITE . ItemHandler::rarityToString($enchantment->getRarity()));
        parent::__construct($title, $elements);
    }
}
