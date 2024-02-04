<?php

declare(strict_types=1);

namespace ItsToxicGG\BedWars\task;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use ItsToxicGG\BedWars\BedWars;
use ItsToxicGG\BedWars\math\Time;
use ItsToxicGG\BedWars\math\Vector3;
use ItsToxicGG\BedWars\Game;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;


class TaskTick extends Task {


    protected $plugin;
    
    /** @var int $waitTime */
    public $waitTime = [];
    /**
     * @var int
     */
    public $kedip2 = 0;
    /**
     * @var array
     */
    public $upgradeNext = [];
    /**
     * @var array
     */
    public $upgradeTime = [];
    
    public $bedgone = [];
    public $suddendeath = [];
    /**
     * @var array
     */
    public $gameover = [];

    /** @var int $restartTime */
    public $kedip1 = 0;
    public $restartTime = [];
    public $kedip = 0;
    public $kedip3 = 0;

    /** @var array $restartData */
    public $restartData = [];

    public $dragon;

    /**
     * TaskTick constructor.
     * @param Game $plugin
     */
    public function __construct(Game $plugin) {
        $this->plugin = $plugin;
    }
    
    public function addsound($player, string $sound, float $pitch = 1): bool
	{
        $pk = new PlaySoundPacket();
        $pk->x = $player->getPosition()->getX();
        $pk->y = $player->getPosition()->getY();
        $pk->z = $player->getPosition()->getZ();
        $pk->volume = 100;
        $pk->pitch = $pitch;
        $pk->soundName = $sound;
        $player->getNetworkSession()->sendDataPacket($pk);
        return true;
    }

    public function counter(Player $player,string $type)
    {
    	if($type == "fk"){
    		if(isset($this->plugin->finalkill[$player->getId()])){
    			return $this->plugin->finalkill[$player->getId()];
			}
		}
    	if($type == "kill"){
    		if(isset($this->plugin->kill[$player->getId()])){
               return $this->plugin->kill[$player->getId()];

			}
		}
    	if($type == "broken"){
    		if(isset($this->plugin->broken[$player->getId()])){
    			return $this->plugin->broken[$player->getId()];
			}

		}
    	return "";
	}


