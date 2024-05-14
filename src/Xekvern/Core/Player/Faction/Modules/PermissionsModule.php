<?php
declare(strict_types=1);

namespace Xekvern\Core\Player\Faction\Modules;

use Xekvern\Core\Player\NexusPlayer;
use libs\utils\UtilsException;
use Xekvern\Core\Player\Faction\Utils\FactionException;
use Xekvern\Core\Player\Faction\Faction;

class PermissionsModule
{

    const PERMISSION_ALLY = "Ally";
    const PERMISSION_DEPOSIT = "Deposit";
    const PERMISSION_WITHDRAW = "Withdraw";
    const PERMISSION_INVITE = "Invite";
    const PERMISSION_CLAIM = "Claim";
    const PERMISSION_OVER_CLAIM = "Overclaim";
    const PERMISSION_USE_VAULT = "Vault";
    const PERMISSION_EDIT_CLAIMS = "Edit claims";
    const PERMISSION_ACCESS_HOME = "Access home";
    const PERMISSION_ACCESS_CONTAINERS = "Access containers";
    const PERMISSION_SEE_PAYOUT_EMAIL = "See payout email";

    const DEFAULTS = [
        self::PERMISSION_ALLY => Faction::OFFICER,
        self::PERMISSION_DEPOSIT => Faction::MEMBER,
        self::PERMISSION_WITHDRAW => Faction::OFFICER,
        self::PERMISSION_INVITE => Faction::OFFICER,
        self::PERMISSION_CLAIM => Faction::OFFICER,
        self::PERMISSION_OVER_CLAIM => Faction::LEADER,
        self::PERMISSION_USE_VAULT => Faction::OFFICER,
        self::PERMISSION_EDIT_CLAIMS => Faction::MEMBER,
        self::PERMISSION_ACCESS_HOME => Faction::MEMBER,
        self::PERMISSION_ACCESS_CONTAINERS => Faction::MEMBER,
        self::PERMISSION_SEE_PAYOUT_EMAIL => Faction::LEADER
    ];

    /** @var Faction */
    private $faction;

    /** @var int[] */
    private $permissions;

    /**
     * PermissionManager constructor.
     *
     * @param Faction $faction
     * @param array $permissions
     *
     * @throws FactionException
     */
    public function __construct(Faction $faction, array $permissions)
    {
        $this->faction = $faction;
        $this->permissions = $this->validate($permissions);
    }

    /**
     * @param array $permissions
     *
     * @return int[]
     *
     * @throws FactionException
     */
    public function validate(array $permissions): array
    {
        foreach (self::DEFAULTS as $permission => $fallback) {
            if (!isset($permissions[$permission])) {
                $permissions[$permission] = $fallback;
            }
            if ($permissions[$permission] < Faction::RECRUIT or $permissions[$permission] > Faction::LEADER) {
                throw new FactionException("Invalid role \"$permissions[$permission]\" for permission: #$permission");
            }
        }
        return $permissions;
    }

    /**
     * @param string $permission
     * @param int $value
     *
     * @throws FactionException
     * @throws UtilsException
     */
    public function setValue(string $permission, int $value): void
    {
        if ($value < Faction::RECRUIT or $value > Faction::LEADER) {
            throw new FactionException("Invalid role \"$value\" for permission: #$permission");
        }
        $this->permissions[$permission] = $value;
    }

    /**
     * @param NexusPlayer $player
     * @param string $permission
     *
     * @return bool
     */
    public function hasPermission(NexusPlayer $player, string $permission): bool
    {
        if (!isset($this->permissions[$permission])) {
            $this->permissions[$permission] = self::DEFAULTS[$permission];
        }
        return $player->getDataSession()->getFactionRole() >= $this->permissions[$permission];
    }

    /**
     * @return int[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
}