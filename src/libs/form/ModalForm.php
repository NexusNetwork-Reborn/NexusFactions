<?php

declare(strict_types = 1);

namespace libs\form;

use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

/**
 * This form type presents a simple "yes/no" dialog with two buttons.
 */
abstract class ModalForm extends BaseForm {

    /** @var string */
    protected $content;

    /** @var string */
    protected $button1;

    /** @var string */
    protected $button2;

    /**
     * ModalForm constructor.
     *
     * @param string $title
     * @param string $text
     * @param string $yesButtonText
     * @param string $noButtonText
     */
    public function __construct(string $title, string $text, string $yesButtonText = "gui.yes", string $noButtonText = "gui.no") {
        parent::__construct($title);
        $this->content = $text;
        $this->button1 = $yesButtonText;
        $this->button2 = $noButtonText;
    }

    /**
     * @return string
     */
    public function getYesButtonText(): string {
        return $this->button1;
    }

    /**
     * @return string
     */
    public function getNoButtonText(): string {
        return $this->button2;
    }

    /**
     * @param Player $player Player submitting this form
     * @param bool $choice Selected option. True for yes button, false for no button.
     */
    public function onSubmit(Player $player, bool $choice): void {
    }

    /**
     * @param Player $player
     * @param mixed $data
     */
    final public function handleResponse(Player $player, $data): void {
        if(!is_bool($data)) {
            throw new FormValidationException("Expected bool, got " . gettype($data));
        }
        $this->onSubmit($player, $data);
    }

    /**
     * @return string
     */
    protected function getType(): string {
        return "modal";
    }

    /**
     * @return array
     */
    protected function serializeFormData(): array {
        return [
            "content" => $this->content,
            "button1" => $this->button1,
            "button2" => $this->button2,
            "type" => $this->getType()
        ];
    }
}