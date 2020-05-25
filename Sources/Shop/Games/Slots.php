<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Games;

use Shop\Shop;
use Shop\View\GamesRoom;
use Shop\Helper\Format;

if (!defined('SMF'))
	die('No direct access...');

class Slots extends GamesRoom
{
	/**
	 * @var array Array with the payouts.
	 */
	private $_faces = [];

	/**
	 * @var string Name of the game.
	 */
	private $_game;

	/**
	 * @var array Virtual "wheel" of the game.
	 */
	private $_wheel = [];

	/**
	 * @var array Starting values.
	 */
	private $_start = [];

	/**
	 * @var array Stopping values.
	 */
	private $_stop = [];

	/**
	 * @var array Results of the wheel.
	 */
	private $_result = [];

	/**
	 * @var bool If the user wins or not.
	 */
	private $_winner = false;

	/**
	 * Slots::__construct()
	 *
	 * Load the data for this game
	 */
	function __construct()
	{
		// Load previous info
		parent::__construct();

		// Set the images url
		$this->_images_dir .= 'slots/';

		// Set up the payouts
		$this->payouts();

		// Game details
		$this->details();

		// Initialize our wheel with 3 spins
		$this->_wheel = [
			1 => [],
			2 => [],
			3 => [],
		];
	}

	public function payouts()
	{
		$this->_faces = [
			'7' => '7',
			'bell' => 'bell',
			'cherry' => 'cherry',
			'lemon' => 'lemon',
			'orange' => 'orange',
			'plum' => 'plum',
			'dollar' => 'dollar',
			'melon' => 'melon',
			'grapes' => 'grapes',
		];
	}

	public function details()
	{
		global $context, $scripturl;

		// Images folder
		$context['shop_game_images'] = $this->_images_dir;

		// Linktree
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=games;play=slots',
			'name' => Shop::getText('games_slots'),
		];

		// Title and description
		$context['game']['title'] = Shop::getText('games_slots');
		$context['page_title'] .= ' - ' . $context['game']['title'];
		$context['page_description'] = Shop::getText('games_slots_desc');

		// Sub template
		$context['sub_template'] = 'games_play';

		// Faces
		$context['game']['faces'] = $this->_faces;

		// User cash
		$context['user']['games']['real_money'] = Format::cash($context['user']['shopMoney']);

		// Spin wheel
		$context['shop_game_spin'] = [true, 3];
	}

	public function play()
	{
		global $context, $modSettings, $user_info;

		if (isset($_REQUEST['do']))
		{
			// Check session
			checkSession();

			// Construct wheels
			foreach ($this->_faces as $face => $pay)
				$this->_wheel[1][] = $face;
			$this->_wheel[2] = array_reverse($this->_wheel[1]);
			$this->_wheel[3] = $this->_wheel[1];

			// Set to zero just in case
			list($this->_start[1], $this->_start[2], $this->_start[3]) = [0,0,0];

			// Value of each wheel
			$this->_stop[1] = mt_rand(count($this->_wheel[1])+$this->_start[1], 10*count($this->_wheel[1])) % count($this->_wheel[1]);
			$this->_stop[2] = mt_rand(count($this->_wheel[2])+$this->_start[2], 10*count($this->_wheel[2])) % count($this->_wheel[2]);
			$this->_stop[3] = mt_rand(count($this->_wheel[3])+$this->_start[3], 10*count($this->_wheel[3])) % count($this->_wheel[3]);

			// The results!!! Let's see if we are lucky
			$this->_result[1] = $this->_wheel[1][$this->_stop[1]];
			$this->_result[2] = $this->_wheel[2][$this->_stop[2]];
			$this->_result[3] = $this->_wheel[3][$this->_stop[3]];

			// Use these results
			$context['shop_game']['wheel'] = $this->_result;

			// By default user's a loser
			$context['game_result'] = [$this->_winner, sprintf(Shop::getText('games_loser'), Format::cash($modSettings['Shop_settings_slots_losing']))];

			// You are very lucky
			if ($this->_result[1] == $this->_result[2] && $this->_result[2] == $this->_result[3])
			{
				// Winner
				$this->_winner = true;

				// The user is a winner
				$context['game_result'] = [$this->_winner, sprintf(Shop::getText('games_winner'), Format::cash($modSettings['Shop_settings_slots_' . $this->_faces[$this->_result[1]]]))];
			}

			// Update user cash
			//$this->_log->game($user_info['id'], (!empty($this->_winner) ? $modSettings['Shop_settings_slots_' . $this->_faces[$this->_result[1]]] : ((-1) * $modSettings['Shop_settings_slots_losing'])), $_REQUEST['play']);

			// User real money
			$context['user']['games']['real_money'] = Format::cash(empty($this->_winner) ? ($user_info['shopMoney'] - $modSettings['Shop_settings_slots_losing']) : ($user_info['shopMoney'] + $modSettings['Shop_settings_slots_' . $this->_faces[$this->_result[1]]]));
		}
	}
}