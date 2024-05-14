<?php

declare(strict_types = 1);

namespace libs\form\element;

class StepSlider extends BaseSelector {

    /**
     * @return string
     */
    public function getType(): string {
        return "step_slider";
    }

    /**
     * @return array
     */
    protected function serializeElementData(): array {
        return [
            "steps" => $this->options,
            "default" => $this->defaultOptionIndex
        ];
    }
}