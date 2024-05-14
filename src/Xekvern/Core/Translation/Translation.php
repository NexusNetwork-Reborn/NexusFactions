<?php

declare(strict_types=1);

namespace Xekvern\Core\Translation;

use pocketmine\utils\TextFormat;

class Translation implements Messages
{

    /**
     * @param string $identifier
     * @param array $args
     *
     * @return string
     *
     * @throws TranslatonException
     */
    public static function getMessage(string $identifier, array $args = []): string
    {
        if (!isset(self::MESSAGE[$identifier])) {
            throw new TranslatonException("Invalid identifier: $identifier");
        }
        $message = self::MESSAGE[$identifier];
        foreach ($args as $arg => $value) {
            $message = str_replace("{" . $arg . "}", $value . TextFormat::RESET . TextFormat::GRAY, $message);
        }
        return (string)$message;
    }
}
