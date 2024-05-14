<?php

declare(strict_types = 1);

namespace libs\form\element;

use pocketmine\form\FormValidationException;

/**
 * Element which accepts text input. The text-box can have a default value, and may also have a text hint when there is
 * no text in the box.
 */
class Input extends CustomFormElement {

    /** @var string */
    private $hint;

    /** @var string */
    private $default;

    /**
     * @param string $name
     * @param string $text
     * @param string $hintText
     * @param string $defaultText
     */
    public function __construct(string $name, string $text, string $hintText = "", string $defaultText = "") {
        parent::__construct($name, $text);
        $this->hint = $hintText;
        $this->default = $defaultText;
    }

    public function getType(): string {
        return "input";
    }

    /**
     * @param string $value
     *
     * @throws FormValidationException
     */
    public function validateValue($value): void {
        if(!is_string($value)) {
            throw new FormValidationException("Expected string, got " . gettype($value));
        }
    }

    /**
     * Returns the text shown in the text-box when the box is not focused and there is no text in it.
     * @return string
     */
    public function getHintText(): string {
        return $this->hint;
    }

    /**
     * Returns the text which will be in the text-box by default.
     * @return string
     */
    public function getDefaultText(): string {
        return $this->default;
    }

    protected function serializeElementData(): array {
        return [
            "placeholder" => $this->hint,
            "default" => $this->default
        ];
    }
}