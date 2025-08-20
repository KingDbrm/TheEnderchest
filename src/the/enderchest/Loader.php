<?php

namespace the\enderchest;

use block\BlockRegistry;
use the\enderchest\blocks\EnderChestBlock;
use the\enderchest\commands\EnderchestCommand;
use the\enderchest\database\PlayerDatabase;
use the\enderchest\utils\EnderChestUtils;
use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

final class Loader extends PluginBase {
    use SingletonTrait;

    protected PlayerDatabase $database;

    protected function onEnable(): void {
        self::setInstance($this);
        
        CustomSizedInvMenu::init($this);
        new EnderChestUtils($this);

        $this->registerCommands();
        $this->registerBlocks();

        $this->database = new PlayerDatabase($this->getDataFolder());

        $this->getLogger()->notice("Plugin created by KingDbrm");
    }

    public function registerCommands(): void {
        Server::getInstance()->getCommandMap()->register('enderchests', new EnderchestCommand($this));
    }

    public function registerBlocks(): void {
        $vanillaBlock = VanillaBlocks::ENDER_CHEST();
        $block = new EnderChestBlock(
            $vanillaBlock->getIdInfo(),
            "Ender Chest",
            new BlockTypeInfo($vanillaBlock->getBreakInfo())
        );
        BlockRegistry::override($block, BlockTypeNames::ENDER_CHEST);
    }

    public function getPlayerDatabase(): PlayerDatabase {
        return $this->database;
    }
}