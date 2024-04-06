<?php



namespace ItsToxicGG\BedWars;


use pocketmine\entity\Human;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\utils\Config;
use pocketmine\world\World;
use pocketmine\plugin\PluginBase;
use ItsToxicGG\BedWars\entities\Bedbug;
use ItsToxicGG\BedWars\entities\EnderDragon;
use ItsToxicGG\BedWars\entities\Golem;
use ItsToxicGG\BedWars\entities\projectiles\Egg;
use ItsToxicGG\BedWars\entities\projectiles\Fireball;
use ItsToxicGG\BedWars\libs\scoreboard\ScoreAPI;
use ItsToxicGG\BedWars\map\MapReset;
use ItsToxicGG\BedWars\commands\BedWarsCommand;
use ItsToxicGG\BedWars\provider\DatabaseMYSQL;
use ItsToxicGG\BedWars\math\Vector3;
use ItsToxicGG\BedWars\entities\Generator;
use ItsToxicGG\BedWars\entities\ShopVillager;
use ItsToxicGG\BedWars\entities\UpgradeVillager;
use ItsToxicGG\BedWars\provider\YamlDataProvider;
use pocketmine\entity\Skin;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntityDataHelper;
use pocketmine\world\Position;
use pocketmine\player\Player;
use pocketmine\nbt\tag\CompoundTag;
use NurAzliYT\invmenu\InvMenuHandler;
use pocketmine\network\mcpe\protocol\types\BlockPosition;

/**
 * Class BedWars
 * @package ItsToxicGG\BedWars
 */
class BedWars extends PluginBase implements Listener {

    public $dataProvider;
    /**
     * @var
     */
    public $config;
    /**
     * @var array
     */
    public $placedBlock = [];
    /**
     * @var array
     */
    public $arenas = [];

    /**
     * @var array
     */
    public $setters = [];
    /**
     * @var array
     */
    public $setupData = [];
    /**
     * @var
     */
    public $mysqldata;
    /**
     * @var array
     */
    public $arenaPlayer = [];
    /**
     * @var array
     */
    public $teams = [];
    /**
     * @var
     */
    public static $score;

    /**
     * @var
     */
    public static $instance;

    /**
     * @var
     */
    public $shop;
    /**
     * @var
     */
    public $upgrade;

    protected function onEnable(): void{
        self::$instance = $this;
        self::$score = new ScoreAPI($this);
        $this->saveResource("config.yml");
        $this->saveResource("diamond.png");
        $this->saveResource("emerald.png");
        $this->registerEntity();
        parent::onEnable();
        $this->mysqldata = new DatabaseMYSQL($this);
        $this->config = (new Config($this->getDataFolder() . "config.yml", Config::YAML))->getAll();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->dataProvider = new YamlDataProvider($this);
        $this->dataProvider->loadArenas();
        $this->getServer()->getCommandMap()->register("bw", new BedWarsCommand($this));
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
        foreach ($this->getServer()->getNetwork()->getInterfaces() as $interface) {
            if($interface instanceof RakLibInterface) {
                $interface->setPacketLimit(PHP_INT_MAX);
                break;
            }
        }
        if(is_null($this->getConfig()->get("join-arena"))){
            $this->getConfig()->set("join-arena","false");
            $this->getConfig()->save();
            $this->getConfig()->reload();
        }
    }

    
    public static function getInstance(){
        return self::$instance;
    }

    public function isInGame(Player $player): bool
    {
        if(isset($this->arenaPlayer[$player->getName()])){
            return true;
        } else {
            return false;
        }
    }

    public function getArenaByPlayer(Player $player){
        return $this->arenaPlayer[$player->getName()];
    }

