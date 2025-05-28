<?php

declare(strict_types=1);

namespace item;

use Logger;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use function count;
use function is_array;
use function is_int;
use function is_numeric;
use function is_string;

final class ItemFactory {

    public static function getItemIdentifier(Item $item): string {
        return $item->getVanillaName();
    }

    public static function get(string|int $id, int $meta = 0, int $count = 1, ?CompoundTag $tags = null) : Item{
        $item = null;
        if (is_string($id)) {
            $explodeID = explode(":", $id, 2);
            if ($explodeID[0] === "minecraft") {
                $id = $explodeID[1];
            }
            try {
                $item = StringToItemParser::getInstance()->parse($id) ?? LegacyStringToItemParser::getInstance()->parse($id);
            } catch (LegacyStringToItemParserException $e){
                Server::getInstance()->getLogger()->warning($e->getMessage());
            }
        } else {
            try {
                $item = StringToItemParser::getInstance()->parse("{$id}:{$meta}") ?? LegacyStringToItemParser::getInstance()->parse("{$id}:{$meta}");
            } catch (LegacyStringToItemParserException $e){
                Server::getInstance()->getLogger()->warning($e->getMessage());
            }
        }
        if ($item == null){
            $item = VanillaItems::PAPER();
            Server::getInstance()->getLogger()->error("{$id} não é um id de item.");
        }
        $item->setCount($count);
        if($tags !== null){
            $item->setNamedTag($tags);
        }
        return $item;
    }
}