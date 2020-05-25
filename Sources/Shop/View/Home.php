<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\View;

use Shop\Shop;
use Shop\Helper\Format;

if (!defined('SMF'))
	die('No direct access...');

class Home
{
	/**
	 * @var array Tabs array with the navegation of the shop.
	 */
	var $_shop_tabs = [];

	/**
	 * @var array Actions array for each section/area of the shop.
	 */
	protected $_actions = [];

	/**
	 * @var string The current area/action.
	 */
	protected $_sa;

	function __construct()
	{
		// Load language files
		loadLanguage('Shop/Shop');
		loadLanguage('Shop/Errors');

		// Load template file
		loadTemplate('Shop/Shop');

		// Build the array of actions
		$this->actions();

		// Shop tabs
		$this->tabs();
	}

	public function actions()
	{
		// Big array of actions
		$this->_actions = [
			'home' =>  'Home::portal',
			'buy' => 'Buy::main',
			'buy2' => 'Buy::purchase',
			'inventory' => 'Inventory::main',
			'search' => 'Inventory::search',
			'search2' => 'Inventory::search_inventory',
			'invuse' => 'Inventory::use',
			'invused' => 'Inventory::used',
			'invfav' => 'Inventory::fav',
			'owners' => 'Inventory::owners',
			'invdisp' => 'Inventory::display_extend',
			'gift' => 'Gift::main',
			'senditem' => 'Gift::main',
			'sendmoney' => 'Gift::main',
			'gift2' => 'Gift::send',
			'bank' => 'Bank::main',
			'bank2' => 'Bank::trans',
			'invtrade' => 'Trade::set',
			'invtrade2' => 'Trade::set2',
			'trade' => 'Trade::main',
			'trade2' => 'Trade::transaction',
			'traderemove' => 'Trade::remove',
			'tradelist' => 'Trade::list',
			'mytrades' => 'Trade::list',
			'tradesearch' => 'Inventory::search_inventory',
			'tradelog' => 'Trade::log',
			'stats' => 'Stats::main',
			'games' => 'GamesRoom::main',
		];
		$this->_sa = isset($_GET['sa'], $this->_actions[$_GET['sa']]) ? $_GET['sa'] : 'home';

		// More sections?
		call_integration_hook('integrate_shop_home_actions', array(&$this->_actions, &$this->_sa));
	}

	public function tabs()
	{
		$this->_shop_tabs = [
			'home' => [
				'action' => ['home'],
				'label' => Shop::getText('main_home'),
				'permission' => 'shop_canAccess',
				'enable' => 'Shop_enable_shop'
			],
			'buy' => [
				'action' => ['buy','buy2','buy3', 'whohas'],
				'label' => Shop::getText('main_buy'),
				'permission' => 'shop_canBuy',
				'enable' => 'Shop_enable_shop'
			],
			'inventory' => [
				'action' => ['inventory', 'invdisp', 'invuse', 'invused', 'owners','search','search2'],
				'label' => Shop::getText('main_inventory'),
				'permission' => 'shop_canBuy',
				'enable' => 'Shop_enable_shop'
			],
			'gift' => [
				'action' => ['gift','senditem','sendmoney','gift2','gift3'],
				'label' => Shop::getText('main_gift'),
				'permission' => 'shop_canGift',
				'enable' => 'Shop_enable_gift'
			],
			'bank' => [
				'action' => ['bank', 'bank2'],
				'label' => Shop::getText('main_bank'),
				'permission' => 'shop_canBank',
				'enable' => 'Shop_enable_bank'
			],
			'trade' => [
				'action' => ['trade', 'tradelist', 'mytrades', 'tradelog', 'trade2','trade3','traderemove', 'invtrade', 'invtrade2',],
				'label' => Shop::getText('main_trade'),
				'permission' => 'shop_canBuy',
				'enable' => 'Shop_enable_trade'
			],
			'games' => [
				'action' => ['games'],
				'label' => Shop::getText('main_games'),
				'permission' => 'shop_playGames',
				'enable' => 'Shop_enable_games'
			],
			'stats' => [
				'action' => ['stats'],
				'label' => Shop::getText('main_stats'),
				'permission' => 'shop_viewStats',
				'enable' => 'Shop_enable_stats'
			],
		];
		// More tabs?
		call_integration_hook('integrate_shop_home_tabs', [&$this->_shop_tabs]);
	}

