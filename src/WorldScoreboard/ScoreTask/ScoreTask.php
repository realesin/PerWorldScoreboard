<?php

namespace WorldScoreboard\ScoreTask;

use WorldScoreboard\Score;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TE;

class ScoreTask extends Task {

	/** @var Score */
	private $plugin;

	/**
	* @param Score $plugin
	*/
	public function __construct(Score $plugin) {
		$this->plugin = $plugin;
	}

	/**
	* @return Config
	*/
	public function getConfig() : Config{
		return new Config($this->plugin->getDataFolder() . 'config.yml');
	}

	/**
	* @param int $tick
	*/
	public function onRun($tick) : void{
		$worlds = $this->getConfig()->get("worlds", []);

		if (empty($worlds)) {
			$this->prepareHud(Server::getInstance()->getOnlinePlayers(), $this->getConfig()->get('default', []));
		}else{
			foreach ($worlds as $world => $title) {
				$level_world = Server::getInstance()->getLevelByName($world);
				if ($level_world instanceof Level) {
					$this->prepareHud($level_world->getPlayers(), $title);
				}
			}
		}
	}

	/**
	* @param array $players
	* @param array $config_title
	*/
	public function prepareHud(array $players, array $config_title) : void{
		foreach ($players as $player) {
			$this->broadcastHud($player, $config_title['title'], $config_title['lines'], (int) $config_title['sortOrder']);
		}
	}

	/**
	* @param Player $player
	* @param array $titles
	* @param array $lines
	* @param int   $sortOrder
	*/
	public function broadcastHud(Player $player, array $titles, array $lines, int $sortOrder) : void{
		$title = $this->getTitle($titles);
		$this->plugin->createScore($player, $this->plugin->translate($player, $title), $sortOrder);
		$this->plugin->setScoreLines($player, $lines, true);
	}

	/**
	* @param array $titles
	* @return string
	*/
	public function getTitle(array $titles) : ?string{
		shuffle($titles);
		shuffle($titles);
		return array_shift($titles);
	}
}