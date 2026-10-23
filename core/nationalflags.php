<?php
/**
*
* @package National Flags
* @copyright (c) 2015 Rich McGirr(RMcGirr83)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace rmcgirr83\nationalflags\core;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\cache\service as cache;
use phpbb\db\driver\driver_interface as db;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\user;
use phpbb\extension\manager;
use phpbb\path_helper;
use phpbb\collapsiblecategories\operator\operator as cc_operator;
use Symfony\Component\HttpFoundation\JsonResponse;

class nationalflags
{
	protected $ext_path;
	protected $ext_path_web;
	protected $images;

	/** @var config $config */
	protected $config;

	/** @var helper $helper */
	protected $helper;

	/** @var cache $cache */
	protected $cache;

	/** @var db $db */
	protected $db;

	/** @var language $language */
	protected $language;

	/** @var template $template */
	protected $template;

	/** @var user $user */
	protected $user;

	/**
	 * The database table the flags are stored in
	 *
	 * @var string
	 */
	protected $flags_table;

	/** @var ext_manager $ext_manager */
	protected $ext_manager;

	/** @var path_helper $path_helper */
	protected $path_helper;

	/** @var array */
	protected $flag_constants;

	/** @var cc_operator */
	protected $cc_operator;

	/**
	 * Constructor
	 *
	 * @param config					$config				Config object
	 * @param helper					$helper				Controller helper object
	 * @param cache						$cache				Cache object
	 * @param db						$db					Database object
	 * @param language					$language			Language object
	 * @param template					$template			Template object
	 * @param user						$user				User object
	 * @param string					$flags_table		Name of the table used to store flag data
	 * @param ext_manager				$ext_manager		Extension manager object
	 * @param path_helper				$path_helper		Path helper object
	 * @param array						$flag_constants		Constants used by the extension
	 * @param cc_operator				$cc_operator
	 */
	public function __construct(
			config $config,
			helper $helper,
			cache $cache,
			db $db,
			language $language,
			template $template,
			user $user,
			string $flags_table,
			manager $ext_manager,
			path_helper $path_helper,
			array $flag_constants,
			flag_images $images,
			cc_operator $cc_operator = null)
	{
		$this->images = $images;
		$this->config = $config;
		$this->helper = $helper;
		$this->cache = $cache;
		$this->db = $db;
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;
		$this->flags_table = $flags_table;
		$this->ext_manager	 = $ext_manager;
		$this->path_helper	 = $path_helper;
		$this->flag_constants = $flag_constants;
		$this->cc_operator = $cc_operator;

		$this->ext_path = $this->ext_manager->get_extension_path('rmcgirr83/nationalflags', true);
		$this->ext_path_web = $this->path_helper->update_web_root_path($this->ext_path);
	}

	/**
	 * Get user flag
	 *
	 * @return string flag
	 */

	public function get_user_flag($flag_id = false, $size = 0)
	{
		$flags = $this->get_flag_cache();

		if ($flag_id && isset($flags[$flag_id]))
		{
			$flag_name = $this->flag_name_lang_var($flags[$flag_id]['flag_name']);
			$size = (!empty($size)) ? 'style="height:' . $size . 'px; width:auto;"' : '';
			$flag = '<img class="flag_image" src="' . $this->images->url($flags[$flag_id]['flag_image']) . '" ' . $size . ' alt="' . $flag_name . '" title="' . $flag_name . '" />';

			return $flag;
		}
		return false;
	}
	/**
	 * cache_flags
	 *
	 * Build the cache of the flags
	 *
	 * @return null
	 */

	public function cache_flags()
	{
		if (($this->cache->get('_user_flags')) === false)
		{
			$sql = 'SELECT *
				FROM ' . $this->flags_table . '
			ORDER BY flag_id';
			$result = $this->db->sql_query($sql);

			$user_flags = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$user_flags[$row['flag_id']] = [
					'flag_id'		=> $row['flag_id'],
					'flag_name'		=> $row['flag_name'],
					'flag_image'	=> $row['flag_image'],
					'flag_default'	=> $row['flag_default'],
				];
			}
			$this->db->sql_freeresult($result);

			// cache this data for ever, can only change in ACP
			$this->cache->put('_user_flags', $user_flags);
		}
	}

	/**
	 * Resolve a numeric ID or an unambiguous image country code to a flag ID.
	 * USA is accepted as an alias for US when no flag uses USA itself.
	 *
	 * @param string|int $identifier URL identifier
	 * @return int|false Flag ID, or false for an unknown or ambiguous identifier
	 */
	public function resolve_flag_id($identifier)
	{
		$flags = $this->get_flag_cache();
		$identifier = (string) $identifier;
		if (ctype_digit($identifier))
		{
			return isset($flags[$identifier]) ? (int) $identifier : false;
		}

		$code = strtoupper($identifier);
		if (!preg_match('/^[A-Z]{2,3}(?:-[A-Z]{2,3})?$/D', $code))
		{
			return false;
		}

		$matches = [];
		foreach ($flags as $id => $flag)
		{
			if (strtoupper(pathinfo($flag['flag_image'], PATHINFO_FILENAME)) === $code)
			{
				$matches[] = (int) $id;
			}
		}

		if (!$matches && $code === 'USA')
		{
			return $this->resolve_flag_id('US');
		}

		return count($matches) === 1 ? $matches[0] : false;
	}

	/**
	 * Get a readable URL identifier, retaining numeric IDs for custom or ambiguous flags.
	 *
	 * @param int $flag_id Stored flag ID
	 * @return string|int Country code or numeric ID
	 */
	public function flag_url_identifier($flag_id)
	{
		$flags = $this->get_flag_cache();
		if (isset($flags[$flag_id]))
		{
			$code = strtoupper(pathinfo($flags[$flag_id]['flag_image'], PATHINFO_FILENAME));
			if ($this->resolve_flag_id($code) === (int) $flag_id)
			{
				return $code;
			}
		}

		return (int) $flag_id;
	}

	/**
	 * Get list_flags
	 *
	 * @param int $flag_id
	 * @return string flag_options
	 */

	public function list_flags($flag_id = false)
	{
		$flag_is_set = false;

		$sql = 'SELECT *
			FROM ' . $this->flags_table . '
		ORDER BY flag_name';
		$result = $this->db->sql_query($sql);

		$flag_options = '<option value="0">' . $this->language->lang('FLAG_EXPLAIN') . '</option>';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$selected = ($row['flag_id'] == $flag_id && !$flag_is_set) ? ' selected="selected"' : '';
			if (!empty($selected))
			{
				$flag_is_set = true;
			}
			else if ($row['flag_default'] && !$flag_is_set)
			{
				$selected = ' selected="selected"';
			}

			$flag_name = $this->flag_name_lang_var($row['flag_name']);

			$flag_options .= '<option value="' . $row['flag_id'] . '" ' . $selected . '>' . $flag_name . '</option>';
		}
		$this->db->sql_freeresult($result);

		return $flag_options;
	}

	/**
	 * Get top_flags
	 * displayed on the index page
	 */
	public function top_flags()
	{
		// If setting in ACP is set to not allow guests and bots to view the flags
		if (!$this->display_flags_on_forum())
		{
			return false;
		}

		$cached_flags = $this->get_flag_cache();
		// Count the same eligible members as the country member list, while retaining
		// all users in the separate profile/post flag cache.
		$cached_user_flags = [];
		$sql = 'SELECT user_flag, COUNT(*) AS user_count
			FROM ' . USERS_TABLE . '
			WHERE user_flag > 0 AND ' . $this->db->sql_in_set('user_type', [USER_NORMAL, USER_FOUNDER]) . '
			GROUP BY user_flag';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$cached_user_flags[$row['user_flag']] = (int) $row['user_count'];
		}
		$this->db->sql_freeresult($result);

		if (sizeof($cached_user_flags))
		{
			$cached_flags_keys = array_keys($cached_flags);

			foreach ($cached_user_flags as $key => $value)
			{
				if (in_array($key, $cached_flags_keys))
				{
					$cached_flags[$key]['user_count'] = (int) $value;
				}
			}

			//need to add bogus user_count to cached_flags for the sort
			foreach ($cached_flags as $key => $value)
			{
				if (!isset($value['user_count']))
				{
					$cached_flags[$key]['user_count'] = 0;
				}
			}

			$user_count = array_column($cached_flags, 'user_count');
			$flag_name = array_column($cached_flags, 'flag_name');

			// Sort the data with user count descending, flag name ascending
			array_multisort($user_count, SORT_DESC, $flag_name, SORT_ASC, $cached_flags);

			//build the display of flags with user count
			$max_display = (int) $this->config['flags_num_display'];

			$count = 0;
			for ($i = 0; $i < $max_display; ++$i)
			{
				if (!empty($cached_flags[$i]['user_count']))
				{
					$this->template->assign_block_vars('flag', [
						'FLAG' 			=> $this->get_user_flag($cached_flags[$i]['flag_id']),
						'FLAG_USERS'	=> $this->user->lang('FLAG_USERS', (int) $cached_flags[$i]['user_count']),
						'U_FLAG'		=> $this->helper->route('rmcgirr83_nationalflags_getflags', ['flag_id' => $this->flag_url_identifier($cached_flags[$i]['flag_id'])]),
					]);

					++$count;
				}
			}

			if ($count)
			{
				if ($this->cc_operator !== null)
				{
					$fid = 'nationalflags'; // can be any unique string to identify your extension's collapsible element, must have version 2.0.0 of collapsible categories for this to work
					$this->template->assign_vars([
						'S_NATIONALFLAGS_HIDDEN' => $this->cc_operator->is_collapsed($fid),
						'U_NATIONALFLAGS_COLLAPSE_URL' => $this->cc_operator->get_collapsible_link($fid),
					]);
				}
				$this->template->assign_vars([
					'U_FLAGS'		=> $this->helper->route('rmcgirr83_nationalflags_display'),
					'S_FLAGS'		=> true
				]);
			}
		}
	}

	/**
	 * Display flag on change in ucp
	 * Ajax function
	 * @param $flag_id
	 *
	 * @return JsonResponse
	 */
	public function get_flag($flag_id)
	{
		$flags = $this->get_flag_cache();
		$flags_id = [];

		foreach ($flags as $id => $data)
		{
			$flags_id[] = $id;
		}
		if (!in_array($flag_id, $flags_id))
		{
			if ($this->config['flags_required'])
			{
				return new JsonResponse([
					'error' => $this->language->lang('MUST_CHOOSE_FLAG'),
				]);
			}
			else
			{
				return new JsonResponse([
					'error' => $this->language->lang('FLAG_NOT_EXIST'),
				]);
			}
		}

		$flag_img = $this->images->url($flags[$flag_id]['flag_image']);

		$flag_name = $this->flag_name_lang_var($flags[$flag_id]['flag_name']);

		$json = new JsonResponse([
				'flag_image'     => $flag_img,
				'flag_name'     => $flag_name,
		]);
		return $json;
	}

	/**
	 * Get the cache of the flags
	 *
	 * @return string flag_cache
	 * @access public
	 */
	public function get_flag_cache()
	{
		return $this->cache->get('_user_flags') ?: [];
	}

	/**
	 * Get the cache of the users and their flags
	 *
	 * @return string users and flags
	 * @access public
	 */
	public function get_users_and_flags_cache()
	{
		return $this->cache->get('_users_and_flags');
	}

	/**
	* Display Flag to guests
	*/
	public function display_flags_on_forum($setting = true)
	{
		if (!$setting)
		{
			return false;
		}
		if (!$this->config['flags_display_to_guests'])
		{
			$check_display = ($this->user->data['user_id'] == ANONYMOUS || !empty($this->user->data['is_bot'])) ? false : true;
			return $check_display;
		}

		return true;
	}

	/**
	* Flag position
	*/
	public function flag_display_position()
	{

		$flag_display_position = '';
		foreach ($this->flag_constants as $name => $value)
		{
			if ($value == $this->config['flag_position'])
			{
				$flag_display_position = 'FLAG_POSITION_' . strtoupper($name);
			}
		}
		return $flag_display_position;
	}

	/**
	* Build users and flags		A cache of user ids and the applicable flag id
	*
	* @return null
	* @access public
	*/
	public function build_users_and_flags()
	{
		if (($this->cache->get('_users_and_flags')) === false)
		{
			$sql = 'SELECT user_id, user_flag
				FROM ' . USERS_TABLE . '
				WHERE user_flag > 0';
			$result = $this->db->sql_query($sql);

			$users_and_flags = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$users_and_flags[$row['user_id']] = $row['user_flag'];
			}
			$this->db->sql_freeresult($result);

			// cache this data forever, cache is only destroyed when a user registers or updates their profile
			$this->cache->put('_users_and_flags', $users_and_flags);
		}
	}

	/**
	* User flag id		The flag id a user has chosen
	*
	* @param int user_id	$user_id	the user id
	* @param return			int 		the users flag id or false
	*/
	public function user_flag_id($user_id = 0)
	{
		$users_flag_array = $this->get_users_and_flags_cache();

		$users = [];
		$flag_id = 0;
		if (!empty($users_flag_array))
		{
			$users = array_keys($users_flag_array);
			$flag_id = isset($users_flag_array[$user_id]) ? intval($users_flag_array[$user_id]) : 0;
		}

		if (in_array($user_id, $users) && !empty($flag_id))
		{
			return $flag_id;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Display flag for user selection
	 *
	 * @param object $user_flag The users flag number
	 * @return null
	 * @access public
	 */
	public function display_flag_options($user_flag)
	{
		$flags = $this->get_flag_cache();
		$flags_id = [];

		foreach ($flags as $id => $data)
		{
			$flags_id[] = $id;
		}

		if (!in_array($user_flag, $flags_id))
		{
			$user_flag = 0;
		}
		$flag_name = $flag_image = '';

		foreach ($flags as $key => $value)
		{
			if ($value['flag_default'])
			{
				$flag_name = $value['flag_name'];
				$flag_image = $value['flag_image'];
			}
		}

		if ($user_flag)
		{
			$flag_name = $this->flag_name_lang_var($flags[$user_flag]['flag_name']);

			$flag_image = $flags[$user_flag]['flag_image'];
		}

		$s_flag_options = $this->list_flags($user_flag);

		$this->template->assign_vars([
			'USER_FLAG'			=> $user_flag,
			'FLAG_IMAGE'		=> ($flag_image) ? $this->images->url($flag_image) : '',
			'FLAG_NAME'			=> $flag_name,
			'S_FLAG_OPTIONS'	=> $s_flag_options,
			'S_FLAG_REQUIRED'	=> ($this->config['flags_required']) ? true : false,
			'AJAX_FLAG_INFO' 	=> $this->helper->route('rmcgirr83_nationalflags_getflag', ['flag_id' => $user_flag]),
		]);
	}

	/**
	* Trash the cache		destroys the users and flags cache
	*						called on registration and if a user changes their flag choice in profile
	*
	* @return null
	* @access public
	*/
	public function trash_the_cache()
	{
		$this->cache->destroy('_users_and_flags');
	}

	/**
	 * Check for translated flag name for the user
	 *
	 * @return string	translated flag name
	 * @access public
	 */
	public function flag_name_lang_var($flag_name = '')
	{
		if (array_key_exists((strtoupper(str_replace(" ", "_", trim($flag_name)))), $this->language->get_lang_array()))
		{
			$flag_name = html_entity_decode($this->language->lang(strtoupper(str_replace(" ", "_", trim($flag_name)))));
		}

		return $flag_name;
	}
}
