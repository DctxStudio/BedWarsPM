<?php

declare(strict_types=1);

namespace ItsToxicGG\BedWars\entities;

use pocketmine\entity\Human;
use pocketmine\entity\Villager;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use ItsToxicGG\BedWars\Game;

class UpgradeVillager extends Villager {

    public $arena;

    public function getName(): string {
        return "UpgradeVillager";
    }

    public function initEntity(CompoundTag $nbt): void{
        parent::initEntity($nbt);
        $this->setNametag("§bTEAM\n§bUPGRADE\n§r§eLEFT CLICK");
        $this->setNametagAlwaysVisible(true);
    }

    public function attack(EntityDamageEvent $source): void {
        $event = $source;
        $event->cancel();
        $player = $source->getEntity();
        $arena =  $this->arena;

        if(!$arena instanceof Game){
            return;
        }

        if($arena->phase == $arena::PHASE_GAME){
            if($event instanceof EntityDamageByEntityEvent){
                if($event->getCause() == $source::CAUSE_ENTITY_ATTACK) {
                    $dmg = $event->getDamager();

                    if($dmg instanceof Player){
                        if(!isset($this->arena->spectators[$dmg->getName()])) {
                            if ($this->arena->inGame($dmg)) {
                                $this->arena->upgradeMenu($dmg);
                                $player->setHealth(20);
                                $event->cancel();
                            }
                        }
                    }
                }
            } else {
                $event->cancel();
            }
        }

    }
}
