<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Rank\Rank;
use pocketmine\command\Command;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginException;
use Xekvern\Core\Command\Types\AddGemsCommand;
use Xekvern\Core\Command\Types\AddMoneyCommand;
use Xekvern\Core\Command\Types\AddPermissionCommand;
use Xekvern\Core\Command\Types\AddXPCommand;
use Xekvern\Core\Command\Types\BalanceCommand;
use Xekvern\Core\Command\Types\BalanceTopCommand;
use Xekvern\Core\Command\Types\BroadcastCommand;
use Xekvern\Core\Command\Types\CalculateCommand;
use Xekvern\Core\Command\Types\ChangeLogCommand;
use Xekvern\Core\Command\Types\ClearCommand;
use Xekvern\Core\Command\Types\ClearlagCommand;
use Xekvern\Core\Command\Types\FeedCommand;
use Xekvern\Core\Command\Types\FloatXYZCommand;
use Xekvern\Core\Command\Types\FlyCommand;
use Xekvern\Core\Command\Types\FreezeCommand;
use Xekvern\Core\Command\Types\GiveItemCommand;
use Xekvern\Core\Command\Types\GlobalMuteCommand;
use Xekvern\Core\Command\Types\GodCommand;
use Xekvern\Core\Command\Types\GracePeriodCommand;
use Xekvern\Core\Command\Types\HomeCommand;
use Xekvern\Core\Command\Types\InboxCommand;
use Xekvern\Core\Command\Types\InformationCommand;
use Xekvern\Core\Command\Types\LevelCommand;
use Xekvern\Core\Command\Types\ListCommand;
use Xekvern\Core\Command\Types\LobbyCommand;
use Xekvern\Core\Command\Types\LuckyBlockCommand;
use Xekvern\Core\Command\Types\NightVisionCommand;
use Xekvern\Core\Command\Types\OnlineTimeCommand;
use Xekvern\Core\Command\Types\PayCommand;
use Xekvern\Core\Command\Types\PingCommand;
use Xekvern\Core\Command\Types\PlaySoundCommand;
use Xekvern\Core\Command\Types\PVPCommand;
use Xekvern\Core\Command\Types\RemoveHomeCommand;
use Xekvern\Core\Command\Types\RenameCommand;
use Xekvern\Core\Command\Types\RepairCommand;
use Xekvern\Core\Command\Types\ReplyCommand;
use Xekvern\Core\Command\Types\RestartCommand;
use Xekvern\Core\Command\Types\RulesCommand;
use Xekvern\Core\Command\Types\SacredAllCommand;
use Xekvern\Core\Command\Types\SellCommand;
use Xekvern\Core\Command\Types\SellWandUsesCommand;
use Xekvern\Core\Command\Types\SetHomeCommand;
use Xekvern\Core\Command\Types\SetMoneyCommand;
use Xekvern\Core\Command\Types\SpawnCommand;
use Xekvern\Core\Command\Types\StatsCommand;
use Xekvern\Core\Command\Types\StopCommand;
use Xekvern\Core\Command\Types\TagCommand;
use Xekvern\Core\Command\Types\TeleportAskCommand;
use Xekvern\Core\Command\Types\TellCommand;
use Xekvern\Core\Command\Types\TellLocationCommand;
use Xekvern\Core\Command\Types\TestCommand;
use Xekvern\Core\Command\Types\TogglePrivateMessagesCommand;
use Xekvern\Core\Command\Types\VanishCommand;
use Xekvern\Core\Command\Types\VoteCommand;
use Xekvern\Core\Command\Types\VotesCommand;
use Xekvern\Core\Command\Types\WildCommand;
use Xekvern\Core\Command\Types\WithdrawCommand;
use Xekvern\Core\Command\Types\XYZCommand;
use Xekvern\Core\Player\Combat\Boss\Command\BossCommand;
use Xekvern\Core\Player\Combat\Koth\Command\KOTHCommand;
use Xekvern\Core\Player\Combat\Outpost\Command\OutpostCommand;
use Xekvern\Core\Player\Gamble\Command\CoinFlipCommand;
use Xekvern\Core\Player\Faction\Command\FactionCommand;
use Xekvern\Core\Player\Gamble\Command\LotteryCommand;
use Xekvern\Core\Player\Quest\Command\QuestsCommand;
use Xekvern\Core\Player\Rank\Command\SetRankCommand;
use Xekvern\Core\Player\Vault\Command\PlayerVaultCommand;
use Xekvern\Core\Server\Auction\Command\AuctionHouseCommand;
use Xekvern\Core\Server\Crate\Command\GiveKeysCommand;
use Xekvern\Core\Server\Crate\Command\KeyAllCommand;
use Xekvern\Core\Server\Item\Command\CEInfoCommand;
use Xekvern\Core\Server\Item\Command\EnchantCommand;
use Xekvern\Core\Server\Item\Forms\CEInfoForm;
use Xekvern\Core\Server\Item\Types\SellWand;
use Xekvern\Core\Server\Kit\Command\KitCommand;
use Xekvern\Core\Server\Kit\Command\SKitCommand;
use Xekvern\Core\Server\Price\Command\ShopCommand;
use Xekvern\Core\Server\Watchdog\Command\KickCommand;
use Xekvern\Core\Server\Watchdog\Command\PunishCommand;
use Xekvern\Core\Server\Watchdog\Command\StaffChatCommand;
use Xekvern\Core\Server\Watchdog\Command\StaffModeCommand;
use Xekvern\Core\Server\Watchdog\Command\UnmuteCommand;
use Xekvern\Core\Server\Watchdog\Command\HistoryCommand;
use Xekvern\Core\Server\Watchdog\Command\MuteCommand;
use Xekvern\Core\Server\Watchdog\Command\PardonCommand;
use Xekvern\Core\Command\Types\TrashCommand;
use Xekvern\Core\Command\Types\BlessCommand;
use Xekvern\Core\Command\Types\GemShopCommand;
use Xekvern\Core\Command\Types\RankUpCommand;
use Xekvern\Core\Command\Types\SetGemsCommand;
use Xekvern\Core\Server\Fund\Command\FundCommand;
use Xekvern\Core\Server\Item\Command\CEShopCommand;

