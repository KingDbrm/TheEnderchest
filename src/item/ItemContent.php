<?php

declare(strict_types=1);

namespace item;

use pocketmine\data\SavedDataLoadingException;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\SingletonTrait;
use function count;
use function min;

class ItemContent {
	use SingletonTrait;

	/** @var array<int, Item> $contents */
	public array $contents = [];
	protected int $maxStackSize = Inventory::MAX_STACK;

	public function __construct() {
		self::setInstance($this);
	}

	/** @param array<int, array>|Tag $items */
	public function loadItems(array|Tag $items = [], bool $compound = false): void {
		$contents = [];
		$useTag = $compound || $items instanceof Tag;

		foreach ($items as $slot => $value) {
			if ($useTag) {
				try {
					$item = ItemSerializer::nbtDeserialize($value);
				} catch (SavedDataLoadingException) {
					continue;
				}
				$contents[$value->getInt('Slot')] = $item;
			} else {
				$contents[$slot] = ItemSerializer::deserialize($value);
			}
		}
		$this->contents = $contents;
	}

	/** @return array<int, array|CompoundTag>|Tag[]|null */
	public function getItems(bool $compound = false): ?array {
		if (!isset($this->contents)) {
			return null;
		}
		$items = [];

		foreach ($this->contents as $slot => $item) {
			if ($compound) {
				$items[] = ItemSerializer::nbtSerialize($item, $slot);
			} else {
				$items[$slot] = ItemSerializer::serialize($item);
			}
		}
		return $items;
	}

	/** @return Item[] */
	public function getContents(): array {
		return $this->contents;
	}

	/** @param Item[] $contents */
	public function setContents(array $contents): void {
		$this->contents = $contents;
	}

	public function canAddItem(Item $item): bool {
		return $this->getAddableItemQuantity($item) === $item->getCount();
	}

	public function getAddableItemQuantity(Item $item): int {
		$count = $item->getCount();
		$maxStackSize = min($this->getMaxStackSize(), $item->getMaxStackSize());

		for ($i = 0, $size = $this->getSize(); $i < $size; ++$i) {
			if ($this->isSlotEmpty($i)) {
				$count -= $maxStackSize;
			} else {
				$slotCount = $this->getMatchingItemCount($i, $item, true, true);

				if ($slotCount > 0 && ($diff = $maxStackSize - $slotCount) > 0) {
					$count -= $diff;
				}
			}

			if ($count <= 0) {
				return $item->getCount();
			}
		}

		return $item->getCount() - $count;
	}

	public function getMaxStackSize(): int {
		return $this->maxStackSize;
	}

	public function getSize(): int {
		return count($this->contents) + 30;
	}

	public function isSlotEmpty(int $slot): bool {
		return !isset($this->contents[$slot]);
	}

	protected function getMatchingItemCount(int $slot, Item $test, bool $checkDamage, bool $checkTags): int {
		$item = $this->getItem($slot);
		return $item->equals($test, $checkDamage, $checkTags) ? $item->getCount() : 0;
	}

	public function getItem(int $slot): Item {
		return $this->contents[$slot];
	}

	public function addItem(Item ...$slots): array {
		/** @var Item[] $itemSlots */
		/** @var Item[] $slots */
		$itemSlots = [];

		foreach ($slots as $slot) {
			if (!$slot->isNull()) {
				$itemSlots[] = clone $slot;
			}
		}
		/** @var Item[] $returnSlots */
		$returnSlots = [];

		foreach ($itemSlots as $item) {
			$leftover = $this->internalAddItem($item);

			if (!$leftover->isNull()) {
				$returnSlots[] = $leftover;
			}
		}

		return $returnSlots;
	}

	private function internalAddItem(Item $newItem): Item {
		$emptySlots = [];
		$maxStackSize = min($this->getMaxStackSize(), $newItem->getMaxStackSize());

		for ($i = 0, $size = $this->getSize(); $i < $size; ++$i) {
			if ($this->isSlotEmpty($i)) {
				$emptySlots[] = $i;
				continue;
			}
			$slotCount = $this->getMatchingItemCount($i, $newItem, true, true);

			if ($slotCount === 0) {
				continue;
			}

			if ($slotCount < $maxStackSize) {
				$amount = min($maxStackSize - $slotCount, $newItem->getCount());

				if ($amount > 0) {
					$newItem->setCount($newItem->getCount() - $amount);
					$slotItem = $this->getItem($i);
					$slotItem->setCount($slotItem->getCount() + $amount);
					$this->setItem($i, $slotItem);

					if ($newItem->getCount() <= 0) {
						break;
					}
				}
			}
		}

		if (count($emptySlots) > 0) {
			foreach ($emptySlots as $slotIndex) {
				$amount = min($maxStackSize, $newItem->getCount());
				$newItem->setCount($newItem->getCount() - $amount);
				$slotItem = clone $newItem;
				$slotItem->setCount($amount);
				$this->setItem($slotIndex, $slotItem);

				if ($newItem->getCount() <= 0) {
					break;
				}
			}
		}

		return $newItem;
	}

	public function setItem(int $index, Item $item): void {
		if ($item->isNull()) {
			$item = VanillaItems::AIR();
		} else {
			$item = clone $item;
		}
		$this->internalSetItem($index, $item);
	}

	protected function internalSetItem(int $index, Item $item): void {
		$this->contents[$index] = $item;
	}
}
