<?php

namespace the\enderchest\commands;

use rajadordev\smartcommand\command\CommandArguments;
use rajadordev\smartcommand\command\SmartCommand;
use the\enderchest\Loader;
use the\enderchest\utils\EnderChestUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class EnderchestCommand extends SmartCommand {

    public function __construct(private Loader $loader) {
        parent::__construct($loader, "enderchest", "Abrir enderchest por comando", self::DEFAULT_USAGE_PREFIX, ["echest", "ec"]);
    }

    public function onRun(CommandSender $sender, string $label, CommandArguments $args): void {
        if (!($sender instanceof Player)){
            $sender->sendMessage("§cUse o comando no jogo!");
        }
        if (!$this->testPermission($sender)){
            $sender->sendMessage("§r§cVocê não tem permissão para isso.");
        }

        $utils = EnderChestUtils::getInstance();
        $utils->sendEnderchest($sender);
    }

    public function prepare(): void {
        $this->setPrefix('§l§eENDERCHEST §r§7');
    }

    public function getRuntimePermission(): string {
        return "enderchest.command";
    }
}