class CommandManager
{

    const DISGUISES = [
        "ButterBean46" => Rank::SPARTAN, "FinnaDropEm21" => Rank::KING,
        "DuckThePolice12" => Rank::DEITY, "LetsScrapF00l" => Rank::PLAYER,
        "BigRipsss" => Rank::PLAYER, "DrunkenSailer123" => Rank::DEITY,
        "thiccMarshall" => Rank::KING, "FBIwatchinu" => Rank::SPARTAN,
        "HeavySetJoe321" => Rank::PLAYER, "Stonrs4Lif3" => Rank::PLAYER,
        "Roawrses" => Rank::PLAYER, "Cowtails" => Rank::PLAYER,
        "Sender852" => Rank::PLAYER, "VaterSienSohn" => Rank::PLAYER,
        "StickyVibes" => Rank::PLAYER, "ImBitter" => Rank::PLAYER,
        "Pookiechnan" => Rank::PLAYER, "Zilla" => Rank::PLAYER,
        "Xaoa" => Rank::PLAYER, "OutByte" => Rank::PLAYER,
        "SpecularBurrito" => Rank::PLAYER, "Cupofcomfy" => Rank::PLAYER,
        "Dehlu" => Rank::PLAYER, "JupyLupy" => Rank::PLAYER,
        "Deurolo" => Rank::PLAYER, "Jonesei" => Rank::PLAYER,
        "DingyHingy" => Rank::PLAYER, "ShampArtEr" => Rank::PLAYER,
        "TheGreatPerhaps" => Rank::PLAYER, "MindOfRelo" => Rank::PLAYER,
        "EleMaont" => Rank::PLAYER, "BoshToshLo" => Rank::PLAYER,
        "DaggaMan" => Rank::PLAYER, "Wrenteerd" => Rank::PLAYER,
        "NonStolpo" => Rank::PLAYER, "BeveLent" => Rank::PLAYER,
        "SaintPerper" => Rank::PLAYER, "PepperHenna" => Rank::PLAYER,
        "GlowPaint23" => Rank::PLAYER, "Dovol3l" => Rank::PLAYER,
        "CeeshesHesh" => Rank::PLAYER, "PeshestGrade" => Rank::PLAYER,
        "SentiRebi" => Rank::PLAYER, "Hanmotanny" => Rank::PLAYER,
        "Ronbubway" => Rank::PLAYER, "SUW0" => Rank::PLAYER
    ];

    /** @var Nexus */
    private $core;

    /** @var string|array */
    private $usedDisguise = [];

