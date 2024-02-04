<?php



namespace ItsToxicGG\BedWars\libs\scoreboard;

use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use ItsToxicGG\BedWars\BedWars;

/**
 * Class ScoreAPI
 * @package skywars\arena\object
 */
class ScoreAPI {
	private $scoreboards = [];
	private $plugin;
	
	public function __construct(BedWars $plugin){
         $this->plugin = $plugin;
	}
	
	public function new(Player $pl, string $objectiveName, string $displayName) : void {
		if(isset($this->scoreboards[$pl->getName()])){
			$this->remove($pl);
		}
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = "sidebar";
		$pk->objectiveName = $objectiveName;
		$pk->displayName = $displayName;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = 0;
		$pl->getNetworkSession()->sendDataPacket($pk);
		$this->scoreboards[$pl->getName()] = $objectiveName;
	}
	
	public function remove(Player $pl) : void {
		if(isset($this->scoreboards[$pl->getName()])){
			$objectiveName = $this->getObjectiveName($pl);
			$pk = new RemoveObjectivePacket();
			$pk->objectiveName = $objectiveName;
			$pl->getNetworkSession()->sendDataPacket($pk);
			unset($this->scoreboards[$pl->getName()]);
		}
	}
	
	public function setLine(Player $pl, int $score, string $message) : void {
		if(!isset($this->scoreboards[$pl->getName()])){

			return;
		}
		$objectiveName = $this->getObjectiveName($pl);
		$entry = new ScorePacketEntry();
		$entry->objectiveName = $objectiveName;
		$entry->type = $entry::TYPE_FAKE_PLAYER;
		$entry->customName = $message;
		$entry->score = $score;
		$entry->scoreboardId = $score;
		$pk = new SetScorePacket();
		$pk->type = $pk::TYPE_CHANGE;
		$pk->entries[] = $entry;
		$pl->getNetworkSession()->sendDataPacket($pk);
	}
	
	public function getObjectiveName(Player $pl) : ?string {
		return isset($this->scoreboards[$pl->getName()]) ? $this->scoreboards[$pl->getName()] : null;
	}
}