	public function main()
	{
		global $context, $scripturl, $modSettings, $user_info;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button');
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=shop',
			'name' => Shop::getText('main_button'),
		);

		// What if the Shop is disabled? User shouldn't be able to access the Shop
		if (empty($modSettings['Shop_enable_shop']))
			fatal_error(Shop::getText('currently_disabled'), false);

		// Last but not less important. Are they actually allowed to Access the Shop? If not.. YOU SHALL NOT PASS. 
		// Anyway if user can Manage the Shop, there's no problem :).
		if (!empty($modSettings['Shop_enable_shop']) && !allowedTo('shop_canAccess') && !allowedTo('shop_canManage'))
			isAllowedTo('shop_canAccess');

		// Maintenance. Only Shop admins can access.
		if (!empty($modSettings['Shop_enable_shop']) && !empty($modSettings['Shop_enable_maintenance']) && allowedTo('shop_canAccess') && !allowedTo('shop_canManage'))
			fatal_error(Shop::getText('currently_maintenance'), false);

		// Games Pass, get the days!
		$context['user']['gamedays'] = ($user_info['gamesPass'] <= time() || empty($user_info['gamesPass']) ? 0 : Format::gamespass($user_info['gamesPass']));

		// Lovely copyright in shop pages
		$context['shop']['copyright'] = $this->copyright();
		// Shop tabs
		$context['shop']['tabs'] = $this->_shop_tabs;

		// Invoke the function
		call_helper(__NAMESPACE__ . '\\' . $this->_actions[$this->_sa] . '#');
	}

	public function portal()
	{
		global $context, $user_info, $modSettings, $scripturl;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('main_home');
		$context['sub_template'] = 'home';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=shop;sa=home',
			'name' => Shop::getText('main_home'),
		);

		// Forum name + Shop
		$context['shop']['forum_welcome'] = sprintf(Shop::getText('welcome_to'), $context['forum_name']);

		// Welcome message
		$context['shop']['welcome'] = sprintf(Shop::getText('welcome_text'), $user_info['name'], $modSettings['Shop_credits_suffix']);

		// Profile action??
		if (isset($_REQUEST['u']) && !empty($_REQUEST['u']))
			redirectexit('action=shop;sa=gift;u='.$_REQUEST['u']);

		// Display some general stats
		/*$context['home_stats'] = array(
			// Last items added
			'last_added' => array(
				'label' => $txt['Shop_stats_last_added'],
				'icon' => 'last_added.png',
				'function' => ShopStats::LastItems(),
				'enabled' => true,
			),
			// Last items bought
			'last_bought' => array(
				'label' => $txt['Shop_stats_last_bought'],
				'icon' => 'last_bought.png',
				'function' => ShopStats::LastBought(),
				'enabled' => allowedTo('shop_canBuy'),
			),
			// Richest pocket
			'richest_pocket' => array(
				'label' => $txt['Shop_stats_richest_pocket'],
				'icon' => 'richest_pocket.png',
				'function' => ShopStats::Richest('pocket'),
				'enabled' => true,
			),
			// Richest bank
			'richest_bank' => array(
				'label' => $txt['Shop_stats_richest_bank'],
				'icon' => 'richest_bank.png',
				'function' => ShopStats::Richest('bank'),
				'enabled' => allowedTo('shop_canBank') && !empty($modSettings['Shop_enable_bank']),
			),
		);*/
	}




	public static function logGames($userid, $amount, $game = 'slots')
	{
		global $smcFunc;

		// Add/remove the amount from the player
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET	shopMoney = shopMoney + {int:amount}
			WHERE id_member = {int:userid}',
			array(
				'userid' => $userid,
				'amount' => $amount,
			)
		);

		// Insert the information in the log
		$smcFunc['db_insert']('',
			'{db_prefix}shop_log_games',
			array(
				'userid' => 'int',
				'amount' => 'int',
				'game' => 'string',
				'date' => 'int',
			),
			array(
				$userid,
				$amount,
				$game,
				time()
			),
			array()
		);
	}



	/**
	 * Shop::copyright()
	 *
	 * Used in the credits action.
	 * @param boolean $return decide between returning a string or append it to a known context var.
	 * @return string A link for copyright notice
	 */
	public function copyright($return = false)
	{
		return '
			<br /><div style="text-align: center;"><span class="smalltext">Powered by <a href="https://smftricks.com" target="_blank" rel="noopener">ST Shop</a></span></div>';
	}
}