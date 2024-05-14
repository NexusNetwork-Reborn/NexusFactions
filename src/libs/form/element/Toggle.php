<?php

declare(strict_types = 1);

namespace libs\form\element;

use pocketmine\form\FormValidationException;

/**
 * Represents a UI on/off switch. The switch may have a default value.
 */
class Toggle extends CustomFormElement {

    /** @var bool */
    private $default;

    /**
     * Toggle constructor.
     *
     * @param string $name
     * @param string $text
     * @param bool $defaultValue
     */
    public function __construct(string $name, string $text, bool $defaultValue = false) {
        parent::__construct($name, $text);
        $this->default = $defaultValue;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return "toggle";
    }

    /**
     * @return bool
     */
    public function getDefaultValue(): bool {
        return $this->default;
    }

    /**
     * @param bool $value
     *
     * @throws
     */
    public function validateValue($value): void {
        if(!is_bool($value)) {
            throw new FormValidationException("Expected bool, got " . gettype($value));
        }
    }

    /**
     * @return array
     */
    protected function serializeElementData(): array {
        return [
            "default" => $this->default
        ];
    }
}