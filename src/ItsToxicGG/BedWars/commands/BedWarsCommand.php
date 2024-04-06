<?php



declare(strict_types=1);

namespace ItsToxicGG\BedWars\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use ItsToxicGG\BedWars\Game;
use ItsToxicGG\BedWars\BedWars;

/**
 *
 */
class BedWarsCommand extends Command {

    private BedWars $plugin;

    /**
     * BedWarsCommand constructor.
     * @param BedWars $plugin
     */
    public function __construct(BedWars $plugin) {
        $this->plugin = $plugin;

        parent::__construct(
            "bedwars",
            "BedWars Command",
            "§cUse /bedwars help or /bw help to see list of commands!",
            ["bw"]
        );

        $this->setPermission("bw.cmd");
        $this->setAliases(["bw"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        $helpM = "
                §l§b===================================\n" .
            "§aBedWars 4vs4vs4vs4\n".
            "§7/bw help\n".
            "§7/bw create\n".
            "§7/bw remove\n".
            "§7/bw set\n".
            "§7/bw list\n§l§b===================================
                    ";
        if(!isset($args[0])) {
            $sender->sendMessage($helpM);


            
            return;
        }
        switch ($args[0]) {
            case "help":
                $sender->sendMessage($helpM);
                break;

            case "create":
                if(!$sender->hasPermission("bw.cmd.create")) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage("§cUsage: §7/bw create <arenaName>");
                    break;
                }
                if(isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("§c> Arena $args[1] already exists!");
                    break;
                }
                $this->plugin->arenas[$args[1]] = new Game($this->plugin, []);
                $sender->sendMessage("§a> Arena $args[1] created!");
                break;
            case "remove":
                if(!$sender->hasPermission("bw.cmd.remove")) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage("§cUsage: §7/bw remove <arenaName>");
                    break;
                }
                if(!isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("§c> Arena $args[1] was not found!");
                    break;
                }

                /** @var Game $arena */
                $arena = $this->plugin->arenas[$args[1]];

                foreach ($arena->players as $player) {
                    $player->teleport($this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                }

                if(is_file($file = $this->plugin->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $args[1] . ".yml")) unlink($file);
                unset($this->plugin->arenas[$args[1]]);
                $sender->sendMessage("§cArena removed!");
                break;
            case "set":
                if(!$sender->hasPermission("bw.cmd.set")) {
                    break;
                }
                if(!$sender instanceof Player) {
                    $sender->sendMessage("§cyou can't execute this command in console");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage("§cUsage: §7/bw set <arenaName>");
                    break;
                }
                if(isset($this->plugin->setters[$sender->getName()])) {
                    $sender->sendMessage("§bYou are already in setup mode!");
                    break;
                }
                if(!isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("§bArena $args[1] does not found!");
                    break;
                }
                if(!$sender->getServer()->getWorldManager()->isWorldGenerated($args[1])){
                    $sender->sendMessage("§bWorld not found");
                    break;
                }
                if(!$sender->getServer()->getWorldManager()->isWorldLoaded($args[1])) {
                    $sender->getServer()->getWorldManager()->loadWorld($args[1]);
                }

                $sender->sendMessage("§bYou've joined setup mode");
                 $this->plugin->startSetup($sender,$args[1]);
                break;
            case "random":
                if(!$sender instanceof Player) {
                    $sender->sendMessage("§cyou can't execute this command in console");
                    break;
                }
                $sender->sendMessage("§bYou've join to arena");
                $this->plugin->joinToRandomArena($sender);
                break;
            case "list":
                if(!$sender->hasPermission("bw.cmd.list")) {
                    break;
                }
                if(count($this->plugin->arenas) === 0) {
                    $sender->sendMessage("§a0 Arena");
                    break;
                }
                $list = "§eArenas\n";
                foreach ($this->plugin->arenas as $name => $arena) {
                    if($arena->setup) {
                        $list .= "§b$name  §cnot active\n";
                    }
                    else {
                        $list .= "§b$name : §aactived\n";
                    }
                }
                $sender->sendMessage($list);
                break;
        }
    }
}

