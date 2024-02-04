<?php

namespace ItsToxicGG\BedWars\provider;

use pocketmine\player\Player;
use ItsToxicGG\BedWars\BedWars;
use mysqli;
use mysqli_result;

class DatabaseMYSQL{

    private $plugin;
    private $database;

    public function __construct(BedWars $plugin){
        $this->plugin = $plugin;
        $this->init();
    }

    public function init(){
        $this->getDatabase()->query("
            CREATE TABLE IF NOT EXISTS BedWars (
                PlayerName VARCHAR(255) PRIMARY KEY,
                BedBroken INT NOT NULL,
                GamePlayed INT NOT NULL,
                MainKill INT NOT NULL,
                FinalKill INT NOT NULL,
                Victory INT NOT NULL,
                QuickBuyData TEXT NOT NULL
            );
        ");
    }

    private function getDatabase(){
        if($this->database instanceof mysqli && $this->database->ping()){
            return $this->database;
        }

        $config = $this->plugin->getConfig()->get("mysql");
        $this->database = new mysqli($config["ip"], $config["user"], $config["password"], $config["database"]);

        if($this->database->connect_error){
            BedWars::getInstance()->getLogger()->alert("Could not connect to MySQL: " . $this->database->connect_error);
            return null;
        }

        return $this->database;
    }

    public function registerAccount(Player $player){
        $playerName = $player->getName();
        $oke = $this->getDatabase();

        $stmt = $oke->prepare("INSERT INTO BedWars (PlayerName, BedBroken, GamePlayed, MainKill, FinalKill, Victory, QuickBuyData) VALUES (?, 0, 0, 0, 0, 0, '')");
        $stmt->bind_param("s", $playerName);
        $stmt->execute();
        $stmt->close();

        $oke->close();
    }

    public function addscore(Player $player, $type){
        $playerName = $player->getName();
        $oke = $this->getDatabase();

        $query = function ($sql) use ($oke){
            $result = $oke->query($sql);
            if(!$result){
                BedWars::getInstance()->getLogger()->error("Error executing query: " . $oke->error);
            }
        };

        switch($type){
            case "kill":
                $query("UPDATE BedWars SET MainKill = MainKill + 1 WHERE PlayerName = '$playerName'");
                break;
            case "fk":
                $query("UPDATE BedWars SET FinalKill = FinalKill + 1 WHERE PlayerName = '$playerName'");
                break;
            case "GamePlayed":
                $query("UPDATE BedWars SET GamePlayed = GamePlayed + 1 WHERE PlayerName = '$playerName'");
                break;
            case "Victory":
                $query("UPDATE BedWars SET Victory = Victory + 1 WHERE PlayerName = '$playerName'");
                break;
            case "BedBroken":
                $query("UPDATE BedWars SET BedBroken = BedBroken + 1 WHERE PlayerName = '$playerName'");
                break;
        }

        $oke->close();
    }

    public function getAccount(Player $player): bool {
        $playerName = $player->getName();
        $oke = $this->getDatabase();

        $stmt = $oke->prepare("SELECT * FROM BedWars WHERE PlayerName = ?");
        $stmt->bind_param("s", $playerName);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result instanceof mysqli_result) {
            return isset($result->fetch_assoc()["PlayerName"]);
        }

        return false;
    }
}