    public function registerEntity(){
		$entityFactory = EntityFactory::getInstance();

		$entityFactory->register(Fireball::class, function(World $world, CompoundTag $nbt) :Fireball{
			return new Fireball(EntityDataHelper::parseLocation($nbt, $world), null);
		}, ['Fireball']);
		$entityFactory->register(EnderDragon::class, function(World $world, CompoundTag $nbt) :EnderDragon{
			return new EnderDragon(EntityDataHelper::parseLocation($nbt, $world), $nbt, null, null);
		}, ['EnderDragon']);
		$entityFactory->register(Golem::class, function(World $world, CompoundTag $nbt) :Golem{
			return new Golem(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ['Golem']);
		$entityFactory->register(Bedbug::class, function(World $world, CompoundTag $nbt) :Bedbug{
			return new Bedbug(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ['Bedbug']);
		$entityFactory->register(Generator::class, function(World $world, CompoundTag $nbt) :Generator{
			return new Generator(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
		}, ['Generator']);
	    $entityFactory->register(UpgradeVillager::class, function(World $world, CompoundTag $nbt) :UpgradeVillager{
			return new UpgradeVillager(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ['UpgradeVillager']);
		$entityFactory->register(ShopVillager::class, function(World $world, CompoundTag $nbt) :ShopVillager{
			return new ShopVillager(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ['ShopVillager']);
		$entityFactory->register(Egg::class, function(World $world, CompoundTag $nbt) :Egg{
			return new Egg(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['Egg']);
    
    }

    public function onPlayerJoin(PlayerJoinEvent $event){
        $event->setJoinMessage("");
    }

    public function join(PlayerLoginEvent $event)
	{
		$player = $event->getPlayer();
	
		if (!$this->mysqldata->getAccount($player)) {
			$this->mysqldata->registerAccount($player);
		}  
        if($this->getConfig()->get("join-arena") == true && count($this->arenas) != 0){
            $this->joinToRandomArena($player);
        }
	}

    /**
     * @param $path
     * @return Skin
     */
    
    public function getSkinFromFile($path) : Skin{
        $img = imagecreatefrompng($path);
        $bytes = '';
        $l = (int) getimagesize($path)[1];
        for ($y = 0; $y < $l; $y++) {
            for ($x = 0; $x < 64; $x++) {
                $rgba = imagecolorat($img, $x, $y);
                $r = ($rgba >> 16) & 0xff;
                $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        imagedestroy($img);
        return new Skin("Standard_CustomSlim", $bytes);
    }

    protected function onDisable(): void{
        $this->dataProvider->saveArenas();
        if(file_exists($this->getDataFolder()."finalkills.yml")){
            unlink($this->getDataFolder()."finalkills.yml");
        }
    }

    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();

        if (!isset($this->setters[$player->getName()])) {
            return;
        }

        $event->cancel();
        $args = explode(" ", $event->getMessage());

        /** @var Game $arena */
        $arena = $this->setters[$player->getName()];
        /** @var Game[] $arenas */
        $arenas = is_array($this->setters[$player->getName()]) ? $this->setters[$player->getName()] : [$this->setters[$player->getName()]];

        switch ($args[0]) {
            case "help":
                $player->sendMessage(
                "§bhelp : §aDisplays list of available setup commands\n" .
                "§bworld : §aSets arena world\n".
                "§blobby : §aSets Lobby Spawn\n".
                "§blocation: §aSets arena location\n".
                "§bcorner1: §aSets arena dragon 1\n".
                "§bcorner2: §aSets arena dragon 2\n".
                "§baddupgrade: §aSpawns Upgrade Villager \n".
                "§baddshop: §aSpawns Main Shop Villager\n".                                              
                "§bjoinsign : §aSets arena join sign\n".
                "§bsaveworld : §aSaves the arena world\n".
                "§bsetbed : §aset bed position \n".
                "§benable : §aEnables the arena");
                break;
            case "corner1":
                $arena->data["corner1"] = $player->getPosition();
                $player->sendMessage("Sucessfuly set ender dragon position 1");
                break;
            case "corner2":
                $world = $player->getWorld();
                $firstPos = $arena->data["corner1"];
                $firstPos->y = World::Y_MIN;
                $secondPos = $player->getPosition();
                $secondPos->y = World::Y_MAX;

                $player->sendMessage("§6> Importing blocks...");
                $blocks = [];

                for($x = min($firstPos->getX(), $secondPos->getX()); $x <= max($firstPos->getX(), $secondPos->getX()); $x++) {
                    for($y = min($firstPos->getY(), $secondPos->getY()); $y <= max($firstPos->getY(), $secondPos->getY()); $y++) {
                        for($z = min($firstPos->getZ(), $secondPos->getZ()); $z <= max($firstPos->getZ(), $secondPos->getZ()); $z++) {
                            if($world->getBlockLightAt($x, $y, $z) !== BlockTypeIds::AIR) {
                                $blocks["$x:$y:$z"] = new Vector3($x,$y,$z);
                            }
                        }
                    }
                }

                $player->sendMessage("§aDragon position 2 set to {$player->getPosition()->asVector3()->__toString()} in world {$world->getFolderName()}");
                $arena->data["corner1"] = (new Vector3((int)$firstPos->getX(), (int)$firstPos->getY(), (int)$firstPos->getZ()))->__toString();
                $arena->data["corner2"] = (new Vector3((int)$player->getPosition()->getX(), (int)$player->getPosition()->getY(), (int)$player->getPosition()->getZ()))->__toString();
                $arena->data["blocks"] = $blocks;
                $player->sendMessage("Successfully set ender dragon position 2");
                break;
           /* case "slots":
                if(!isset($args[1])) {
                    $player->sendMessage("§cUsage: §7slots <int: slots>");
                    break;
                }
                $arena->data["slots"] = (int)$args[1];
                $player->sendMessage("§bSlots updated to $args[1]!");
                break;*/
            case "world":
                if(!isset($args[1])) {
                    $player->sendMessage("§bUsage: §7world <worldName>");
                    break; 
	        }
                if(!$this->getServer()->getWorldManager()->isWorldGenerated($args[1])) {
                    $player->sendMessage("§bWorld $args[1] does not found!");
                    break;
                }
                $player->sendMessage("§bArena World updated to $args[1]!");
                $arena->data["level"] = $args[1];
                break;
            case "addupgrade":
                $upgrade = $this->upgrade[$player->getName()];
                $arena->data["upgrade"]["$upgrade"] = (new Vector3(floor($player->getPosition()->getX()), floor($player->getPosition()->getY()), floor($player->getPosition()->getZ())))->__toString();
                $player->sendMessage("§bSpawn upgrade $upgrade setted " . (string)floor($player->getPosition()->getX()) . " Y: " . (string)floor($player->getPosition()->getY()) . " Z: " . (string)floor($player->getPosition()->getZ()));
                $this->upgrade[$player->getName()]++;
                break;
            case "addshop":
                $shop = $this->shop[$player->getName()];
                $arena->data["shop"]["$shop"] = (new Vector3(floor($player->getPosition()->getX()), floor($player->getPosition()->getY()), floor($player->getPosition()->getZ())))->__toString();
                $player->sendMessage("§bSpawn Shop  $shop setted " . (string)floor($player->getPosition()->getX()) . " Y: " . (string)floor($player->getPosition()->getY()) . " Z: " . (string)floor($player->getPosition()->getZ()));
                $this->shop[$player->getName()]++;
            break;
//             case "setdistance":
//               $arena->data["distance"] = Vector3::fromString($arena->data["location"]["red"])->distance($player->getEyePos()->getPosition()->asVector3());
//             break;
            case "location":
                if(!isset($args[1])) {
                    $player->sendMessage("§cUsage: §7location blue/red/yellow/green");
                    break;
                }

                if(!in_array($args[1], ["red", "blue", "yellow", "green"])){
                    $player->sendMessage("§cUsage: §7location blue/red/yellow/green");
                    break;
                }

                $arena->data["location"]["{$args[1]}"] = (new Vector3(floor($player->getPosition()->getX()), floor($player->getPosition()->getY()), floor($player->getPosition()->getZ())))->__toString();
                $player->sendMessage("§bLocation Team $args[1] set to X: " . (string)floor($player->getArmorPoints()) . " Y: " . (string)floor($player->getPosition()->getY()) . " Z: " . (string)floor($player->getPosition()->getZ()));

            break;   
            case "setbed":
                if(!isset($args[1])) {
                    $player->sendMessage("§cUsage: §7setspawn blue/red/yellow/green");
                    break;
                }
                if(!in_array($args[1], ["red", "blue", "yellow", "green"])){
                    break;
                }
                $arena->data["bed"]["{$args[1]}"] = (new Vector3(floor($player->getPosition()->getX()), floor($player->getPosition()->getY()), floor($player->getPosition()->getZ())))->__toString();
                $player->sendMessage("§a Bed position $args[1] set to X: " . (string)floor($player->getPosition()->getX()) . " Y: " . (string)floor($player->getPosition()->getY()) . " Z: " . (string)floor($player->getPosition()->getZ()));
                break; 
            case "lobby":
                $arena->data["lobby"] = (new Vector3(floor($player->getPosition()->getX()) + 0.0, floor($player->getPosition()->getY()), floor($player->getPosition()->getZ()) + 0.0))->__toString();
                $player->sendMessage("§bLobby set to X: " . (string)floor($player->getPosition()->getX()) . " Y: " . (string)floor($player->getPosition()->getY()) . " Z: " . (string)floor($player->getPosition()->getZ()));
                break;
            case "joinsign":
                $player->sendMessage("§a> Break block to set join sign!");
                $this->setupData[$player->getName()] = 0;
                break;
            case "saveworld":
                if(!$arena->world instanceof World) {
                    $player->sendMessage("§c> Error when saving world: world not found.");
                    if($arena->setup) {
                        $player->sendMessage("§bEror arena not enabled");
                    }
                    break;
                }
                $arena->mapReset->saveMap($player->getWorld());
                $player->sendMessage("World Saved");
                break;
            case "enable":
                if (is_array($arena)) {
                    $player->sendMessage("§c> You cannot enable arena in mode multi-setup mode.");
                    break;
                }

                if (!$arena->setup) {
                    $player->sendMessage("§6> Arena is already enabled!");
                    break;
                }

                if (!$arena->enable()) {
                    $player->sendMessage("§c> Could not load arena, there are missing information!");
                    break;
                }

                $arena->mapReset->saveMap($player->getWorld());

                $player->sendMessage("§a> Arena enabled!");
                break;  
            case "done":
                $player->sendMessage("§eArena saved to database");
                unset($this->setters[$player->getName()]);
                unset($this->upgrade[$player->getName()]);
                unset($this->shop[$player->getName()]);
                if(isset($this->setupData[$player->getName()])) {
                    unset($this->setupData[$player->getName()]);
                }
                break;
            default:
                $player->sendMessage("§etype 'help' for list commands ");
                break;
        }
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if(isset($this->setupData[$player->getName()])) {
            switch ($this->setupData[$player->getName()]) {
                case 0:
                    $this->setters[$player->getName()]->data["joinsign"] = [(new Vector3($block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ()))->__toString(), $block->getPosition()->getWorld()->getFolderName()];
                    $player->sendMessage("§aJoin sign seted");
                    unset($this->setupData[$player->getName()]);
                    $event->cancel();
                    break;
            }
        }
    }

    public function startSetup(Player $player,string $map){
        $player->teleport($player->getServer()->getWorldManager()->getWorldByName($map)->getSafeSpawn());
        $this->setters[$player->getName()] = $this->arenas[$map];
        $this->upgrade[$player->getName()] = 1;
        $this->shop[$player->getName()] = 1;
    }

    public function getRandomArena(){

        $availableArenas = [];
        foreach ($this->arenas as $index => $arena) {
            $availableArenas[$index] = $arena;
        }

        //2.
        foreach ($availableArenas as $index => $arena) {
            if($arena->phase !== 0 || $arena->setup) {
                unset($availableArenas[$index]);
            }
        }

        //3.
        $arenasByPlayers = [];
        foreach ($availableArenas as $index => $arena) {
            $arenasByPlayers[$index] = count($arena->players);
        }

        arsort($arenasByPlayers);
        $top = -1;
        $availableArenas = [];

        foreach ($arenasByPlayers as $index => $players) {
            if($top == -1) {
                $top = $players;
                $availableArenas[] = $index;
            }
            else {
                if($top == $players) {
                    $availableArenas[] = $index;
                }
            }
        }

        if(empty($availableArenas)) {
            return null;
        }

        return $this->arenas[$availableArenas[array_rand($availableArenas, 1)]];
    }
    
    public function joinToRandomArena(Player $player) {
        $arena = $this->getRandomArena();
        if(!is_null($arena)) {
            $arena->joinToArena($player);
            return;
        }
       
        $player->getInventory()->clearAll();
        $player->teleport($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
        
    }

    public static function getScore(): ScoreAPI {
        return self::$score;
    }
}
