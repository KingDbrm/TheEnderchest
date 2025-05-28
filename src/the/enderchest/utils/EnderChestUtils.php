<?php

namespace the\enderchest\utils;

use the\enderchest\Loader;
use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\Block;
use pocketmine\inventory\Inventory;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class EnderChestUtils {
    use SingletonTrait;

    public function __construct(private Loader $loader){
        self::setInstance($this);
    }

    public function getLoader(): Loader {
        return $this->loader;
    }

    protected function animateBlock(bool $isOpen, Block $block) : void{
		$holder = $block->getPosition();
		$holder->getWorld()->broadcastPacketToViewers($holder, BlockEventPacket::create(BlockPosition::fromVector3($holder), 1, $isOpen ? 1 : 0));
	}

    function createInvMenu(Player $player): ?InvMenu {
        $size = 27;
        foreach (["36.echest.slots" => 36, "45.echest.slots" => 45, "54.echest.slots" => 54, "63.echest.slots" => 63] as $perm => $slots) {
            if ($player->hasPermission($perm)) { $size = $slots; break; }
        }
        return $size === 27 ? InvMenu::create(InvMenuTypeIds::TYPE_CHEST)
            : ($size === 54 ? InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST)
            : CustomSizedInvMenu::create($size));
    }

    public function getEnderchestName(Player $player): string {
        if ($this->getLoader()->getConfig()->exists("enderchest-name")){
            return (string) str_replace("{player}", $player->getName(), $this->getLoader()->getConfig()->get("enderchest-name"));
        }
        return "Enderchest";
    }

    public function sendEnderchest(Player $player, ?Block $block = null): void {
        $playerDatabase = $this->getLoader()->getPlayerDatabase();
        $menu = $this->createInvMenu($player);
        $name = $this->getEnderchestName($player);
        $menu->setName($name);
        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($block, $playerDatabase): void {
            $playerDatabase->saveEnderChest($player, $inventory);
            if ($block !== null) $this->animateBlock(false, $block);
        });

        $inventory = $menu->getInventory();
        $playerDatabase->loadEnderChest($player, $inventory);
        $menu->send($player);
        if ($block !== null) $this->animateBlock(true, $block);
    }
}