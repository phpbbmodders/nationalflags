<?php
/**
*
* National Flags extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Rich McGirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\nationalflags\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\db\driver\driver_interface as db;
use phpbb\pagination;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\extension\manager;
use phpbb\path_helper;
use phpbb\template\template;
use phpbb\user;
use rmcgirr83\nationalflags\core\nationalflags;
use phpbb\files\factory;
use phpbb\exception\http_exception;

/**
* Main controller
*/
class main_controller
{
	protected $ext_path;

	/** @var auth $auth */
	protected $auth;

	/** @var config $config */
	protected $config;

	/** @var db $db */
	protected $db;

	/** @var pagination $pagination */
	protected $pagination;

	/** @var helper $helper */
	protected $helper;

	/** @var language $language */
	protected $language;

	/* @var request $request */
	protected $request;

	/** @var ext_manager $ext_manager */
	protected $ext_manager;

	/** @var path_helper $path_helper */
	protected $path_helper;

	/** @var template $template */
	protected $template;

	/** @var user $user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	* The database table the flags are stored in
	*
	* @var string
	*/
	protected $flags_table;

	/* @var nationalflags $nationalflags */
	protected $nationalflags;

	/** @var files_factory $files_factory */
	protected $files_factory;

	const MAX_SIZE = 30; // Max size img

	/**
	* Constructor
	*
	* @param auth					$auth			Auth object
	* @param config               	$config         Config object
	* @param db						$db				Database object
	* @param pagination				$pagination		Pagination object
	* @param helper           		$helper         Controller helper object
	* @param language           	$language       Language object
	* @param request				$request		Request object
	* @param ext_manager			$ext_manager	Extension manager object
	* @param path_helper			$path_helper	Path helper object
	* @param template           	$template       Template object
	* @param user                   $user           User object
	* @param string                 $root_path      phpBB root path
	* @param string                 $php_ext        phpEx
	* @param string					$flags_table	Name of the table used to store flag data
	* @param nationalflags			$nationalflags	methods to be used by class
	* @param files_factory			$files_factory	File classes factory
	* @access public
	*/
	public function __construct(
			auth $auth,
			config $config,
			db $db,
			pagination $pagination,
			helper $helper,
			language $language,
			request $request,
			manager $ext_manager,
			path_helper $path_helper,
			template $template,
			user $user,
			string $root_path,
			string $php_ext,
			string $flags_table,
			nationalflags $nationalflags,
			factory $files_factory)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->pagination = $pagination;
		$this->helper = $helper;
		$this->language = $language;
		$this->request = $request;
		$this->ext_manager	 = $ext_manager;
		$this->path_helper	 = $path_helper;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->flags_table = $flags_table;
		$this->nationalflags = $nationalflags;
		$this->files_factory = $files_factory;