    /**
     * CommandManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
        $permManager = PermissionManager::getInstance();

        $this->unregisterCommand("about");
        $this->unregisterCommand("help");
        $this->unregisterCommand("me");
        $this->unregisterCommand("particle");
        $this->unregisterCommand("title");

        $userCommands = [
            // All Users can use but still uses permissions (for staff)
            new AuctionHouseCommand(),
            new BalanceCommand(),
            new BossCommand(),
            new BalanceTopCommand(),
            new BroadcastCommand(),
            new CalculateCommand(),
            new CoinFlipCommand(),
            new CEInfoCommand(),
            new CEShopCommand(),
            new ChangeLogCommand(),
            new ClearCommand(),
            new ClearlagCommand(),
            new EnchantCommand(),
            new GiveItemCommand(),
            new GracePeriodCommand(),
            new GlobalMuteCommand(),
            new GemShopCommand(),
            new HomeCommand(),
            new InboxCommand(),
            new InformationCommand(),
            new KitCommand(),
            new FlyCommand(),
            new FeedCommand(),
            new FreezeCommand(),
            new FactionCommand(),
            new FundCommand(),
            //new KOTHCommand(),
            new LotteryCommand(),
            new ListCommand(),
            new LobbyCommand(),
            new NightVisionCommand(),
            new LuckyBlockCommand(),
            new OnlineTimeCommand(),
            new OutpostCommand(),
            new PayCommand(),
            new PlaySoundCommand(),
            new PingCommand(),
            new PVPCommand(),
            new QuestsCommand(),
            new RenameCommand(),
            new RepairCommand(),
            new RankUpCommand(),
            new ReplyCommand(),
            new RemoveHomeCommand(),
            new RulesCommand(),
            new SellCommand(),
            new SellWandUsesCommand(),
            new SetHomeCommand(),
            new SetRankCommand(),
            new SKitCommand(),
            new ShopCommand(),
            new SpawnCommand(),  
            new StatsCommand(),
            new TestCommand(),
            new TellCommand(),
            new TellLocationCommand(),
            new TogglePrivateMessagesCommand(),
            new TeleportAskCommand(),
            new RestartCommand(),
            new TagCommand(),
            new VanishCommand(),
            new VoteCommand(),   
            new VotesCommand(),  
            new WildCommand(),
            new WithdrawCommand(),
            new PlayerVaultCommand(),  
            new XYZCommand(),  
            new TrashCommand(),
            new BlessCommand(),

            //Staff Commands
            new StaffModeCommand(),
            new StaffChatCommand(),
            new HistoryCommand(),
            new KickCommand(),
            new PunishCommand(),
            new MuteCommand(),
            new UnmuteCommand(),
            new PardonCommand(),
        ];  
        $opCommands = [
            new AddMoneyCommand(),
            new AddGemsCommand(),
            new AddXPCommand(),
            new AddPermissionCommand(),
            new FloatXYZCommand(),
            new KeyAllCommand(),
            new GiveKeysCommand(),
            new StopCommand(),
            new SacredAllCommand(),
            new SetMoneyCommand(),
            new SetGemsCommand(),
            new GodCommand(),
            // Only Operators can use
        ];
    
        $opRoot = $permManager->getPermission(DefaultPermissions::ROOT_OPERATOR);
        $everyoneRoot = $permManager->getPermission(DefaultPermissions::ROOT_USER);

        /** @var Command $command */
        foreach ($userCommands as $command) {
            $permManager->addPermission(new Permission("nexus.command." . strtolower($command->getName()), null));
            $everyoneRoot->addChild("nexus.command." . strtolower($command->getName()), true);
            $command->setPermission("nexus.command." . strtolower($command->getName()));
            $this->registerCommand($command);
        }
        foreach ($opCommands as $command) {
            $permManager->addPermission(new Permission("nexus.command." . $command->getName(), null));
            $opRoot->addChild("nexus.command." . strtolower($command->getName()), true);
            $command->setPermission("nexus.command." . strtolower($command->getName()));
            $this->registerCommand($command);
        }
    }

    /**
     * @param Command $command
     */
    public function registerCommand(Command $command): void
    {
        $commandMap = $this->core->getServer()->getCommandMap();
        $existingCommand = $commandMap->getCommand($command->getName());
        if ($existingCommand !== null) {
            $commandMap->unregister($existingCommand);
        }
        $commandMap->register($command->getName(), $command);
    }

    /**
     * @param string $name
     */
    public function unregisterCommand(string $name): void
    {
        $commandMap = $this->core->getServer()->getCommandMap();
        $command = $commandMap->getCommand($name);
        if ($command === null) {
            throw new PluginException("Invalid command: $name to un-register.");
        }
        $commandMap->unregister($commandMap->getCommand($name));
    }

    /**
     * @param array|null $disguises
     *
     * @return string|null
     */
    public function selectDisguise(?array $disguises = null): ?string
    {
        if ($disguises === null) {
            $disguises = self::DISGUISES;
        }
        if (empty($disguises)) {
            return null;
        }
        $name = array_rand($disguises);
        if (in_array($name, $this->usedDisguise)) {
            unset($disguises[$name]);
            return $this->selectDisguise($disguises);
        }
        $this->usedDisguise[] = $name;
        return $name;
    }

    /**
     * @param string $name
     */
    public function removeUsedDisguise(string $name): void
    {
        unset($this->usedDisguise[array_search($name, $this->usedDisguise)]);
    }
}