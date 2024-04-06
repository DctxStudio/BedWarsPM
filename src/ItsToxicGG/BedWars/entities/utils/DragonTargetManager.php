<?php

declare(strict_types=1);

namespace ItsToxicGG\BedWars\entities\utils;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use ItsToxicGG\BedWars\entities\EnderDragon;
use ItsToxicGG\BedWars\Game;

/**
 * Class DragonTargetManager
 * @package vixikhd\dragons\arena
 */
class DragonTargetManager {
    public Game $game;
    public array $allBlocksInArena = [];
    public Location $mid; // Used when all the blocks are broken
    public array $dragons = [];
    public Random $random;


    public function __construct(Game $plugin, array $allBlocksInArena, Location $mid) {
        $this->game = $plugin;
        $this->allBlocksInArena = $allBlocksInArena;
        $this->mid = $mid;

        $this->random = new Random();
    }


    /**
     * @param EnderDragon $dragon
     *
     * @param int $x
     * @param int $y
     * @param int $z
     */
    public function removeBlockIfInArena(EnderDragon $dragon, int $x, int $y, int $z): void {
        $blockPos = new Vector3($x, $y, $z);
        $dragon->getWorld()->setBlock($blockPos, VanillaBlocks::AIR());

        unset($this->allBlocksInArena["$x:$y:$z"]);

        $dragon->changeRotation(true);
    }

    /**
     * @param $team
     */
    public function addDragon($team): void {
        $findSpawnPos = function (Location $mid): Location {
            $randomAngle = mt_rand(0, 359);
            $x = ((EnderDragon::MAX_DRAGON_MID_DIST - 5) * cos($randomAngle)) + $mid->getX();
            $z = ((EnderDragon::MAX_DRAGON_MID_DIST - 5) * sin($randomAngle)) + $mid->getZ();

            return Location::fromObject(new Vector3($x, $mid->getY(), $z), $mid->getWorld());
        };

        $dragon = new EnderDragon($findSpawnPos($this->mid) ,null, $this, $team);
        $dragon->lookAt($this->mid);
        $dragon->setMaxHealth(70);
        $dragon->setHealth(70);

        $dragon->spawnToAll();
    }
}