		$this->ext_path = $this->ext_manager->get_extension_path('rmcgirr83/nationalflags', true);
	}

	/**
	 * Display the flags page
	 *
	 * @access public
	 */
	public function display_flags()
	{
		// If setting in ACP is set to not allow guests and bots to view the flags
		if (!$this->nationalflags->display_flags_on_forum())
		{
			throw new http_exception(401, 'NOT_AUTHORISED');
		}

		//let's get the flags
		$sql = 'SELECT f.flag_id, f.flag_name, f.flag_image, COUNT(u.user_flag) as user_count
			FROM ' . $this->flags_table . ' f
			LEFT JOIN ' . USERS_TABLE . ' u on f.flag_id = u.user_flag AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]) . '
		WHERE u.user_flag > 0
		GROUP BY f.flag_id
		ORDER BY user_count DESC, f.flag_name ASC';
		$result = $this->db->sql_query($sql);

		$countries = $users_count = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			++$countries;
			$user_count = $row['user_count'];
			$users_count = $users_count + $user_count;
			$user_flag_count = $this->language->lang('FLAG_USERS', (int) $user_count);

			$flag_image = $this->nationalflags->get_user_flag($row['flag_id']);

			$this->template->assign_block_vars('flag', [
				'FLAG' 				=> $flag_image,
				'FLAG_USER_COUNT'	=> $user_flag_count,
				'U_FLAG'			=> $this->helper->route('rmcgirr83_nationalflags_getflags', ['flag_id' => $this->nationalflags->flag_url_identifier($row['flag_id'])]),
			]);
		}
		$this->db->sql_freeresult($result);

		$flag_users = $this->language->lang('FLAG_USERS', (int) $users_count);

		$countries = $this->language->lang('FLAGS', (int) $countries);

		$this->template->assign_vars([
			'L_FLAGS'	=> $countries . '&nbsp;&nbsp;' . $flag_users,
			'S_FLAGS'		=> true,
		]);

		// Assign breadcrumb template vars for the flags page
		$this->template->assign_block_vars('navlinks', [
			'U_VIEW_FORUM'		=> $this->helper->route('rmcgirr83_nationalflags_display'),
			'FORUM_NAME'		=> $this->language->lang('NATIONAL_FLAGS'),
		]);

		// Send all data to the template file
		return $this->helper->render('@rmcgirr83_nationalflags\flags_list.html', $this->language->lang('NATIONAL_FLAGS'));
	}

	/**
	 * Display the users of flags page
	 *
	 * @param $flag_id	string|int	the country code or id of the flag
	 * @param $page		int		page number we are on
	 * @access public
	 */
	public function get_flags($flag_id, $page = 0)
	{
		// If setting in ACP is set to not allow guests and bots to view the flags
		if (!$this->nationalflags->display_flags_on_forum())
		{
			throw new http_exception(401, 'NOT_AUTHORISED');
		}

		$flag_id = $this->nationalflags->resolve_flag_id($flag_id);
		$flags = $this->nationalflags->get_flag_cache();

		// ensure our flag id passed actually exists in the cache
		if ($flag_id === false || !isset($flags[$flag_id]))
		{
			throw new http_exception(404, 'FLAG_NOT_EXIST');
		}

		$flag_name = $flags[$flag_id]['flag_name'];
		$page_title = $flag_name;
		if ($page > 1)
		{
			$page_title .= ' - ' . $this->language->lang('PAGE_TITLE_NUMBER', $page);
		}

		$this->display_flag($flag_id, ($page - 1) * $this->config['posts_per_page'], $this->config['posts_per_page']);

		// Send all data to the template file
		return $this->helper->render('@rmcgirr83_nationalflags\flag_users.html', $page_title);
	}

	/**
	 * Display flag
	 *
	 * @param $flag_id		int		the id of the flag
	 * @param $start		int		page number we start at
	 * @param $limit		int		limit to display for pagination
	 * @return null
	 * @access public
	 */
	protected function display_flag($flag_id, $start, $limit)
	{
		// Get users that have the flag
		$sql = 'SELECT user_regdate, user_lastvisit, username, user_colour, user_posts, user_id, user_avatar, user_avatar_height, user_avatar_type, user_avatar_width
			FROM ' . USERS_TABLE . '
			WHERE user_flag = ' . (int) $flag_id . '
				AND ' . $this->db->sql_in_set('user_type', [USER_NORMAL, USER_FOUNDER]) . '
			ORDER BY username_clean';
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		// for counting of total flag users
		$result = $this->db->sql_query($sql);
		$row2 = $this->db->sql_fetchrowset($result);
		$total_users = (int) sizeof($row2);
		$this->db->sql_freeresult($result);
		unset($row2);

		foreach ($rows as $userrow)
		{
			$user_id = $userrow['user_id'];

			$username = ($this->auth->acl_get('u_viewprofile')) ? get_username_string('full', $user_id, $userrow['username'], $userrow['user_colour']) : get_username_string('no_profile', $user_id, $userrow['username'], $userrow['user_colour']);

			$user_avatar = '';
			if ($this->config['flags_avatars'])
			{
				$user_avatar = ($this->user->optionget('viewavatars')) ? phpbb_get_user_avatar($this->avatar_img_resize($userrow)) : '';

				if (empty($user_avatar))
				{
					$no_avatar = "{$this->path_helper->get_web_root_path()}styles/" . rawurlencode($this->user->style['style_path']) . '/theme/images/no_avatar.gif';
					$avatar = [
						'user_avatar' => $no_avatar,
						'user_avatar_type' => AVATAR_REMOTE,
						'user_avatar_width' => self::MAX_SIZE,
						'user_avatar_height' => self::MAX_SIZE,
					];

					$user_avatar = '<img class="avatar" src="' . $avatar['user_avatar'] . '" width="' . $avatar['user_avatar_width'] . '" height="' . $avatar['user_avatar_height'] . '" alt="' . $this->language->lang('USER_AVATAR') . '" />';
				}
			}

			$this->template->assign_block_vars('user_row', [
				'JOINED'		=> $this->user->format_date($userrow['user_regdate']),
				'VISITED'		=> (empty($userrow['user_lastvisit'])) ? ' - ' : $this->user->format_date($userrow['user_lastvisit']),
				'POSTS'			=> ($userrow['user_posts']) ? $userrow['user_posts'] : 0,
				'USERNAME_FULL'		=> $username,
				'USER_AVATAR'		=> $user_avatar,
				'U_SEARCH_USER'		=> ($this->auth->acl_get('u_search')) ? append_sid("{$this->root_path}search.$this->php_ext", "author_id=$user_id&amp;sr=posts") : '',
			]);
		}

		$this->pagination->generate_template_pagination([
			'routes' => [
				'rmcgirr83_nationalflags_getflags',
				'rmcgirr83_nationalflags_getflags_page',
			],
			'params' => [
				'flag_id' => $this->nationalflags->flag_url_identifier($flag_id),
			],
		], 'pagination', 'page', $total_users, $limit, $start);

		$flag_image = $this->nationalflags->get_user_flag((int) $flag_id);

		$users_count = $total_users;

		$total_users = $this->language->lang('FLAG_USERS', (int) $total_users);

		$flags_array = $this->nationalflags->get_flag_cache();

		$flag_name = $this->nationalflags->flag_name_lang_var($flags_array[$flag_id]['flag_name']);

		$this->template->assign_vars([
			'FLAG'			=> $flag_name,
			'FLAG_IMAGE'	=> $flag_image,
			'TOTAL_USERS'	=> $total_users,
			'S_VIEWONLINE'	=> $this->auth->acl_get('u_viewonline'),
			'S_FLAGS'		=> true,
			'S_FLAG_USERS'	=> (!empty($users_count)) ? true : false,
			'MESSAGE_TEXT'	=> (empty($users_count)) ? $this->language->lang('NO_USER_HAS_FLAG') : '',
		]);

		// Assign breadcrumb template vars for the flags page
		$this->template->assign_block_vars('navlinks', [
			'U_VIEW_FORUM'		=> $this->helper->route('rmcgirr83_nationalflags_display'),
			'FORUM_NAME'		=> $this->language->lang('NATIONAL_FLAGS'),
		]);

		// Assign breadcrumb template vars for the flags page
		$this->template->assign_block_vars('navlinks', [
			'U_VIEW_FORUM'		=> $this->helper->route('rmcgirr83_nationalflags_getflags', ['flag_id' => $this->nationalflags->flag_url_identifier($flag_id)]),
			'FORUM_NAME'		=> $flag_name,
		]);
	}

	/* Generate and resize avatar
	* from last post avatar extension
	*/
	private function avatar_img_resize($avatar)
	{
		if (!empty($avatar['user_avatar']))
		{
			if ($avatar['user_avatar_width'] >= $avatar['user_avatar_height'])
			{
				$avatar_width = ($avatar['user_avatar_width'] > self::MAX_SIZE) ? self::MAX_SIZE : $avatar['user_avatar_width'];
				if ($avatar_width == self::MAX_SIZE)
				{
					$avatar['user_avatar_height'] = round(self::MAX_SIZE/$avatar['user_avatar_width']*$avatar['user_avatar_height']);
				}
				$avatar['user_avatar_width'] = $avatar_width;
			}
			else
			{
				$avatar_height = ($avatar['user_avatar_height'] > self::MAX_SIZE) ? self::MAX_SIZE : $avatar['user_avatar_height'];
				if ($avatar_height == self::MAX_SIZE)
				{
					$avatar['user_avatar_width'] = round(self::MAX_SIZE/$avatar['user_avatar_height']*$avatar['user_avatar_width']);
				}
				$avatar['user_avatar_height'] = $avatar_height;
			}
		}
		return $avatar;
	}
}
