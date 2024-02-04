<?php
declare(strict_types=1);

namespace ItsToxicGG\BedWars\map;

use pocketmine\Server;
use pocketmine\world\World;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ItsToxicGG\BedWars\BedWars;
use ItsToxicGG\BedWars\Game;
use ZipArchive;

/**
 * Class MapReset
 * @package ItsToxicGG\BedWars\math
 */
class MapReset
{

    /** @var Game $plugin */
    public $plugin;

    /**
     * MapReset constructor.
     * @param Game $plugin
     */
    public function __construct(Game $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param World $world
     */
    public function saveMap(World $world)
    {
        if (!file_exists($this->plugin->plugin->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $world->getFolderName())) {
            return;
        }
        $world->save(true);
        $levelPath = $this->plugin->plugin->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $world->getFolderName();
        $zipPath = $this->plugin->plugin->getDataFolder() . "saves" . DIRECTORY_SEPARATOR . $world->getFolderName() . ".zip";
        $zip = new ZipArchive();
        if (is_file($zipPath)) {
            unlink($zipPath);
        }
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($levelPath)), RecursiveIteratorIterator::LEAVES_ONLY);
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
                $localPath = substr($filePath, strlen($this->plugin->plugin->getServer()->getDataPath() . "worlds"));
                $zip->addFile($filePath, $localPath);
            }
        }
        $zip->close();
    }

    /**
     * @param string $folderName
     * @return World $world
     */
    public function loadMap(string $folderName): ?World
    {
        if (!file_exists($this->plugin->plugin->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $folderName)) {
            return null;
        }
        if (!$this->plugin->plugin->getServer()->getWorldManager()->isWorldGenerated($folderName)) {
            return null;
        }

        if ($this->plugin->plugin->getServer()->getWorldManager()->isWorldLoaded($folderName)) {
            $this->plugin->plugin->getServer()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName($folderName));
        }

        $zipPath = $this->plugin->plugin->getDataFolder() . "saves" . DIRECTORY_SEPARATOR . $folderName . ".zip";
        if (!file_exists($zipPath)) {
            BedWars::getInstance()->getLogger()->critical("Couldn't reload level {$folderName} (map archive was not found).");
            return null;
        }

        $zipArchive = new ZipArchive();
        $zipArchive->open($zipPath);
        $zipArchive->extractTo($this->plugin->plugin->getServer()->getDataPath() . "worlds");
        $zipArchive->close();

        $this->plugin->plugin->getServer()->getWorldManager()->loadWorld($folderName);
        return $this->plugin->plugin->getServer()->getWorldManager()->getWorldByName($folderName);
    }
}
