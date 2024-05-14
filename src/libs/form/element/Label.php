<?php

declare(strict_types = 1);

namespace libs\form\element;

/**
 * Element which displays some text on a form.
 */
class Label extends CustomFormElement {

    /**
     * @return string
     */
    public function getType(): string {
        return "label";
    }

    /**
     * @param mixed $value
     */
    public function validateValue($value): void {
        assert($value === null);
    }

    /**
     * @return array
     */
    protected function serializeElementData(): array {
        return [];
    }
}