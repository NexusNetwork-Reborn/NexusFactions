<?php

declare(strict_types = 1);

namespace libs\form;

use JsonSerializable;

/**
 * Represents an option on a MenuForm. The option is shown as a button and may optionally have an image next to it.
 */
class MenuOption implements JsonSerializable {

    /** @var string */
    private $text;

    /** @var FormIcon|null */
    private $image;

    /**
     * MenuOption constructor.
     *
     * @param string $text
     * @param FormIcon|null $image
     */
    public function __construct(string $text, ?FormIcon $image = null) {
        $this->text = $text;
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getText(): string {
        return $this->text;
    }

    /**
     * @return bool
     */
    public function hasImage(): bool {
        return $this->image !== null;
    }

    /**
     * @return FormIcon|null
     */
    public function getImage(): ?FormIcon {
        return $this->image;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): mixed {
        $json = [
            "text" => $this->text
        ];
        if($this->hasImage()) {
            $json["image"] = $this->image;
        }
        return $json;
    }
}