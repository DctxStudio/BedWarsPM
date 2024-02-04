<?php

namespace ItsToxicGG\BedWars\map;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use ItsToxicGG\BedWars\Game;
use ItsToxicGG\BedWars\math\Vector3;
use pocketmine\player\Player;
use ItsToxicGG\BedWars\BedWars;
use pocketmine\block\utils\DyeColor;
class TowerSouth {

    private $arena;

    public function __construct(Game $arena)
    {
        $this->arena = $arena;
        
    }

    public function  Tower (Block $player,Player $p,$team) {
        $meta = [
                "red" => DyeColor::RED,
                "blue" => DyeColor::BLUE,
                "yellow" => DyeColor::YELLOW,
                "green" => DyeColor::LIME
            ];
         $ld1 = $player->getPosition();
         $ld2 = $player->getPosition()->add(0,1, 0);
         $ld3 = $player->getPosition()->add(0,2, 0);
         $ld4 = $player->getPosition()->add(0,3, 0);
         $ld5 = $player->getPosition()->add(0,4, 0);


         $list = [];
  
      $list[] = $player->getPosition()->add(1, 1, 2);
      $list[] = $player->getPosition()->add(2, 1, 1);
      $list[] = $player->getPosition()->add(2, 1, 0);
      $list[] = $player->getPosition()->add(1, 1, -1);
      $list[] = $player->getPosition()->add(0, 1, -1);
      $list[] = $player->getPosition()->add(-1, 1, -1);
      $list[] = $player->getPosition()->add(-2, 1, 0);
      $list[] = $player->getPosition()->add(-2, 1, 1);
      $list[] = $player->getPosition()->add(-1, 1, 2);
      

      //
      $list[] = $player->getPosition()->add(1, 2, 2);
      $list[] =  $player->getPosition()->add(2, 2, 1);
      $list[] =  $player->getPosition()->add(2, 2, 0);
      $list[] = $player->getPosition()->add(1, 2, -1);
      $list[] =  $player->getPosition()->add(0, 2, -1);
      $list[] = $player->getPosition()->add(-1, 2, -1);
      $list[] = $player->getPosition()->add(-2, 2, 0);
      $list[] = $player->getPosition()->add(-2, 2, 1);
      $list[] = $player->getPosition()->add(-1, 2, 2);
      //
      
      $list[] = $player->getPosition()->add( 0, 3, 2);
      $list[] = $player->getPosition()->add(1, 3, 2);
      $list[] = $player->getPosition()->add(2, 3, 1);
      $list[] = $player->getPosition()->add(2, 3, 0);
      $list[] = $player->getPosition()->add(1, 3, -1);
      $list[] = $player->getPosition()->add(0, 3, -1);
      $list[] = $player->getPosition()->add(-1, 3, -1);
      $list[] = $player->getPosition()->add(-2, 3, 0);
      $list[] = $player->getPosition()->add(-2, 3, 1);
      $list[] = $player->getPosition()->add(-1, 3, 2);
      
      //
      $list[] = $player->getPosition()->add(2, 4, -1);
      $list[] = $player->getPosition()->add(2, 4, 0);
      $list[] = $player->getPosition()->add(2, 4, 1);
      $list[] = $player->getPosition()->add(2, 4, 2);
      $list[] = $player->getPosition()->add(1, 4, -1);
      $list[] = $player->getPosition()->add(1, 4, 0);
      $list[] = $player->getPosition()->add(1, 4, 1);
      $list[] = $player->getPosition()->add(1, 4, 2);
      $list[] = $player->getPosition()->add(0, 4, -1);
      $list[] = $player->getPosition()->add(0, 4, 1);
      $list[] = $player->getPosition()->add(0, 4, 2);
      $list[] = $player->getPosition()->add(-1, 4, -1);
      $list[] = $player->getPosition()->add(-1, 4, 0);
      $list[] = $player->getPosition()->add(-1, 4, 1);
      $list[] = $player->getPosition()->add(-1, 4, 2);
      $list[] = $player->getPosition()->add(-2, 4, -1);
      $list[] = $player->getPosition()->add(-2, 4, 0);
      $list[] = $player->getPosition()->add(-2, 4, 1);
      $list[] = $player->getPosition()->add(-2, 4, 2);
      //

      $list[] = $player->getPosition()->add(3, 4, 2);
      $list[] = $player->getPosition()->add(3, 5, 2);
      $list[] = $player->getPosition()->add(3, 6, 2);
      $list[] = $player->getPosition()->add(3, 5, 1);
      $list[] = $player->getPosition()->add(3, 5, 0);
      $list[] = $player->getPosition()->add(3, 4, -1);
      $list[] = $player->getPosition()->add(3, 5, -1);
      $list[] = $player->getPosition()->add(3, 6, -1);
      $list[] = $player->getPosition()->add(-3, 4, 2);
      $list[] = $player->getPosition()->add(-3, 5, 2);
      $list[] = $player->getPosition()->add(-3, 6, 2);
      $list[] = $player->getPosition()->add(-3, 5, 1);
      $list[] = $player->getPosition()->add(-3, 5, 0);
      $list[] = $player->getPosition()->add(-3, 4, -1);
      $list[] = $player->getPosition()->add(-3, 5, -1);
      $list[] = $player->getPosition()->add(-3, 6, -1);
      $list[] = $player->getPosition()->add( 2, 4, -2);
      $list[] = $player->getPosition()->add(2, 5, -2);
      $list[] = $player->getPosition()->add( 2, 6, -2);
      $list[] = $player->getPosition()->add(1, 5, -2);
      $list[] = $player->getPosition()->add(0, 4, -2);
      $list[] = $player->getPosition()->add(0, 5, -2);
      $list[] = $player->getPosition()->add(0, 6, -2);
      $list[] = $player->getPosition()->add(-1, 5, -2);
      $list[] = $player->getPosition()->add(-2, 4, -2);
      $list[] = $player->getPosition()->add(-2, 5, -2);
      $list[] = $player->getPosition()->add(-2, 6, -2);
      $list[] = $player->getPosition()->add(2, 4, 3);
      $list[] = $player->getPosition()->add(2, 5, 3);
      $list[] = $player->getPosition()->add(2, 6, 3);
      $list[] = $player->getPosition()->add(1, 5, 3);
      $list[] = $player->getPosition()->add(0, 4, 3);
      $list[] = $player->getPosition()->add(0, 5, 3);
      $list[] = $player->getPosition()->add(0, 6, 3);
      $list[] = $player->getPosition()->add(-1, 5, 3);
      $list[] = $player->getPosition()->add(-2, 4, 3);
      $list[] = $player->getPosition()->add(-2, 5, 3);
      $list[] = $player->getPosition()->add(-2, 6, 3);
        $ladermeta = 3;
                foreach($list as $pe){
                        if($player->getPosition()->getWorld()->getBlockAt($pe->getX(),$pe->getY(),$pe->getZ())->getId() == 0){
                             BedWars::getInstance()->getArenaByPlayer($p)->addPlacedBlock($p->getPosition()->getWorld()->getBlockAt($pe->getX(),$pe->getY(),$pe->getZ()));
                             $player->getPosition()->getWorld()->setBlock($pe,VanillaBlocks::WOOL()->setColor($meta));
                        }

                }

                 if($player->getPosition()->getWorld()->getBlockat($ld1->x,$ld1->y,$ld1->z)->getId() == 0){
                         $p->getPosition()->getWorld()->setBlock($ld1,\pocketmine\block\VanillaBlocks::LADDER(),true,true);
                          BedWars::getInstance()->getArenaByPlayer($p)->addPlacedBlock($p->getPosition()->getWorld()->getBlockAt($ld1->x,$ld1->y,$ld1->z));

                     }
                 if($player->getPosition()->getWorld()->getBlockat($ld2->x,$ld2->y,$ld2->z)->getId() == 0){
                         $p->getPosition()->getWorld()->setBlock($ld2,\pocketmine\block\VanillaBlocks::LADDER(),true,true);
                         BedWars::getInstance()->getArenaByPlayer($p)->addPlacedBlock($p->getPosition()->getWorld()->getBlockAt($ld2->x,$ld2->y,$ld2->z));
                 }
                 if($player->getPosition()->getWorld()->getBlockat($ld3->x,$ld3->y,$ld3->z)->getId() == 0){
                         $p->getPosition()->getWorld()->setBlock($ld3,\pocketmine\block\VanillaBlocks::LADDER(),true,true);
                         BedWars::getInstance()->getArenaByPlayer($p)->addPlacedBlock($p->getPosition()->getWorld()->getBlockAt($ld3->x,$ld3->y,$ld3->z));

                 }
                 if($player->getPosition()->getWorld()->getBlockat($ld4->x,$ld4->y,$ld4->z)->getId() == 0){
                         $p->getPosition()->getWorld()->setBlock($ld4,\pocketmine\block\VanillaBlocks::LADDER(),true,true);
                         BedWars::getInstance()->getArenaByPlayer($p)->addPlacedBlock($p->getPosition()->getWorld()->getBlockAt($ld4->x,$ld4->y,$ld4->z));
                 }
                 if($player->getPosition()->getWorld()->getBlockat($ld5->x,$ld5->y,$ld5->z)->getId() == 0){
                         $p->getPosition()->getWorld()->setBlock($ld5,\pocketmine\block\VanillaBlocks::LADDER(),true,true);
                         BedWars::getInstance()->getArenaByPlayer($p)->addPlacedBlock($p->getPosition()->getWorld()->getBlockAt($ld5->x,$ld5->y,$ld5->z));
                 }


    }


                          
}