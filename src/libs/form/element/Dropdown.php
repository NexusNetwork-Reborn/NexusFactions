<?php

declare(strict_types = 1);

namespace libs\form\element;

class Dropdown extends BaseSelector {

    /**
     * @return string
     */
    public function getType(): string {
        return "dropdown";
    }

    /**
     * @return array
     */
    protected function serializeElementData(): array {
        return [
            "options" => $this->options,
            "default" => $this->defaultOptionIndex
        ];
    }
}