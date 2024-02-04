<?php


namespace ItsToxicGG\BedWars\provider;

use pocketmine\world\World;
use pocketmine\utils\Config;
use ItsToxicGG\BedWars\Game;
use ItsToxicGG\BedWars\BedWars;


class YamlDataProvider {


    private $plugin;


    public function __construct(BedWars $plugin) {
        $this->plugin = $plugin;
        $this->init();
        $this->loadArenas();
    }

    public function init() {
        if(!is_dir($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }
        if(!is_dir($this->getDataFolder() . "arenas")) {
            @mkdir($this->getDataFolder() . "arenas");
        }
        if(!is_dir($this->getDataFolder() . "saves")) {
            @mkdir($this->getDataFolder() . "saves");
        }
    }

    public function loadArenas() {
        foreach (glob($this->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . "*.yml") as $arenaFile) {
            $config = new Config($arenaFile, Config::YAML);
            $iyah = $config->getAll();  
            foreach ($iyah as $key => $value) {
                if(is_string($value) && substr($value, 0, $length = strlen("serialized=")) == "serialized=") {
                    $iyah[$key] = unserialize(substr($value, $length));
                }
            }
            $this->plugin->arenas[basename($arenaFile, ".yml")] = new Game($this->plugin, $iyah);
        }
    }

    public function saveArenas() {
        foreach ($this->plugin->arenas as $fileName => $arena) {
            if($arena->world instanceof World) {
                foreach ($arena->players as $player) {
                    $player->teleport($player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                }
                $arena->draw();
                // must be reseted
                $arena->mapReset->loadMap($arena->world->getFolderName(), true);
            }
            $config = new Config($this->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $fileName . ".yml", Config::YAML);
            $config->setAll($arena->data);
            $config->save();
        }
    }

    /**
     * @return string $dataFolder
     */
    private function getDataFolder(): string {
        return $this->plugin->getDataFolder();
    }
}