    public function onRun(): void
    {

        $redteam = [
            "red" => "§7YOU",
            "blue" => "",
            "yellow" => " ",
            "green" => "",
        ];
        $blueteam = [
            "red" => "",
            "blue" => "§7YOU",
            "yellow" => "",
            "green" => "",
        ];
        $yellowteam = [
            "red" => "",
            "blue" => "",
            "yellow" => "§7YOU",
            "green" => "",
        ];
        $greenteam = [
            "red" => "",
            "blue" => "",
            "yellow" => "",
            "green" => "§7YOU",
        ];
        $text = "§l§eBEDWARS";
        if(!$this->plugin->setup) {
            switch ($this->plugin->phase) {
                case Game::PHASE_LOBBY:
                    if(count($this->plugin->players) >= 2) {
                        $time = $this->waitTime[$this->plugin->data["level"]];
                        if($time > 0){
                            $this->waitTime[$this->plugin->data["level"]] -= 1;
                            foreach($this->plugin->players as $player){
                                if(!$player->isOnline()){
                                    continue;
                                }
                                $api = BedWars::getScore();
                                $api->new($player, "ObjectiveName", $text);
                                $api->setLine($player, 1, "§f");
                                $api->setLine($player, 2, "§fMap: §a".$this->plugin->world->getFolderName());
                                $api->setLine($player, 3, "§fPlayers: §a" .  count($this->plugin->players) . "/{$this->plugin->data["slots"]}");
                                $api->setLine($player, 4, "           ");
                                $api->setLine($player, 5, "§fStarting in: §a".$this->waitTime[$this->plugin->data["level"]]. "s");
                                $api->setLine($player, 6, "   ");
                                $api->setLine($player, 7, "§fMode: §a4v4v4v4");
                                $api->setLine($player, 8, "                 ");
                                $api->setLine($player, 9, "§eNordicNetwork");
                            }
                        }
                        if ($this->waitTime[$this->plugin->data["level"]] == 5) {
                            $this->plugin->broadcastMessage("§eThe game has starts in §c5 §eseconds!");
                            foreach ($this->plugin->players as $players) {
                                $this->addSound($players, 'random.toast', 1.5);
                                $players->sendTitle("§c5");
                            }
                        }
                        if ($this->waitTime[$this->plugin->data["level"]] == 4) {
                            $this->plugin->broadcastMessage("§eThe game has starts in §c4 §eseconds!");
                            foreach ($this->plugin->players as $players) {
                                $this->addSound($players, 'random.toast', 1.5);
                                $players->sendTitle("§c4");
                            }
                        }
                        if ($this->waitTime[$this->plugin->data["level"]] == 3) {
                            $this->plugin->broadcastMessage("§eThe game has starts in §c3 §eseconds!");
                            foreach ($this->plugin->players as $players) {
                                $this->addSound($players, 'random.toast', 1.5);
                                $players->sendTitle("§c3");
                            }
                        }
                        if ($this->waitTime[$this->plugin->data["level"]] == 2) {
                            $this->plugin->broadcastMessage("§eThe game has starts in §c2 §eseconds!");
                            foreach ($this->plugin->players as $players) {
                                $this->addSound($players, 'random.toast', 1.5);
                                $players->sendTitle("§c2");
                            }
                        }
                        if ($this->waitTime[$this->plugin->data["level"]] == 1) {
                            $this->plugin->broadcastMessage("§eThe game has starts in §c1 §eseconds!");
                            foreach ($this->plugin->players as $players) {
                                $this->addSound($players, 'random.toast', 1.5);
                                $players->sendTitle("§c1");
                            }
                        }
                        if($this->waitTime[$this->plugin->data["level"]] == 0){
                            foreach($this->plugin->players as $players){
                                $players->sendMessage("§cCross teaming is not allowed. You will get temporarily even permanently ban.\n\n§aYou can shout message to all players. type ! at first character on chat!. (ex: hello world!)");
                                $this->plugin->startGame();
                            }
                        }
                    } else {
                        foreach($this->plugin->players as $player){
                            var_dump($player->getName());
                            $api = BedWars::getScore();
                            $api->new($player, "ObjectiveName", $text);
                            $api->setLine($player, 1, "§f");
                            $api->setLine($player, 2, "§fMap: §a".$this->plugin->world->getFolderName());
                            $api->setLine($player, 3, "§fPlayers: §a" .  count($this->plugin->players) . "/{$this->plugin->data["slots"]}");
                            $api->setLine($player, 4, "           ");
                            $api->setLine($player, 5, "§fWaiting...");
                            $api->setLine($player, 6, "   ");
                            $api->setLine($player, 7, "§fMode: §a4v4v4v4");
                            $api->setLine($player, 8, "                 ");
                            $api->setLine($player, 9, "§eNordicNetwork");
                            $this->waitTime[$this->plugin->data["level"]] = 30;
                        }
                    }
                    break;
                case Game::PHASE_GAME:
                    $this->plugin->world->setTime(5000);
                    $events = "";
                    if($this->upgradeNext[$this->plugin->data["level"]] <= 4){
                        $this->upgradeTime[$this->plugin->data["level"]] -=  1;
                        if($this->upgradeNext[$this->plugin->data["level"]] == 1){
                            $events = "§fDiamond II in: §a" . Time::calculateTime($this->upgradeTime[$this->plugin->data["level"]]) . "";
                        }
                        if($this->upgradeNext[$this->plugin->data["level"]] == 2){
                            $events = "§fEmerald II in: §a" . Time::calculateTime($this->upgradeTime[$this->plugin->data["level"]]) . "";
                        }
                        if($this->upgradeNext[$this->plugin->data["level"]] == 3){
                            $events = "§fDiamond III in: §a" . Time::calculateTime($this->upgradeTime[$this->plugin->data["level"]]) . "";
                        }
                        if($this->upgradeNext[$this->plugin->data["level"]] == 4){
                            $events = "§fEmerald III in: §a" . Time::calculateTime($this->upgradeTime[$this->plugin->data["level"]]) . "";
                        }
                        if($this->upgradeTime[$this->plugin->data["level"]] == (0.0 * 60)){
                            $this->upgradeTime[$this->plugin->data["level"]] = 5 * 60;
                            if($this->upgradeNext[$this->plugin->data["level"]] == 1){
                                $this->plugin->broadcastMessage("§bDiamond Generators §ahas been upgraded to Tier §eII");
                                $this->plugin->upgradeGeneratorTier("diamond", 2);
                                foreach($this->plugin->players as $player){
                                    $this->plugin->addexp($player);
                                }

                            }
                            if($this->upgradeNext[$this->plugin->data["level"]] == 2){
                                $this->plugin->broadcastMessage("§2Emerald Generators §ahas been upgraded to Tier §eII");
                                $this->plugin->upgradeGeneratorTier("emerald", 2);

                            }
                            if($this->upgradeNext[$this->plugin->data["level"]] == 3){
                                $this->plugin->broadcastMessage("§bDiamond Generators §ahas been upgraded to Tier §eIII");
                                $this->plugin->upgradeGeneratorTier("diamond", 3);
                                foreach($this->plugin->players as $player){
                                    $this->plugin->addexp($player);
                                }

                            }
                            /**
                             *
                             */
                            if($this->upgradeNext[$this->plugin->data["level"]] == 4){
                                $this->plugin->broadcastMessage("§2Emerald Generators §ahas been upgraded to Tier §eIII");
                                $this->plugin->upgradeGeneratorTier("emerald", 3);

                            }
                            $this->upgradeNext[$this->plugin->data["level"]]++;
                        }
                    } else {
                        if($this->bedgone[$this->plugin->data["level"]] > (-1.0 * 60)){
                            $this->bedgone[$this->plugin->data["level"]] -=  1;
                            $events = "§fBedgone in: §a" . Time::calculateTime($this->bedgone[$this->plugin->data["level"]]) . "";
                        }
                        if($this->upgradeNext[$this->plugin->data["level"]] == 6){
                            $this->suddendeath[$this->plugin->data["level"]] -=  1;

                            $events = "§fSudden Death in: §a" . Time::calculateTime($this->suddendeath[$this->plugin->data["level"]]) . "";
                        }
                        if($this->bedgone[$this->plugin->data["level"]] == (0.0 * 60)){
                            if($this->upgradeNext[$this->plugin->data["level"]] == 5){
                                $this->plugin->destroyAllBeds();
                                $this->upgradeNext[$this->plugin->data["level"]] = 6;
                                $this->suddendeath[$this->plugin->data["level"]] -=  1;
                            }
                            $this->plugin->world->setTime(5000);
                            foreach($this->plugin->players as $player){
                                $this->plugin->addexp($player);
                            }
                        }

                        if($this->suddendeath[$this->plugin->data["level"]] == (0.1 * 60)){

                            if($this->upgradeNext[$this->plugin->data["level"]] == 6){
                                $this->upgradeNext[$this->plugin->data["level"]] = 7;

                                $this->plugin->dragon();
                            }
                        }

                        if($this->upgradeNext[$this->plugin->data["level"]] == 7){
                            $this->gameover[$this->plugin->data["level"]] -=  1;
                            $events = "§fGame end in: §a" . Time::calculateTime($this->gameover[$this->plugin->data["level"]]) . "";
                        }

                        if($this->gameover[$this->plugin->data["level"]] == (0.1 * 60)){
                            $this->upgradeNext[$this->plugin->data["level"]] = 8;
                            $this->plugin->draw();
                            foreach($this->plugin->players as $player){
                                $this->plugin->addexp($player);
                            }
                        }
                    }

                    foreach($this->plugin->players as $r) {
                        if(isset($this->plugin->respawnC[$r->getName()])){
                            if($this->plugin->respawnC[$r->getName()] <= 1) {
                                unset($this->plugin->respawnC[$r->getName()]);
                                $this->plugin->respawn($r);
                            } else {
                                $this->plugin->respawnC[$r->getName()]--;
                                $r->sendSubtitle("§eYou will respawn in §c{$this->plugin->respawnC[$r->getName()]} §eseconds!");
                                $r->sendMessage("§eYou will respawn in §c{$this->plugin->respawnC[$r->getName()]} §eseconds!");

                            }
                        }

                    }

                    foreach($this->plugin->players as $milk){
                        if(isset($this->plugin->milk[$milk->getId()])){
                            if($this->plugin->milk[$milk->getId()] <= 0) {
                                unset($this->plugin->milk[$milk->getId()]);
                            } else {
                                $this->plugin->milk[$milk->getId()]--;
                            }
                        }
                    }

                    foreach($this->plugin->players as $pt){
                        $team = $this->plugin->getTeam($pt);
                        $pos = Vector3::fromString($this->plugin->data["bed"][$team]);
                        if(isset($this->plugin->utilities[$this->plugin->world->getFolderName()][$team]["haste"])){
                            if($this->plugin->getTeam($pt) == $team){
                                if($this->plugin->utilities[$this->plugin->world->getFolderName()][$team]["haste"] > 1){
                                    $eff = new EffectInstance(VanillaEffects::HASTE(), 60, ($this->plugin->utilities[$this->plugin->world->getFolderName()][$team]["haste"]  - 2));
                                    $eff->setVisible(false);
                                    $pt->getEffects()->add($eff);
                                }
                            }
                        }

                        if(isset($this->plugin->utilities[$this->plugin->world->getFolderName()][$team]["health"])){
                            if($this->plugin->getTeam($pt) == $team){
                                if($this->plugin->utilities[$this->plugin->world->getFolderName()][$team]["health"] > 1){
                                    if($pt->getLocation()->distance($pos) < 10){
                                        $eff = new EffectInstance(VanillaEffects::REGENERATION(), 60, 0);
                                        $eff->setVisible(false);
                                        $pt->getEffects()->add($eff);
                                    }
                                }
                            }
                        }
                    }

                    foreach (array_merge($this->plugin->players, $this->plugin->spectators) as $player) {
                        $player->setScoreTag("§f{$player->getHealth()} §c§l❤️");
                        $team =  $this->plugin->getTeam($player);
                        if(!$player->isOnline()){
                        }
                        if($team == ""){
                        }
                        if(!$player->getEffects()->has(VanillaEffects::INVISIBILITY())){
                            if(isset($this->invis[$player->getId()])){
                                $this->plugin->setInvis($player, false);
                            }
                        }
                        $api = BedWars::getScore();
                        $player->getHungerManager()->setFood(20);
                        $kills = $this->counter($player,"kill");
                        $fkills = $this->counter($player,"fk");
                        $broken = $this->counter($player,"broken");
                        $api->new($player, "ObjectiveName", $text);
                        $api->setLine($player, 1, "        ");
                        $api->setLine($player, 2,  $events);
                        $api->setLine($player, 3, "§b§b§b ");
                        $api->setLine($player, 4, "§l§cR§r §fRed   {$this->plugin->statusTeam("red")}  {$redteam[$team]}");
                        $api->setLine($player, 5, "§l§9B§r §fBlue {$this->plugin->statusTeam("blue")}  {$blueteam[$team]}");
                        $api->setLine($player, 6, "§l§eY§r §fYellow {$this->plugin->statusTeam("yellow")} {$yellowteam[$team]}");
                        $api->setLine($player, 7, "§l§aG§r §fGreen  {$this->plugin->statusTeam("green")} {$greenteam[$team]}");
                        $api->setLine($player, 8, "§b§b§b  ");
                        $api->setLine($player, 9, "§fKills: §a{$kills}");
                        $api->setLine($player, 10, "§fFinal Kills: §a{$fkills}");
                        $api->setLine($player, 11, "§fBed Broken: §a{$broken}");
                        $api->setLine($player, 12, "  ");
                        $api->setLine($player, 13, "§eNordicNetwork");
                        $api->getObjectiveName($player);


                    }

                    $redcount = $this->plugin->getCountTeam("red");
                    $aquacount = $this->plugin->getCountTeam("blue");
                    $yellowcount =  $this->plugin->getCountTeam("yellow");
                    $limecount =  $this->plugin->getCountTeam("green");
                    if($redcount <= 0 && $aquacount <= 0 && $yellowcount <= 0){
                        $this->plugin->Wins("green");
                    }
                    if($limecount <= 0 && $aquacount <= 0 && $yellowcount <= 0){
                        $this->plugin->Wins("red");
                    }
                    if($redcount <= 0 && $aquacount <= 0 && $limecount <= 0){
                        $this->plugin->Wins("yellow");
                    }
                    if($redcount <= 0 && $limecount <= 0 && $yellowcount <= 0){
                        $this->plugin->Wins("blue");
                    }

                    break;
                case Game::PHASE_RESTART:
                    $this->restartTime[$this->plugin->data["level"]] -=  1;
                    foreach (array_merge($this->plugin->players, $this->plugin->spectators) as $player) {
                        if(!$player->isOnline()){
                        }
                        $api = BedWars::getScore();
                        $api->new($player, "ObjectiveName", $text);
                        $api->setLine($player, 1, "§f");
                        $api->setLine($player, 2, "§fMap: §a" . $this->plugin->world->getFolderName());
                        $api->setLine($player, 3, "§fPlayers: §a" . count($this->plugin->players) . "/{$this->plugin->data["slots"]}");
                        $api->setLine($player, 4, "           ");
                        $api->setLine($player, 5, "§fRestarting in: §a" . $this->restartTime[$this->plugin->data["level"]]. "s");
                        $api->setLine($player, 6, "   ");
                        $api->setLine($player, 7, "§fMode: §a4v4v4v4");
                        $api->setLine($player, 8, "    ");
                        $api->setLine($player, 9, "§eNordicNetwork");
                    }

                    switch ($this->restartTime[$this->plugin->data["level"]]) {
                        case 0:
                            foreach ($this->plugin->world->getPlayers() as $player){
                                $name = $player->getName();
                                $kills = new Config($this->plugin->plugin->getDataFolder() . "finalkills.yml", Config::YAML);
                                $kills->set($name, 0);
                                $kills->save();
                                $this->plugin->plugin->joinToRandomArena($player);
                                $api->remove($player);
                            }

                            break;
                        case -1:
                            $this->plugin->loadArena(true);
                            $this->reloadTimer();
                            $this->plugin->destroyEntity();
                            break;
                    }
                    break;
            }
        }
    }



    public function reloadTimer() {

         if(!empty($this->plugin->data["level"])){

        $this->waitTime[$this->plugin->data["level"]] = 30;
        $this->upgradeNext[$this->plugin->data["level"]] = 1;
        $this->upgradeTime[$this->plugin->data["level"]] = 5 * 60;
        $this->bedgone[$this->plugin->data["level"]] = 10 * 60;
        $this->suddendeath[$this->plugin->data["level"]] = 10 * 60;
        $this->gameover[$this->plugin->data["level"]] = 10 * 60; 
        $this->restartTime[$this->plugin->data["level"]] = 10;
      }

    }
}
