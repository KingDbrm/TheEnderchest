<?php

namespace the\enderchest\commands;

use the\enderchest\utils\EnderChestUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class EnderchestCommand extends Command {

    public function __construct() {
        parent::__construct("enderchest", "Abrir enderchest por comando", null, ["echest", "ec"]);
        $this->setPermission("enderchest.command");
    }

    public function execute(CommandSender $sender, string $label, array $args): void {
        if (!($sender instanceof Player)){
            $sender->sendMessage("§cUse o comando no jogo!");
        }
        if (!$this->testPermission($sender)){
            $sender->sendMessage("§r§cVocê não tem permissão para isso.");
        }

        $utils = EnderChestUtils::getInstance();
        $utils->sendEnderchest($sender);
    }
}