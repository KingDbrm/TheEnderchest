<?php

declare(strict_types=1);

namespace muqsit\customsizedinvmenu;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\cache\StaticPacketCache;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use RuntimeException;
use function array_rand;
use function assert;
use function is_numeric;

final class CustomSizedInvMenu {

	private const RESOURCE_PACK_ID = "21f0427f-572a-416d-a90e-c5d9becb0fa3";
	private const TYPE_DYNAMIC_PREFIX = "muqsit:customsizedinvmenu_";

	public static function create(int $size) : InvMenu{
		static $ids_by_size = [];
		if(!isset($ids_by_size[$size])){
			$id = self::TYPE_DYNAMIC_PREFIX . $size;
			InvMenuHandler::getTypeRegistry()->register($id, CustomSizedInvMenuType::ofSize($size));
			$ids_by_size[$size] = $id;
		}
		return InvMenu::create($ids_by_size[$size]);
	}

	public static function init(PluginBase $plugin) : void{
		if($plugin->getServer()->getResourcePackManager()->getPackById(self::RESOURCE_PACK_ID) === null){
			$plugin->getLogger()->warning("Resource pack 'Inventory UI Resource Pack' could not be found.");
			$plugin->getLogger()->warning("This plugin cannot be loaded. Please download the resource pack from: https://github.com/tedo0627/InventoryUIResourcePack");
			//throw new RuntimeException("Resource pack 'Inventory UI Resource Pack' has not been loaded");
		}

		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($plugin);
		}

		$packet = StaticPacketCache::getInstance()->getAvailableActorIdentifiers();
		$tag = $packet->identifiers->getRoot();
		assert($tag instanceof CompoundTag);
		$id_list = $tag->getListTag("idlist");
		assert($id_list !== null);
		$id_list->push(CompoundTag::create()
			->setString("bid", "")
			->setByte("hasspawnegg", 0)
			->setString("id", CustomSizedInvMenuType::ACTOR_NETWORK_ID)
			->setByte("summonable", 0)
		);
	}
}