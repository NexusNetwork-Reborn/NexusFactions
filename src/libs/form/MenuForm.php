<?php

declare(strict_types = 1);

namespace libs\form;

use libs\utils\Utils;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

/**
 * This form type presents a menu to the user with a block of options on it. The user may select an option or close the
 * form by clicking the X in the top left corner.
 */
abstract class MenuForm extends BaseForm {

    /** @var string */
    protected $content;

    /** @var MenuOption[] */
    private $options;

    /**
     * MenuForm constructor.
     *
     * @param string $title
     * @param string $text
     * @param array $options
     */
    public function __construct(string $title, string $text, array $options) {
        assert(Utils::validateObjectArray($options, MenuOption::class));
        parent::__construct($title);
        $this->content = $text;
        $this->options = array_values($options);
    }

    public function getOption(int $position): ?MenuOption {
        return $this->options[$position] ?? null;
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void {
    }

    /**
     * Called when a player clicks the close button on this form without selecting an option.
     *
     * @param Player $player
     */
    public function onClose(Player $player): void {
    }

    /**
     * @param Player $player
     * @param mixed $data
     */
    final public function handleResponse(Player $player, $data): void {
        if($data === null) {
            $this->onClose($player);
        }
        elseif(is_int($data)) {
            if(!isset($this->options[$data])) {
                throw new FormValidationException("Option $data does not exist");
            }
            $this->onSubmit($player, $data);
        }
        else {
            throw new FormValidationException("Expected int or null, got " . gettype($data));
        }
    }

    /**
     * @return string
     */
    protected function getType(): string {
        return "form";
    }

    /**
     * @return array
     */
    protected function serializeFormData(): array {
        return [
            "content" => $this->content,
            "buttons" => $this->options //yes, this is intended (MCPE calls them buttons)
        ];
    }
}