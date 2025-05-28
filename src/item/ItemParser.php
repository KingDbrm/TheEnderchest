<?php

declare(strict_types=1);

namespace item;

use enchantment\glow\EnchantmentGlow;
use ErrorException;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use function count;
use function is_array;
use function is_int;
use function is_numeric;
use function is_string;

final class ItemParser {

    public static function getItemIdentifier(Item $item): string {
        return $item->getVanillaName();
    }
    
    /**
	 * Returns an array of item stack properties that can be serialized to json.
	 *
	 * @return mixed[]
	 * @phpstan-return array{id: int, name: string, custom-name: string, count?: int, nbt_b64?: string}
	 */
	public static function jsonSerialize(Item $item) : array{
        $data = [
			"id" => StringToItemParser::getInstance()->lookupAliases($item)[0],
            "custom-name" => $item->getCustomName()
		];
		if($item->getCount() !== 1){
			$data["count"] = $item->getCount();
		}
		if($item->hasNamedTag()){
			$data["nbt_b64"] = base64_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($item->getNamedTag())));
		}
		return $data;
	}

	public static function jsonDeserialize(array $data): Item {
		if (empty($data)){
			throw new ErrorException("Invalid item data");
		}
		$item = ItemFactory::get($data["id"]);
        $item->setCustomName($data["custom-name"]);
		$count = 1;
		if (isset($data["count"])){
			$count = $data["count"];
		}
		$item->setCount($count);
		$nbt = "";

		//Backwards compatibility
		if(isset($data["nbt"])){
			$nbt = $data["nbt"];
		}elseif(isset($data["nbt_hex"])){
			$nbt = hex2bin($data["nbt_hex"]);
		}elseif(isset($data["nbt_b64"])){
			$nbt = base64_decode($data["nbt_b64"], true);
		}
		
		$compound = $nbt !== "" ? (new LittleEndianNbtSerializer())->read($nbt)->mustGetCompoundTag() : null;
		if ($compound !== null) $item->setNamedTag($compound);
		return $item;
	}
}
