<?php

namespace syouyu\denwa;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Main extends PluginBase implements Listener{

    private $denwa;
    private $denwaname;
    private $hassin;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onJoin(PlayerJoinEvent $event){
        $name = $event->getPlayer()->getName();
        $this->hassin[$name] = false;
        $this->denwa[$name] = false;
        $this->denwaname[$name] = null;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command) {
            case "denwa":
                if (!isset($args[0])) {
                    $sender->sendMessage("相手を指定してください。");
                } elseif ($args[0] !== "stop") {
                    $player = Server::getInstance()->getPlayer($args[0]);
                    if ($player instanceof Player) {
                        $this->denwaname[$sender->getName()] = $player->getName();
                        $this->denwaname[$player->getName()] = $sender->getName();
                        $this->hassin[$player->getName()] = true;
                        $player->sendMessage("{$sender->getName()}様から電話が来ました。\n応答なら/denwaok\n拒否なら/denwanoをしてください。");
                    }
                } else {
                    $player = Server::getInstance()->getPlayer($this->denwaname[$sender->getName()]);
                    $this->denwaname[$sender->getName()] = null;
                    $this->denwaname[$player->getName()] = null;
                    $this->hassin[$player->getName()] = false;
                    $this->hassin[$sender->getName()] = false;
                    $this->denwa[$player->getName()] = false;
                    $this->denwa[$sender->getName()] = false;
                }
                break;
            case "denwaok":
                if ($this->hassin[$sender->getName()] === false) {
                    $sender->sendMessage("あなた宛ての電話はありません。");
                } else {
                    $this->hassin[$sender->getName()] = null;
                    $player = Server::getInstance()->getPlayer($this->denwaname[$sender->getName()]);
                    $this->denwa[$sender->getName()] = true;
                    $this->denwa[$player->getName()] = true;
                    $player->sendMessage("許可されました");
                }
                break;
            case "denwano":
                if ($this->hassin[$sender->getName()] === false) {
                    $sender->sendMessage("あなた宛ての電話はありません。");
                } else {
                    $this->hassin[$sender->getName()] = null;
                    $player = Server::getInstance()->getPlayer($this->denwaname[$sender->getName()]);
                    $player->sendMessage("拒否されました");
                }
                break;
        }
        return true;
    }

    public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        if($this->denwa[$player->getName()] === true){
            $aite = Server::getInstance()->getPlayer($this->denwaname[$player->getName()]);
            $msg = $event->getMessage();
            $event->setCancelled();
            $aite->sendMessage("{$player->getName()}>>{$msg}");
            $players = Server::getInstance()->getOnlinePlayers();
            foreach ($players as $playe){
                if($playe->isOp()){
                    $playe->sendMessage($player->getName().">>".$msg);
                }
            }
        }
    }
}