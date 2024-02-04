<?php

declare(strict_types=1);

namespace ItsToxicGG\BedWars\entities;

use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\block\Chest;
use pocketmine\block\EnderChest;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\timings\Timings;
use ItsToxicGG\BedWars\entities\utils\DragonTargetManager;
use ItsToxicGG\BedWars\math\Math;

/**
 * Class EnderDragon
 * - This class has 2x smaller bounding box
 *
 * @package vixikhd\dragons\entity
 */
class EnderDragon extends Living {

    public const MAX_DRAGON_MID_DIST = 100; // Dragon will rotate when will be distanced 64 blocks from map center
    PUBLIC CONST DRAGON_WIDTH = 8.0;
    PUBLIC CONST DRAGON_HEIGHT = 4.0;

    public static function getNetworkTypeId() : string{ return EntityIds::ENDER_DRAGON; }

    public ?DragonTargetManager $targetManager;

	public int $mid;
    public int $rotationTicks = 0;
    public int $rotationChange; // from 5 to 10 (- or +)
    public int $pitchChange;

    public float $lastRotation = 0.0;
    public string $team;

    public bool $isRotating = false;

    /**
     * EnderDragon constructor.
     *
     * @param Location $location
     * @param CompoundTag|null $nbt
     * @param DragonTargetManager|null $targetManager
     * @param string $team
     */
    public function __construct(Location $location, ?CompoundTag $nbt, ?DragonTargetManager $targetManager, string $team) {
        parent::__construct($location, $nbt);
        $this->targetManager = $targetManager;
        $this->team = $team;

        $this->targetManager ?? $this->flagForDespawn();
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(self::DRAGON_HEIGHT, self::DRAGON_WIDTH);
    }

