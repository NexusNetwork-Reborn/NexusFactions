<?php
declare(strict_types=1);

namespace Xekvern\Core\Command\Utils;

use Xekvern\Core\Nexus;
use pocketmine\command\CommandSender;

abstract class Command extends \pocketmine\command\Command implements IArgumentable {
    use ArgumentableTrait;

    public const ERR_INVALID_ARG_VALUE = 0x01;

    public const ERR_TOO_MANY_ARGUMENTS = 0x02;

    public const ERR_INSUFFICIENT_ARGUMENTS = 0x03;

    public const ERR_NO_ARGUMENTS = 0x04;

    /** @var SubCommand[] */
    private $subCommands = [];

    /**
     * @return Nexus
     */
    public function getCore(): Nexus {
        return Nexus::getInstance();
    }

    /**
     * @param SubCommand $subCommand
     */
    public function addSubCommand(SubCommand $subCommand): void {
        $this->subCommands[$subCommand->getName()] = $subCommand;
        foreach($subCommand->getAliases() as $alias) {
            $this->subCommands[$alias] = $subCommand;
        }
    }

    /**
     * @param string $name
     *
     * @return SubCommand|null
     */
    public function getSubCommand(string $name): ?SubCommand {
        return $this->subCommands[$name] ?? null;
    }

    /**
     * @return SubCommand[]
     */
    public function getSubCommands(): array {
        return $this->subCommands;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    abstract public function execute(CommandSender $sender, string $commandLabel, array $args): void;
}