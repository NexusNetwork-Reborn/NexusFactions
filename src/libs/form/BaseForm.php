<?php

declare(strict_types = 1);

namespace libs\form;

use pocketmine\form\Form;

/**
 * Base class for a custom form. Forms are serialized to JSON data to be sent to clients.
 */
abstract class BaseForm implements Form {

    /** @var string */
    protected $title;

    public function __construct(string $title) {
        $this->title = $title;
    }

    /**
     * Returns the text shown on the form title-bar.
     * @return string
     */
    public function getTitle(): string {
        return $this->title ?? "";
    }

    /**
     * Serializes the form to JSON for sending to clients.
     *
     * @return array
     */
    final public function jsonSerialize(): array {
        $ret = $this->serializeFormData();
        $ret["type"] = $this->getType();
        $ret["title"] = $this->getTitle();
        return $ret;
    }

    /**
     * Returns the type used to show this form to clients
     * @return string
     */
    abstract protected function getType(): string;

    /**
     * Serializes additional data needed to show this form to clients.
     * @return array
     */
    abstract protected function serializeFormData(): array;
}