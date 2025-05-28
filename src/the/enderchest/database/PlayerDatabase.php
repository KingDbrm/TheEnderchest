<?php

namespace the\enderchest\database;

use item\ItemParser;
use pocketmine\player\Player;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\inventory\Inventory;
use pocketmine\Server;

class PlayerDatabase {

    private string $dataFolder;

    public function __construct(string $pluginDataFolder) {
        $this->dataFolder = $pluginDataFolder . "enderchest/";
        @mkdir($this->dataFolder, 0777, true);
    }

    public function saveEnderChest(Player $player, Inventory $inventory): void {
        $itemsData = [];
        foreach ($inventory->getContents() as $slot => $item) {
            $itemsData[$slot] = ItemParser::jsonSerialize($item);
        }

        $config = new Config($this->getPlayerFile($player), Config::YAML);
        $config->set("enderchest", $itemsData);
        $config->save();
    }

    public function loadEnderChest(Player $player, Inventory $inventory): void {
        $file = $this->getPlayerFile($player);
        if (!file_exists($file)) return;

        $config = new Config($file, Config::YAML);
        $itemsData = $config->get("enderchest", []);

        $inventory->clearAll();
        foreach ($itemsData as $slot => $itemData) {
            $item = ItemParser::jsonDeserialize($itemData);
            $inventory->setItem((int)$slot, $item);
        }
    }

    private function getPlayerFile(Player $player): string {
        return $this->dataFolder . strtolower($player->getName()) . ".yml";
    }
}
