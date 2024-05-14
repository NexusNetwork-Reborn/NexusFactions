<?php

declare(strict_types = 1);

namespace libs\form\element;

use http\Exception\InvalidArgumentException;
use pocketmine\form\FormValidationException;

abstract class BaseSelector extends CustomFormElement {

    /** @var int */
    protected $defaultOptionIndex;

    /** @var string[] */
    protected $options;

    /**
     * BaseSelector constructor.
     *
     * @param string $name
     * @param string $text
     * @param array $options
     * @param int $defaultOptionIndex
     */
    public function __construct(string $name, string $text, array $options, int $defaultOptionIndex = 0) {
        parent::__construct($name, $text);
        $this->options = array_values($options);
        if(!isset($this->options[$defaultOptionIndex])) {
            throw new InvalidArgumentException("No option at index $defaultOptionIndex, cannot set as default");
        }
        $this->defaultOptionIndex = $defaultOptionIndex;
    }

    /**
     * @param int $value
     *
     * @throws FormValidationException
     */
    public function validateValue($value): void {
        if(!is_int($value)) {
            throw new FormValidationException("Expected int, got " . gettype($value));
        }
        if(!isset($this->options[$value])) {
            throw new FormValidationException("Option $value does not exist");
        }
    }

    /**
     * Returns the text of the option at the specified index, or null if it doesn't exist.
     *
     * @param int $index
     *
     * @return string|null
     */
    public function getOption(int $index): ?string {
        return $this->options[$index] ?? null;
    }

    /**
     * @return int
     */
    public function getDefaultOptionIndex(): int {
        return $this->defaultOptionIndex;
    }

    /**
     * @return string
     */
    public function getDefaultOption(): string {
        return $this->options[$this->defaultOptionIndex];
    }

    /**
     * @return string[]
     */
    public function getOptions(): array {
        return $this->options;
    }
}