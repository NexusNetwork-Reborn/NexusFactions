<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Area;

use Xekvern\Core\Nexus;
use pocketmine\world\Position;

class AreaHandler {

    /** @var Nexus */
    private $core;

    /** @var Area[] */
    private $areas = [];

    /**
     * AreaHandler constructor.
     *
     * @param Nexus $core
     *
     * @throws AreaException
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $core->getServer()->getPluginManager()->registerEvents(new AreaEvents($core), $core);
        $this->init();
    }

    /**
     * @throws AreaException
     */
    public function init(): void {
        $this->addArea(new Area(2, "Spawn", new Position(-100000, 255, -100000, $this->core->getServer()->getWorldManager()->getDefaultWorld()), new Position(100000, 0, 100000, $this->core->getServer()->getWorldManager()->getDefaultWorld()), false, false));
        $this->addArea(new Area(1, "Safezone", new Position(669, 255, 171, $this->core->getServer()->getWorldManager()->getWorldByName("warzone")), new Position(803, 0, 305, $this->core->getServer()->getWorldManager()->getWorldByName("warzone")), false, false));
        $this->addArea(new Area(0, "Warzone", new Position(-100000, 255, -100000, $this->core->getServer()->getWorldManager()->getWorldByName("warzone")), new Position(100000, 0, 100000, $this->core->getServer()->getWorldManager()->getWorldByName("warzone")), true, false)); 
    }

    /**
     * @param Area $area
     */
    public function addArea(Area $area): void {
        $this->areas[] = $area;
    }

    /**
     * @param Position $position
     *
     * @return Area
     */
    public function getAreaByPosition(Position $position): ?Area {
        $areas = $this->getAreas();
        $areasInPosition = [];
        foreach($areas as $area) {
            if($area->isPositionInside($position) === true) {
                $areasInPosition[] = $area;
            }
        }
        if(empty($areasInPosition)) {
            return null;
        }
        if (count($areasInPosition) === 1) return $areasInPosition[0];
        $highestPriorityArea = array_shift($areasInPosition);
        foreach ($areasInPosition as $area) {
            if ($area->getPriority() > $highestPriorityArea->getPriority()) {
                $highestPriorityArea = $area;
            }
        }
        return $highestPriorityArea;
    }

    /**
     * @return Area[]
     */
    public function getAreas(): array {
        return $this->areas;
    }
}