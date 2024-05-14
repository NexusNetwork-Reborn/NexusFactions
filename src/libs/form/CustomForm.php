<?php

declare(strict_types = 1);

namespace libs\form;

use http\Exception\InvalidArgumentException;
use libs\form\element\CustomFormElement;
use libs\utils\Utils;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

abstract class CustomForm extends BaseForm {

    /** @var CustomFormElement[] */
    private $elements;

    /** @var CustomFormElement[] */
    private $elementMap = [];

    /**
     * CustomForm constructor.
     *
     * @param string $title
     * @param CustomFormElement[] $elements
     */
    public function __construct(string $title, array $elements) {
        assert(Utils::validateObjectArray($elements, CustomFormElement::class));
        parent::__construct($title);
        $this->elements = array_values($elements);
        foreach($this->elements as $element) {
            if(isset($this->elements[$element->getName()])) {
                throw new InvalidArgumentException("Multiple elements cannot have the same name, found \"" . $element->getName() . "\" more than once");
            }
            $this->elementMap[$element->getName()] = $element;
        }
    }

    /**
     * @param int $index
     *
     * @return CustomFormElement|null
     */
    public function getElement(int $index): ?CustomFormElement {
        return $this->elements[$index] ?? null;
    }

    /**
     * @param string $name
     *
     * @return null|CustomFormElement
     */
    public function getElementByName(string $name): ?CustomFormElement {
        return $this->elementMap[$name] ?? null;
    }

    /**
     * @return CustomFormElement[]
     */
    public function getAllElements(): array {
        return $this->elements;
    }

    /**
     * @param Player $player
     * @param CustomFormResponse $data
     */
    public function onSubmit(Player $player, CustomFormResponse $data): void {
    }

    /**
     * Called when a player closes the form without submitting it.
     *
     * @param Player $player
     */
    public function onClose(Player $player): void {
    }

    /**
     * @param Player $player
     * @param mixed $data
     */
    final public function handleResponse(Player $player, $data): void {
        if($data === null) {
            $this->onClose($player);
        }
        elseif(is_array($data)) {
            if(($actual = count($data)) !== ($expected = count($this->elements))) {
                throw new FormValidationException("Expected $expected result data, got $actual");
            }
            $values = [];
            /** @var array $data */
            foreach($data as $index => $value) {
                if(!isset($this->elements[$index])) {
                    throw new FormValidationException("Element at offset $index does not exist");
                }
                $element = $this->elements[$index];
                try {
                    $element->validateValue($value);
                } catch(FormValidationException $e) {
                    throw new FormValidationException("Validation failed for element \"" . $element->getName() . "\": " . $e->getMessage(), 0, $e);
                }
                $values[$element->getName()] = $value;
            }
            $this->onSubmit($player, new CustomFormResponse($values));
        }
        else {
            throw new FormValidationException("Expected array or null, got " . gettype($data));
        }
    }

    /**
     * @return string
     */
    protected function getType(): string {
        return "custom_form";
    }

    /**
     * @return array
     */
    protected function serializeFormData(): array {
        return [
            "content" => $this->elements
        ];
    }
}