    public function changeRotation(bool $canStart = false) {
        // checks for new rotation
        if(!$this->isRotating) {
            if(!$canStart) {
                return;
            }

            if(microtime(true)-$this->lastRotation < 10) {
                return;
            }

            $this->rotationChange = mt_rand(5, 30);
            if(mt_rand(0, 1) === 0) {
                $this->rotationChange *= -1;
            }
            $this->pitchChange = mt_rand(-4, 4);

            $this->isRotating = true;
        }
     
        // checks for rotation cancel
        if($this->rotationTicks > mt_rand(5, 8)) {
            $this->lastRotation = microtime(true);
            $this->isRotating = false;
            return;
        }
        $yaw = ($this->getLocation()->getYaw() + ($this->rotationChange / 3)) % 360;
        $pitch = ($this->getLocation()->getPitch() + ($this->pitchChange / 10)) % 360;
        $this->setRotation($yaw, $pitch);
        $this->rotationTicks++;
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool { // TODO - make better movement system
        $return = parent::entityBaseTick($tickDiff);
        if($this->targetManager === null) {
            $this->flagForDespawn();
            return false;
        }

        # $blocks = array_values($this->targetManager->allBlocksInArena);
        # $time = $this->targetManager->game->scheduler->suddendeath[$this->targetManager->game->data["level"]];
        $red = $this->targetManager->game->data["location"]["red"];
        $blue = $this->targetManager->game->data["location"]["blue"];
        $yellow = $this->targetManager->game->data["location"]["yellow"];
        $green = $this->targetManager->game->data["location"]["green"];
        $corner1 = $this->targetManager->game->data["corner1"];
        $game = $this->targetManager->game;

        if ($this->getLocation()->distance($this->targetManager->mid) >= EnderDragon::MAX_DRAGON_MID_DIST || $this->getLocation()->getY() < 4 || $this->getLocation()->getY() > 250) {
	       if($this->team == "green"){
			   $loc = $game->calculate($corner1, $yellow);
			   $this->targetManager->mid = Location::fromObject($loc, $this->getWorld());
				$this->lookAt($loc);
				$this->setMotion($this->getDirectionVector());
			}
	       if($this->team == "yellow"){
			   $loc = $game->calculate($red, $green);
			   $this->targetManager->mid = Location::fromObject($loc, $this->getWorld());
			   $this->lookAt($loc);
			   $this->setMotion($this->getDirectionVector());
		   }
	       if($this->team == "blue"){
			   $loc = $game->calculate($yellow, $blue);
			   $this->targetManager->mid = Location::fromObject($loc, $this->getWorld());
			   $this->lookAt($loc);
			   $this->setMotion($this->getDirectionVector());
		   }
	       if($this->team == "red"){
			   $loc = $game->calculate($green, $yellow);
			   $this->targetManager->mid = Location::fromObject($loc, $this->getWorld());
			   $this->lookAt($loc);
			   $this->setMotion($this->getDirectionVector());
		   }
        }

        $this->changeRotation();
		$this->setMotion($this->getDirectionVector());


        return $return;
    }

    /**
     * Function copied from PocketMine (api missing - setting entity noclip)
     *
     * @param float $dx
     * @param float $dy
     * @param float $dz
     */
    public function move(float $dx, float $dy, float $dz): void {
        $this->blocksAround = null;

        Timings::$entityMove->startTiming();

        $movX = $dx;
        $movY = $dy;
        $movZ = $dz;

        if($this->keepMovement){
            $this->boundingBox->offset($dx, $dy, $dz);
        }else{
            $this->ySize *= 0.4;

            $axisalignedbb = clone $this->boundingBox;

            assert(abs($dx) <= 20 and abs($dy) <= 20 and abs($dz) <= 20, "Movement distance is excessive: dx=$dx, dy=$dy, dz=$dz");

            $list = $this->getWorld()->getCollisionBoxes($this, $this->getWorld()->getTickRateTime() > 50 ? $this->boundingBox->offsetCopy($dx, $dy, $dz) : $this->boundingBox->addCoord($dx, $dy, $dz), false);
            foreach ($list as $bb) {
                $blocks = $this->getWorld()->getBlockAt((int)$bb->minX, (int)$bb->minY, (int)$bb->minZ);
                if(!$blocks instanceof EnderChest && !$blocks instanceof Chest){
                $this->targetManager->removeBlockIfInArena($this, (int)$bb->minX, (int)$bb->minY, (int)$bb->minZ);
                }
            }

            $this->boundingBox->offset(0, $dy, 0); // x
            $fallingFlag = ($this->onGround or ($dy != $movY and $movY < 0));
            $this->boundingBox->offset($dx, 0, 0); // y
            $this->boundingBox->offset(0, 0, $dz); // z

            if($this->stepHeight > 0 and $fallingFlag and $this->ySize < 0.05 and ($movX != $dx or $movZ != $dz)){
                $cx = $dx;
                $cy = $dy;
                $cz = $dz;
                $dx = $movX;
                $dy = $this->stepHeight;
                $dz = $movZ;

                $axisalignedbb1 = clone $this->boundingBox;

                $this->boundingBox = $axisalignedbb;

                foreach (Math::getCollisionBlocks($this->getWorld(), $this->boundingBox->addCoord($dx, $dy, $dz)) as $block) {
                    $this->targetManager->removeBlockIfInArena($this, $block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ());
                }

                $this->boundingBox->offset(0, $dy, 0);
                $this->boundingBox->offset($dx, 0, 0);
                $this->boundingBox->offset(0, 0, $dz);

                if(($cx ** 2 + $cz ** 2) >= ($dx ** 2 + $dz ** 2)){
                    $dx = $cx;
                    $dy = $cy;
                    $dz = $cz;
                    $this->boundingBox = $axisalignedbb1;
                }
                else {
                    $this->ySize += 0.5;
                }
            }
        }

        $this->getLocation()->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
        $this->getLocation()->y = $this->boundingBox->minY - $this->ySize;
        $this->getLocation()->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

        $this->getWorld()->onEntityMoved($this);
        $this->checkBlockIntersections();
        $this->checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz);
        $this->updateFallState($dy, $this->onGround);

        if($movX != $dx){
            $this->motion->x = 0;
        }
        if($movY != $dy){
            $this->motion->y = 0;
        }
        if($movZ != $dz){
            $this->motion->z = 0;
        }

        Timings::$entityMove->stopTiming();
    }

    /**
     * Wtf mojang
     * - Function edited to send +180 yaw
     *
     * @param bool $teleport
     */
    protected function broadcastMovement(bool $teleport = false) : void{
        $entityRuntimeId = $this->id;
        $position = $this->getOffsetPosition($this->getPosition());
        $loc = $this->getLocation();

        //this looks very odd but is correct as of 1.5.0.7
        //for arrows this is actually x/y/z rotation
        //for mobs x and z are used for pitch and yaw, and y is used for headyaw
        $xRot = $loc->getPitch();
        $yRot = ($loc->getYaw() + 180) % 360; //TODO: head yaw
        $zRot = ($loc->getYaw() + 180) % 360;

        $pk = MoveActorAbsolutePacket::create($entityRuntimeId, $position, $xRot, $yRot, $zRot, 0);

        if($teleport){
            $pk->flags |= MoveActorAbsolutePacket::FLAG_TELEPORT;
        }

        $this->getWorld()->broadcastPacketToViewers($this->getLocation(), $pk);
    }

    /**
     * @param EntityDamageEvent $source
     */
 

    /**
     * @param Player $player
     */
    public function onCollideWithPlayer(Player $player): void {
        $player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 0.5));

        parent::onCollideWithPlayer($player);
    }

    /**
     * @param int $seconds
     */
    public function setOnFire(int $seconds): void {}

    /**
     * @return string
     */
    public function getName(): string {
        return "Ender Dragon";
    }

}
