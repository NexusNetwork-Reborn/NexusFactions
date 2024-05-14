<?php

declare(strict_types = 1);

namespace libs\form\element;

use pocketmine\form\FormValidationException;
use InvalidArgumentException;

class Slider extends CustomFormElement {

    /** @var float */
    private $min;

    /** @var float */
    private $max;

    /** @var float */
    private $step;

    /** @var float */
    private $default;

    /**
     * Slider constructor.
     *
     * @param string $name
     * @param string $text
     * @param float $min
     * @param float $max
     * @param float $step
     * @param float|null $default
     */
    public function __construct(string $name, string $text, float $min, float $max, float $step = 1.0, ?float $default = null) {
        parent::__construct($name, $text);
        if($this->min > $this->max) {
            throw new InvalidArgumentException("Slider min value should be less than max value");
        }
        $this->min = $min;
        $this->max = $max;
        if($default !== null) {
            if($default > $this->max or $default < $this->min) {
                throw new InvalidArgumentException("Default must be in range $this->min ... $this->max");
            }
            $this->default = $default;
        }
        else {
            $this->default = $this->min;
        }
        if($step <= 0) {
            throw new InvalidArgumentException("Step must be greater than zero");
        }
        $this->step = $step;
    }

    public function getType(): string {
        return "slider";
    }

    /**
     * @param float $value
     *
     * @throws FormValidationException
     */
    public function validateValue($value): void {
        if(!is_float($value) and !is_int($value)) {
            throw new FormValidationException("Expected float, got " . gettype($value));
        }
        if($value < $this->min or $value > $this->max) {
            throw new FormValidationException("Value $value is out of bounds (min $this->min, max $this->max)");
        }
    }

    /**
     * @return float
     */
    public function getMin(): float {
        return $this->min;
    }

    /**
     * @return float
     */
    public function getMax(): float {
        return $this->max;
    }

    /**
     * @return float
     */
    public function getStep(): float {
        return $this->step;
    }

    /**
     * @return float
     */
    public function getDefault(): float {
        return $this->default;
    }

    /**
     * @return array
     */
    protected function serializeElementData(): array {
        return [
            "min" => $this->min,
            "max" => $this->max,
            "default" => $this->default,
            "step" => $this->step
        ];
    }
}