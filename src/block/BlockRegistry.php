<?php

declare(strict_types=1);

namespace block;

use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\CraftingManagerFromDataHelper;
use pocketmine\data\bedrock\item\ItemDeserializer;
use pocketmine\data\bedrock\item\ItemSerializer;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\Server;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use Symfony\Component\Filesystem\Path;
use function explode;
use function mb_strtoupper;
use function str_starts_with;
use const pocketmine\BEDROCK_DATA_PATH;

final class BlockRegistry {

	public static function override(
		Block   $block,
		string  $blockName,
		?string $vanillaBlocksReference = null
	) : void {
		if (!str_starts_with($blockName, "minecraft:")) {
			throw new \InvalidArgumentException("The block name is invalid.");
		}
		if ($vanillaBlocksReference === null) {
			$vanillaBlocksReference = explode(":", $blockName)[1];
		}

		(function(Block $block) : void {
			/** @var RuntimeBlockStateRegistry $this */
			$typeId = $block->getTypeId();
			$this->typeIndex[$typeId] = clone $block;

			foreach ($block->generateStatePermutations() as $v) {
				$this->fillStaticArrays($v->getStateId(), $v);
			}
		})->call(RuntimeBlockStateRegistry::getInstance(), $block);

		$reflection = new \ReflectionClass(VanillaBlocks::class);
		/** @var array<string, Block> $blocks */
		$blocks = $reflection->getStaticPropertyValue("members");
		$blocks[mb_strtoupper($vanillaBlocksReference)] = clone $block;
		$reflection->setStaticPropertyValue("members", $blocks);

		(function(string $id, \Closure $deserializer) : void {
			/**
			 * @var ItemDeserializer $this
			 */
			$this->deserializers[$id] = $deserializer;
		})->call(
			GlobalItemDataHandlers::getDeserializer(),
			$blockName,
			fn(SavedItemData $data) => $block->asItem()
		);
		(function(Block $block, \Closure $serializer) : void {
			/**
			 * @var ItemSerializer $this
			 */
			$this->blockItemSerializers[$block->getTypeId()] = $serializer;
		})->call(
			GlobalItemDataHandlers::getSerializer(),
			$block,
			fn() => new SavedItemData($blockName)
		);

		CreativeInventory::reset();
		CreativeInventory::getInstance();

		(function() : void {
			/**
			 * @var Server $this
			 */
			$this->craftingManager = CraftingManagerFromDataHelper::make(Path::join(BEDROCK_DATA_PATH, "recipes"));
		})->call(Server::getInstance());
	}

	public static function overrideAblock(
		Block   $block,
		string  $blockName,
		?string $vanillaBlocksReference = null
	) : void {
		if (!str_starts_with($blockName, "minecraft:")) {
			throw new \InvalidArgumentException("The block name is invalid.");
		}
		if ($vanillaBlocksReference === null) {
			$vanillaBlocksReference = explode(":", $blockName)[1];
		}

		(function(Block $block) : void {
			/** @var RuntimeBlockStateRegistry $this */
			$typeId = $block->getTypeId();
			$this->typeIndex[$typeId] = clone $block;

			foreach ($block->generateStatePermutations() as $v) {
				$this->fillStaticArrays($v->getStateId(), $v);
			}
		})->call(RuntimeBlockStateRegistry::getInstance(), $block);

		$reflection = new \ReflectionClass(VanillaBlocks::class);
		/** @var array<string, Block> $blocks */
		$blocks = $reflection->getStaticPropertyValue("members");
		$blocks[mb_strtoupper($vanillaBlocksReference)] = clone $block;
		$reflection->setStaticPropertyValue("members", $blocks);
	}
}
