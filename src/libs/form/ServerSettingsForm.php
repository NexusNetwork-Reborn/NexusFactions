<?php

declare(strict_types = 1);

namespace libs\form;

/**
 * Represents a custom form which can be shown in the Settings menu on the client. This is exactly the same as a regular
 * CustomForm, except that this type can also have an icon which can be shown on the settings section button.
 *
 * Passing this form to {@link Player::sendForm()} will not show a form with an icon nor set this form as the server
 * settings.
 */
abstract class ServerSettingsForm extends CustomForm {

    /**
     * @var FormIcon|null
     */
    private $icon;

    /**
     * ServerSettingsForm constructor.
     *
     * @param string $title
     * @param array $elements
     * @param FormIcon|null $icon
     */
    public function __construct(string $title, array $elements, ?FormIcon $icon = null) {
        parent::__construct($title, $elements);
        $this->icon = $icon;
    }

    /**
     * @return bool
     */
    public function hasIcon(): bool {
        return $this->icon !== null;
    }

    /**
     * @return FormIcon|null
     */
    public function getIcon(): ?FormIcon {
        return $this->icon;
    }

    /**
     * @return array
     */
    protected function serializeFormData(): array {
        $data = parent::serializeFormData();
        if($this->hasIcon()) {
            $data["icon"] = $this->icon;
        }
        return $data;
    }
}