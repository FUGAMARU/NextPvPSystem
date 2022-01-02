<?php

namespace NextPvPSystem;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;

class NextPvPSystem extends PluginBase implements Listener{
	public $RED = 0;
	public $BLUE = 0;
	public $count = 0;
	public $blockcountred = 0;
	public $blockcountblue = 0;
public function onEnable(){
    if($this->getServer()->getPluginManager()->getPlugin("PocketMoney") !== null){
        $this->PocketMoney = $this->getServer()->getPluginManager()->getPlugin("PocketMoney");
        $this->getLogger()->info("PocketMoneyを検出しました。");
    }else{
        $this->getLogger()->warning("PocketMoneyが検出されませんでした。");
        $this->getServer()->getPluginManager()->disablePlugin($this);
    }

    if(!file_exists($this->getDataFolder())){
    	 mkdir($this->getDataFolder(), 0744, true);
	}
	$this->config = new Config("Date.yml", Config::YAML);
	$this->getServer()->getPluginManager()->registerEvents($this, $this);
	$this->getLogger()->info(TextFormat::AQUA."NextPvP-System起動完了".TextFormat::GREEN."V1.0.0".TextFormat::BLUE." byFUGAMARU");
}

public function onJoin(PlayerJoinEvent $event){
	$player = $event->getPlayer();
	$PlayerName = $player->getName();

	if($this->config->exists($PlayerName."@KILL")){
    	//ある
	}else{
		$this->config->set($PlayerName."@KILL", "0");
		$this->config->set($PlayerName."@DEATH", "0");
		$this->config->set($PlayerName."@BLOCK", "0");
		$this->config->save();
	}

	$Money = $this->PocketMoney->getMoney($PlayerName);
		$player->sendMessage("＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
		$player->sendMessage("NextPvPサーバーへようこそ");
		$player->sendMessage($PlayerName."さんの所持金： ".$Money."PM");
		$player->sendMessage("ルールに従ってPvPをお楽しみください");
		$player->sendMessage("サーバーから退出する際は必ず【/logout】とコマンドを実行してください");
		$player->sendMessage("＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
}

public function onEntityDamageByEntity(EntityDamageEvent $event){
    if($event instanceof EntityDamageByEntityEvent){
		$Damager = $event->getDamager();
		$DamagerName = $Damager->getNameTag();
		$Entity = $event->getEntity();
		$EntityName = $Entity->getNameTag();
		if($Entity instanceof Player and $Damager instanceof Player){
			if(preg_match("/[赤]/", $DamagerName)){
				if(preg_match("/[赤]/", $EntityName)){
					$event->setCancelled();
				}
			}elseif(preg_match("/[青]/", $DamagerName)){
				if(preg_match("/[青]/", $EntityName)){
					$event->setCancelled();
				}
			}
		}
	}
}

public function onDeath(PlayerDeathEvent $event){
	$server = Server::getInstance();
    $entity = $event->getEntity();
    $entityname = $entity->getName();
    $player = $this->getServer()->getPlayer($entityname);
    $deather = $entity->getLastDamageCause();
    	if($deather instanceof EntityDamageByEntityEvent){
			$kill3 = $deather->getDamager();
    	if($kill3 instanceof Player){
    		$name = $entity->getNameTag();
    		$name2 = $entity->getName();
			$KillName = $kill3->getNameTag();
			$KillName2 = $kill3->getName();
			$kill3->sendPopUp($name."§bさんを倒しました");
    		$entity->sendMessage($KillName."§6さんに倒されました");

    		$kill = $this->config->get($KillName2."@KILL");
			$kill2 = intval($kill);
			$kill2++;
    		$this->config->set($KillName2."@KILL", $kill2);
			$this->config->save();

			$death = $this->config->get($name2."@DEATH");
			$death2 = intval($death);
			$death2++;
			$this->config->set($name2."@DEATH", $death2);
			$this->config->save();
    	}
}
}

public function onPlace(BlockPlaceEvent $event){
	$x = $event->getBlock()->x;
	$y = $event->getBlock()->y;
	$z = $event->getBlock()->z;

	if($x == 45 and $y == 30 and $z == -1){//赤が相手の羊毛を設置したら
		$PlayerName = $event->getPlayer()->getName();
		$PlayerNameTag = $event->getPlayer()->getNameTag();
		$block = $event->getBlock();
		if($block->getID() === 35 && $block->getDamage() === 5){
			$block2 = $this->config->get($PlayerName."@BLOCK");
			$block = intval($block2);
			$block++;
			$this->config->set($PlayerName."@BLOCK", $block);

			global $blockcountred;
			$blockcountred++;
			Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§f".$PlayerNameTag."さんが§9青§fチームの羊毛を設置しました！");
			$block = Block::get(7, 0);
			$vector = new Vector3($x, $y, $z);
			$level->setBlock($vector, $block);
				if($blockcountred == 2){
					$player = $event->getPlayer();
					Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§fゲーム終了");
					Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§c赤§fチームの勝利！");
					$task = new Shutdown($this, $server);
   					$this->getServer()->getScheduler()->scheduleDelayedTask($task, 1200);
   					Server::getInstance()->broadcastMessage("§6システム§a>>§f1分後にサーバーの再起動を行います");
   					$players = Server::getInstance()->getOnlinePlayers();
					foreach ($players as $player){
					$player->getInventory()->clearall();
					}
				}
			}
		}elseif($x == 45 and $y == 30 and $z == 1){
			$PlayerName = $event->getPlayer()->getName();
			$PlayerNameTag = $event->getPlayer()->getNameTag();
			$block = $event->getBlock();
				if($block->getID() === 35 && $block->getDamage() === 13){
					$block2 = $this->config->get($PlayerName."@BLOCK");
					$block = intval($block2);
					$block++;
					$this->config->set($PlayerName."@BLOCK", $block);

					global $blockcountred;
					$blockcountred++;
					Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§f".$PlayerNameTag."さんが§9青§fチームの羊毛を設置しました！");
					$block = Block::get(7, 0);
					$vector = new Vector3($x, $y, $z);
					$level->setBlock($vector, $block);
						if($blockcountred == 2){
							$player = $event->getPlayer();
							Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§fゲーム終了");
							Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§c赤§fチームの勝利！");
							$task = new Shutdown($this, $server);
   							$this->getServer()->getScheduler()->scheduleDelayedTask($task, 1200);
   							Server::getInstance()->broadcastMessage("§6システム§a>>§f1分後にサーバーの再起動を行います");
   							$players = Server::getInstance()->getOnlinePlayers();
								foreach ($players as $player){
									$player->getInventory()->clearall();
								}
						}
				}
			}
	if($x == -45 and $y == 30 and $z == 1){//青が相手の羊毛を設置したら
		$PlayerName = $event->getPlayer()->getName();
		$PlayerNameTag = $event->getPlayer()->getNameTag();
		$block = $event->getBlock();
		if($block->getID() === 35 && $block->getDamage() === 6){
			$block2 = $this->config->get($PlayerName."@BLOCK");
			$block = intval($block2);
			$block++;
			$this->config->set($PlayerName."@BLOCK", $block);

			global $blockcountblue;
			$blockcountblue++;
			Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§f".$PlayerNameTag."さんが§c赤§fチームの羊毛を設置しました！");
			$block = Block::get(7, 0);
			$vector = new Vector3($x, $y, $z);
			$level->setBlock($vector, $block);
				if($blockcountblue == 2){
					$player = $event->getPlayer();
					Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§fゲーム終了");
					Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§9青§fチームの勝利！");
					$task = new Shutdown($this, $server);
   					$this->getServer()->getScheduler()->scheduleDelayedTask($task, 1200);
   					Server::getInstance()->broadcastMessage("§6システム§a>>§f1分後にサーバーの再起動を行います");
   					$players = Server::getInstance()->getOnlinePlayers();
					foreach ($players as $player){
					$player->getInventory()->clearall();
					}
				}
			}
		}elseif($x == -45 and $y == 30 and $z == -1){
			$PlayerName = $event->getPlayer()->getName();
			$PlayerNameTag = $event->getPlayer()->getNameTag();
			$block = $event->getBlock();
				if($block->getID() === 35 && $block->getDamage() === 2){
					$block2 = $this->config->get($PlayerName."@BLOCK");
					$block = intval($block2);
					$block++;
					$this->config->set($PlayerName."@BLOCK", $block);

					global $blockcountblue;
					$blockcountblue++;
					Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§f".$PlayerNameTag."さんが§c赤§ffチームの羊毛を設置しました！");
					$block = Block::get(7, 0);
					$vector = new Vector3($x, $y, $z);
					$level->setBlock($vector, $block);
						if($blockcountred == 2){
							$player = $event->getPlayer();
							Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§fゲーム終了");
							Server::getInstance()->broadcastMessage("§bゲーム情報§a>>§9青§fチームの勝利！");
							$task = new Shutdown($this, $server);
   							$this->getServer()->getScheduler()->scheduleDelayedTask($task, 1200);
   							Server::getInstance()->broadcastMessage("§6システム§a>>§f1分後にサーバーの再起動を行います");
   							$players = Server::getInstance()->getOnlinePlayers();
								foreach ($players as $player){
									$player->getInventory()->clearall();
								}
						}
				}
			}

}


public function onQuit(PlayerQuitEvent $event){
	global $count;
	$server = Server::getInstance();
	$player = $event->getPlayer();
	$PlayerName = $event->getPlayer()->getName();
	$PlayerNameTag = $event->getPlayer()->getNameTag();
	if(empty($player)){
		$player->getInventory()->clearall();

	if(preg_match("/[赤]/", $PlayerNameTag)){
	}elseif(preg_match("/[青]/", $PlayerNameTag)){
		$count--;
	}
	if($count == 1){
		Server::getInstance()->broadcastMessage("§b【ゲーム情報】 ゲーム終了");
			$players = Server::getInstance()->getOnlinePlayers();
			foreach ($players as $player){
				$player->getInventory()->clearall();
			}
		Server::getInstance()->getScheduler()->scheduleDelayedTask(new EndC($this), 1);
	}

	if(preg_match("/[赤]/", $PlayerNameTag)){
		$death2 = $this->config->get($PlayerName."@DEATH");
		$death = intval($death2);
		$death++;
		$this->config->set($PlayerName."@DEATH", $death);
		$this->config->save();
		$this->getLogger()->info(TextFormat::RED.$PlayerName."さんがコマンドからログアウトしませんでした");
		$server->broadcastMessage($PlayerName."§eさんがコマンドからログアウトしませんでした");
	}elseif(preg_match("/[青]/", $PlayerNameTag)){
		$death2 = $this->config->get($PlayerName."@DEATH");
		$death = intval($death2);
		$death++;
		$this->config->set($PlayerName."@DEATH", $death);
		$this->config->save();
		$this->getLogger()->info(TextFormat::RED.$PlayerName."さんがコマンドからログアウトしませんでした");
		$server->broadcastMessage($PlayerName."§eさんがコマンドからログアウトしませんでした");
	}else{
		//含まれていない場合
	}
	}
	
}

public function onRespawn(PlayerRespawnEvent $event){
	$player = $event->getPlayer();

	$player->getInventory()->setArmorItem(0,Item::get(306,0,1));
	$player->getInventory()->setArmorItem(3,Item::get(309,0,1));
	$player->getInventory()->sendArmorContents($player);

	$item = Item::get(272, 0, 1);
	$player->getInventory()->addItem($item);
	$item2 = Item::get(273, 0, 1);
	$player->getInventory()->addItem($item2);
	$item3 = Item::get(258, 0, 1);
	$player->getInventory()->addItem($item3);
	$item4 = Item::get(278, 0, 1);
	$player->getInventory()->addItem($item4);
	$item5 = Item::get(20, 0,32);
	$player->getInventory()->addItem($item5);
	$item6 = Item::get(17, 0,32);
	$player->getInventory()->addItem($item6);
	$item7 = Item::get(366, 0,32);
	$player->getInventory()->addItem($item7);
	$item8 = Item::get(345, 0,1);
	$player->getInventory()->addItem($item8);

}

public function onCommand(CommandSender $sender, Command $command, $label, array $args){
	global $RED, $BLUE, $count;
	$server = Server::getInstance();
	switch($command->getName()){
		case "join":
			$player = $sender->getPlayer();
			$PlayerName = $player->getName();
			$Tag = $player->getNameTag();
			if(preg_match("/[赤]/", $Tag)){
				$sender->sendMessage("§c既にPvPに参加しています");
			}elseif(preg_match("/[青]/", $Tag)){
				$sender->sendMessage("§c既にPvPに参加しています");
			}else{
				$count++;
				if($BLUE < $RED){
  					$sender->setNameTag("[§9青§f]".$PlayerName);
  					$sender->setDisplayName("[§9青§f]".$PlayerName);
  					$BLUE++;
  					$sender->sendMessage("＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
  					$sender->sendMessage("PvPに参加しました あなたは【§9青§f】チームになりました");
  					$sender->sendMessage("＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
  						if(2 < $count){
  							$vector = new Vector3(-85, 6, 0);
    						$sender->setSpawn($vector);
    						$sender->teleport($vector);

    						$sender->getInventory()->setArmorItem(1,Item::get(315,0,1));
    						$sender->getInventory()->setArmorItem(2,Item::get(300,0,1));
							$sender->getInventory()->setArmorItem(3,Item::get(301,0,1));
							$sender->getInventory()->sendArmorContents($sender);

							$item = Item::get(272, 0, 1);
							$player->getInventory()->addItem($item);
							$item2 = Item::get(273, 0, 1);
							$player->getInventory()->addItem($item2);
							$item3 = Item::get(258, 0, 1);
							$player->getInventory()->addItem($item3);
							$item4 = Item::get(278, 0, 1);
							$player->getInventory()->addItem($item4);
							$item5 = Item::get(20, 0,32);
							$player->getInventory()->addItem($item5);
							$item6 = Item::get(17, 0,32);
							$player->getInventory()->addItem($item6);
							$item7 = Item::get(366, 0,32);
							$player->getInventory()->addItem($item7);
							$item8 = Item::get(345, 0,1);
							$player->getInventory()->addItem($item8);
						}
  				}else{
  					$sender->setNameTag("[§c赤§f]".$PlayerName);
  					$sender->setDisplayName("[§c赤§f]".$PlayerName);
  					$RED++;
  					$sender->sendMessage("＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
  					$sender->sendMessage("PvPに参加しました あなたは【§c赤§f】チームになりました");
  					$sender->sendMessage("＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
  						if(2 < $count){
  							$vector2 = new Vector3(85, 6, 0);
    						$sender->setSpawn($vector2);
    						$sender->teleport($vector2);

    						$sender->getInventory()->setArmorItem(1,Item::get(315,0,1));
    						$sender->getInventory()->setArmorItem(2,Item::get(300,0,1));
							$sender->getInventory()->setArmorItem(3,Item::get(301,0,1));
							$sender->getInventory()->sendArmorContents($sender);

							$player->getInventory()->addItem($item);
							$item2 = Item::get(273, 0, 1);
							$player->getInventory()->addItem($item2);
							$item3 = Item::get(258, 0, 1);
							$player->getInventory()->addItem($item3);
							$item4 = Item::get(278, 0, 1);
							$player->getInventory()->addItem($item4);
							$item5 = Item::get(20, 0,32);
							$player->getInventory()->addItem($item5);
							$item6 = Item::get(17, 0,32);
							$player->getInventory()->addItem($item6);
							$item7 = Item::get(366, 0,32);
							$player->getInventory()->addItem($item7);
							$item8 = Item::get(345, 0,1);
							$player->getInventory()->addItem($item8);
  						}
				}

			if($count == 2){
				$task = new Start($this, $server, $player);
   				$this->getServer()->getScheduler()->scheduleDelayedTask($task, 600);
				$server->broadcastMessage("§b【ゲーム情報】 30秒後にゲームが開始します");
			}
		}
		break;

		case"status":
			 if(empty($args[0])){
			 	$PlayerName = $sender->getName();
			 	$kill2 = $this->config->get($PlayerName."@KILL");
			 	$death2 = $this->config->get($PlayerName."@DEATH");
			 	$block2 = $this->config->get($PlayerName."@BLOCK");
			 	$kill = intval($kill2);
			 	$death = intval($death2);
			 	$block = intval($block2);
			 		$sender->sendMessage("＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
			 		$sender->sendMessage($PlayerName."さんのステータス");
					$sender->sendMessage("KILL数： ".$kill);
					$sender->sendMessage("DEATH数： ".$death);
					$sender->sendMessage("羊毛設置回数： ".$block);
					$sender->sendMessage("＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
			}else{
				if($this->config->exists($args[0]."@KILL")){
    					$kill2 = $this->config->get($args[0]."@KILL");
			 			$death2 = $this->config->get($args[0]."@DEATH");
			 			$block2 = $this->config->get($args[0]."@BLOCK");
			 			$kill = intval($kill2);
			 			$death = intval($death2);
			 			$block = intval($block2);
			 				$sender->sendMessage("＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
			 				$sender->sendMessage($args[0]."さんのステータス");
							$sender->sendMessage("KILL数： ".$kill);
							$sender->sendMessage("DEATH数： ".$death);
							$sender->sendMessage("羊毛設置回数： ".$block);
							$sender->sendMessage("＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝");
					}else{
    					$sender->sendMessage("§c指定されたプレイヤーのデーターが存在しません");
				}
			 }
		break;

		case "logout":
			$player = $sender->getPlayer();
			$PlayerName = $sender->getName();
			$sender->getInventory()->clearall();
			$sender->kick("§a正常にログアウトしました");
			$this->getLogger()->info(TextFormat::AQUA.$PlayerName."さんがコマンドでログアウトしました");
		break;

		case "r":
			$Name = $sender->getName();
			$sender->setNameTag("[§c赤§f]".$Name);
		break;

		case "b":
			$Name = $sender->getName();
			$sender->setNameTag("[§9青§f]".$Name);
		break;

		case "l":
			$x = $sender->getX();
			$y = $sender->getY();
			$z = $sender->getZ();
			$sender->getPlayer()->sendMessage("現在地： x=".$x." y=".$y." z=".$z);
		break;
		}
	}
}
class Start extends PluginTask{
public function __construct(PluginBase $owner, Server $server, Player $player) {
      parent::__construct($owner);
       $this->server = $server;
       $this->player = $player;
}

public function onRun($tick){
	$players = Server::getInstance()->getOnlinePlayers();
	$blue = "青";
	$red = "赤";
	foreach($players as $this->player){
		$PlayerNameTag = $this->player->getNameTag();
    	if(strstr($PlayerNameTag, $blue)){
    		$vector = new Vector3(0, 17, 3);
    		$this->player->setSpawn($vector);
    		$this->player->teleport($vector);

    		$this->player->getInventory()->setArmorItem(1,Item::get(315,0,1));
    		$this->player->getInventory()->setArmorItem(2,Item::get(300,0,1));
			$this->player->getInventory()->setArmorItem(3,Item::get(301,0,1));
			$this->player->getInventory()->sendArmorContents($this->player);

			$this->player->getInventory()->addItem($item);
			$item2 = Item::get(273, 0, 1);
			$this->player->getInventory()->addItem($item2);
			$item3 = Item::get(258, 0, 1);
			$this->player->getInventory()->addItem($item3);
			$item4 = Item::get(278, 0, 1);
			$this->player->getInventory()->addItem($item4);
			$item5 = Item::get(20, 0,32);
			$this->player->getInventory()->addItem($item5);
			$item6 = Item::get(17, 0,32);
			$this->player->getInventory()->addItem($item6);
			$item7 = Item::get(366, 0,32);
			$this->player->getInventory()->addItem($item7);
			$item8 = Item::get(345, 0,1);
			$this->player->getInventory()->addItem($item8);
    	}elseif(strstr($PlayerNameTag, $red)){
    		$vector2 = new Vector3(0, 17, -122);
    		$this->player->setSpawn($vector2);
    		$this->player->teleport($vector2);

    		$this->player->getInventory()->setArmorItem(1,Item::get(315,0,1));
    		$this->player->getInventory()->setArmorItem(2,Item::get(300,0,1));
			$this->player->getInventory()->setArmorItem(3,Item::get(301,0,1));
			$this->player->getInventory()->sendArmorContents($this->player);

			$this->player->getInventory()->addItem($item);
			$item2 = Item::get(273, 0, 1);
			$this->player->getInventory()->addItem($item2);
			$item3 = Item::get(258, 0, 1);
			$this->player->getInventory()->addItem($item3);
			$item4 = Item::get(278, 0, 1);
			$this->player->getInventory()->addItem($item4);
			$item5 = Item::get(20, 0,32);
			$this->player->getInventory()->addItem($item5);
			$item6 = Item::get(17, 0,32);
			$this->player->getInventory()->addItem($item6);
			$item7 = Item::get(366, 0,32);
			$this->player->getInventory()->addItem($item7);
			$item8 = Item::get(345, 0,1);
			$this->player->getInventory()->addItem($item8);
    	}
	}
		$this->server->broadcastMessage("§b【ゲーム情報】 ゲーム開始！");
		Server::getInstance()->getScheduler()->scheduleDelayedTask(new EndC($this->getOwner()), 36000);//ここを36000に変更
	}
}

class Shutdown extends PluginTask{
public function __construct(PluginBase $owner) {
      parent::__construct($owner);
   }

public function onRun($tick){
	Server::getInstance()->broadcastMessage("§bサーバーを終了させます");
	Server::getInstance()->shutdown();
   }
}

class EndC extends PluginTask{
public function __construct($owner) {
      parent::__construct($owner);
   }

public function onRun($tick){
	Server::getInstance()->broadcastMessage("§b1分後にサーバーを終了させます");
	Server::getInstance()->getScheduler()->scheduleDelayedTask(new End($this->getOwner()), 1200);
   }
}

class End extends PluginTask{
public function __construct(PluginBase $owner) {
      parent::__construct($owner);
   }

public function onRun($tick){
	Server::getInstance()->broadcastMessage("§bサーバーを終了させます");
	Server::getInstance()->shutdown();
   }
}
