<?php

declare(strict_types=1);

namespace item;

use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use function base64_decode;
use function base64_encode;

final class ItemSerializer {

	private static BigEndianNbtSerializer $nbtSerializer;

	public static function init(): void {
		self::$nbtSerializer = new BigEndianNbtSerializer();
	}

	public static function serialize(Item $item): string {
		return base64_encode(self::$nbtSerializer->write(new TreeRoot($item->nbtSerialize())));
	}

	public static function nbtSerialize(Item $item, int $slot = -1): CompoundTag {
		return $item->nbtSerialize($slot);
	}

	public static function deserialize(string $string): Item {
		return self::nbtDeserialize(self::$nbtSerializer->read(base64_decode($string, true))->mustGetCompoundTag());
	}

	public static function nbtDeserialize(CompoundTag $tag): Item {
		return Item::nbtDeserialize($tag);
	}
}