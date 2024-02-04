<?php



namespace ItsToxicGG\BedWars\entities\projectiles;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\entity\projectile\Egg as PMEgg;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\math\RayTraceResult;
use pocketmine\world\particle\HeartParticle;
use ItsToxicGG\BedWars\BedWars;
use ItsToxicGG\BedWars\math\Vector3;


class Egg extends PMEgg {

    public $owner;
    
    public $team;

    public $arena;

    public $everbody = [];

    protected $gravity = 0;

    public $timer = 0;

    public $pos = 0;

    public $timerToSpawn = 0;
  


    public function onHitBlock(Block $blockHit, RayTraceResult $hitResult): void {

         $this->spawnBlock();
        
        parent::onHitBlock($blockHit, $hitResult);
    }

    public function spawnBlock(){
        $meta = [
            "red" => DyeColor::RED,
            "blue" => DyeColor::BLUE,
            "yellow" => DyeColor::YELLOW,
            "green" => DyeColor::LIME
        ];

        foreach($this->everbody as $body){
                             
        if(!$this->getWorld()->getBlockAt($body->x,$body->y,$body->z) instanceof Air){
              return;
            }
            foreach($this->arena->data["location"] as $spawn){
                $v = Vector3::fromString($spawn);
                if($body->distance($v->asVector3()) < 6){
                    return;
                }

                if(BedWars::getInstance()->isInGame($this->owner)){


                    BedWars::getInstance()->getArenaByPlayer($this->owner)->addPlacedBlock($this->getWorld()->getBlockAt($body->x,$body->y,$body->z));

                    $this->getWorld()->setBlock($body,VanillaBlocks::WOOL()->setColor($meta[$team]));
                }

                foreach($this->getWorld()->getPlayers() as $player){
                    if($player->getLocation()->distance($body) < 3) {
                        $this->addSound($player);
                    }
                }
            }
        }
    }

    public function addSound($player){
        $pk = new PlaySoundPacket();
        $pk->x = $player->getPosition()->getX();
        $pk->y = $player->getPosition()->getY();
        $pk->z = $player->getPosition()->getZ();
        $pk->volume = 100;
        $pk->pitch = 1;
        $pk->soundName = 'random.pop';
        $player->getNetworkSession()->sendDataPacket($pk);
        //Server::getInstance()->broadcastPacket($player->getWorld()->getPlayers(), $pk);
    }

    public function entityBaseTick(int $tickDiff = -1): bool
    {
        if($this->getWorld()->getBlockAt($this->getPosition()->x,$this->getPosition()->y,$this->getPosition()->z) instanceof Air){
            $this->everbody[] = $this->getPosition();
            $this->everbody[] = $this->getPosition()->add(2, 2, 2);
        }
   
            if($this->pos >= 3){
                $this->pos--;
            }
            $this->timer++;
            if($this->timer >= 40){
                $this->timer = 0;
                $this->timerToSpawn++;

            }
            if($this->timerToSpawn >= 5){
            $this->spawnBlock();
            }
   
            
        return parent::entityBaseTick($tickDiff);
    }
}
