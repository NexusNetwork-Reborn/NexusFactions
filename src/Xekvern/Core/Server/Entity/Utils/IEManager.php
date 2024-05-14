<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Entity\Utils;

use pocketmine\entity\Skin;
use Xekvern\Core\Server\Entity\Utils\SkinConverter;
use Xekvern\Core\Nexus;

class IEManager
{

	/** @var Skin */
	public $skin;

	/** @var string */
	public $name;

	/** @var string */
	private $path;

	/** @var Nexus*/
	private $plugin;

	/**
	 * Manager constructor.
	 *
	 * @param Nexus $plugin
	 * @param string $path
	 */
	public function __construct(Nexus $plugin, string $path)
	{
		$this->plugin = $plugin;
		$this->path = $path;
		$this->init();
	}

	public function init(): void
	{
		$path = $this->plugin->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $this->path;
		$this->skin = SkinConverter::createSkin(SkinConverter::getSkinDataFromPNG($path));
	}
}
