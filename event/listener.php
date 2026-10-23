<?php
/**
*
* National Flags extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Rich McGirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\nationalflags\event;

/**
* @ignore
*/
use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\driver_interface as db;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\extension\manager;
use rmcgirr83\nationalflags\core\nationalflags;
use paybas\recenttopics\core\recenttopics;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	protected $ext_path;

	/** @var auth $auth */
	protected $auth;

	/** @var config $config */
	protected $config;

	/** @var helper $helper*/
	protected $helper;

	/** @var db $db */
	protected $db;

	/** @var language $language */
	protected $language;

	/** @var request $request */
	protected $request;

	/** @var template $template */
	protected $template;

	/** @var user $user */
	protected $user;

	/** @var ext_manager $ext_manager */
	protected $ext_manager;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/* @var nationalflags $nationalflags */
	protected $nationalflags;

	/* @var recenttopics $recenttopics */
	protected $recenttopics;

	/**
	* Constructor
	*
	* @param auth					$auth			Auth object
	* @param config               	$config         Config object
	* @param helper           		$helper         Controller helper object
	* @param db						$db				Database object
	* @param language				$language		Language object
	* @param request				$request		Request object
	* @param template          		$template       Template object
	* @param user                   $user           User object
	* @param ext_manager			$ext_manager	Extension manager object
	* @param string                 $root_path		phpBB root path
	* @param string                 $php_ext		phpEx
	* @param nationalflags			$nationalflags	methods to be used by class
	* @param recenttopics			$recenttopics	recent topics extension
	* @access public
	*/
	public function __construct(
			auth $auth,
			config $config,
			helper $helper,
			db $db,
			language $language,
			request $request,
			template $template,
			user $user,
			manager $ext_manager,
			string $root_path,
			string $php_ext,
			nationalflags $nationalflags,
			recenttopics $recenttopics = null)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->helper = $helper;
		$this->db = $db;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->ext_manager	 = $ext_manager;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->nationalflags = $nationalflags;
		$this->recenttopics = $recenttopics;

		$this->ext_path = $this->ext_manager->get_extension_path('rmcgirr83/nationalflags', true);
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 * @static
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return [
			'core.user_setup_after'						=> 'user_setup_after',
			'core.index_modify_page_title'				=> 'index_modify_page_title',
			'core.page_header_after'					=> 'page_header_after',
			'core.ucp_profile_modify_profile_info'		=> 'user_flag_profile',
			'core.ucp_profile_validate_profile_info'	=> 'user_flag_profile_validate',
			'core.ucp_profile_info_modify_sql_ary'		=> 'user_flag_profile_sql',
			'core.ucp_register_data_before'				=> 'user_flag_profile',
			'core.ucp_register_data_after'				=> 'user_flag_profile_validate',
			'core.ucp_register_user_row_after'			=> 'user_flag_registration_sql',
			'core.acp_users_modify_profile'				=> 'user_flag_profile',
			'core.acp_users_profile_validate'			=> 'user_flag_profile_validate',
			'core.acp_users_profile_modify_sql_ary'		=> 'user_flag_profile_sql',
			'core.viewonline_overwrite_location'		=> 'viewonline_page',
			'core.viewtopic_assign_template_vars_before'	=> 'viewtopic_template_vars_before',
			'core.viewtopic_cache_user_data'			=> 'viewtopic_cache_user_data',
			'core.viewtopic_cache_guest_data'			=> 'viewtopic_cache_guest_data',
			'core.viewtopic_modify_post_row'			=> 'viewtopic_modify_post_row',
			'core.memberlist_view_profile'				=> 'memberlist_view_profile',
			'core.search_get_posts_data'				=> 'search_get_posts_data',
			'core.search_modify_tpl_ary'				=> 'search_modify_tpl_ary',
			'core.search_results_modify_search_title'	=> 'search_modify_search_title',
			'core.ucp_pm_view_messsage'					=> 'ucp_pm_view_messsage',
			'core.memberlist_team_modify_template_vars'	=> 'user_flags_modify_template_vars',
			'core.memberlist_prepare_profile_data'		=> 'memberlist_prepare_profile_data',
			'core.display_forums_modify_template_vars'	=> 'display_forums_modify_template_vars',
			'core.viewforum_modify_page_title'			=> 'viewforum_modify_page_title',
			'core.viewforum_modify_topicrow'			=> 'viewforum_modify_topicrow',
			'paybas.recenttopics.modify_tpl_ary'		=> 'recenttopics_modify_tpl_ary',
			'core.delete_user_after'					=> 'delete_user_after',
			'core.memberlist_modify_memberrow_sql'		=> 'memberlist_modify_memberrow_sql',
		];
	}

	/**
	 * Check for and create if needed users and flags cache
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function user_setup_after($event)
	{
		$this->language->add_lang('common', 'rmcgirr83/nationalflags');
		// Need to ensure the flags are cached on page load
		$this->nationalflags->cache_flags();

		// Regenerate the users and flags cache too
		$this->nationalflags->build_users_and_flags();
	}

	/**
	 * Set up the flags on the index page
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function index_modify_page_title($event)
	{
		if (!$this->config['flags_display_index'] || !$this->nationalflags->display_flags_on_forum())
		{
			return false;
		}
		//display flags on the index page
		$this->nationalflags->top_flags();
	}

	/**
	 * Create URL and message to users if wanted.
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function page_header_after($event)
	{
		if (empty($this->user->data['is_registered']) || !$this->auth->acl_get('u_chgprofileinfo'))
		{
			return;
		}

		$page_name = substr($this->user->page['page_name'], 0, strpos($this->user->page['page_name'], '.'));

		if ($page_name == 'ucp')
		{
			return;
		}
		if ($this->config['flags_display_msg'])
		{
			$this->template->assign_vars([
				'S_FLAG_MESSAGE'	=> (empty($this->user->data['user_flag'])) ? true : false,
				'L_FLAG_PROFILE'	=> $this->language->lang('USER_NEEDS_FLAG', '<a href="' . append_sid("{$this->root_path}ucp.$this->php_ext", 'i=profile') . '">', '</a>'),
			]);
		}
	}

	/**
	 * Allow users to change their flag
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function user_flag_profile($event)
	{
		if (DEFINED('IN_ADMIN'))
		{
			$user_flag = $event['user_row']['user_flag'];
		}
		else
		{
			$user_flag = $this->user->data['user_flag'];
		}

		// Request the user option vars and add them to the data array
		$event['data'] = array_merge($event['data'], [
			'user_flag'	=> $this->request->variable('user_flag', (int) $user_flag),
		]);

		$flags = $this->nationalflags->get_flag_cache();
		$has_default = false;
		foreach ($flags as $flag => $settings)
		{
			if (!empty($settings['flag_default']))
			{
				$has_default = true;
			}
		}

		$this->template->assign_vars([
			'FLAG_DEFAULT' => (empty($event['data']['user_flag']) && $has_default) ? true : false,
		]);
		$this->nationalflags->display_flag_options($event['data']['user_flag']);
	}

	/**
	 * Validate users changes to their flag
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function user_flag_profile_validate($event)
	{
		// stop nubs from being, uhmmm, nubs
		$flags = $this->nationalflags->get_flag_cache();
		$flags_id = [];
		foreach ($flags as $id => $data)
		{
			$flags_id[] = $id;
		}

		$user_flag = $event['data']['user_flag'];

		$array = $event['error'];
		if (!empty($user_flag) && !in_array($user_flag, $flags_id))
		{
			$array[] = $this->language->lang('FLAG_NOT_EXIST');
		}
		if (!DEFINED('IN_ADMIN') && empty($user_flag) && $this->config['flags_required'])
		{
			$array[] = $this->language->lang('MUST_CHOOSE_FLAG');
		}

		$event['error'] = $array;
	}

	/**
	 * User changed their flag so update the database
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function user_flag_profile_sql($event)
	{
		//call function to trash the users_and_flags cache so it's regenerated
		$this->nationalflags->trash_the_cache();

		$user_flag = $event['data']['user_flag'];

		$event['sql_ary'] = array_merge($event['sql_ary'], [
				'user_flag' => $user_flag,
		]);
	}

	/**
	 * Update registration data
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function user_flag_registration_sql($event)
	{
		//call function to trash the users_and_flags cache so it's regenerated
		$this->nationalflags->trash_the_cache();

		$event['user_row'] = array_merge($event['user_row'], [
				'user_flag' => $this->request->variable('user_flag', 0),
		]);
	}

	/**
	 * Show users as viewing the flags on Who Is Online page
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function viewonline_page($event)
	{
		if ($event['on_page'][1] == 'app')
		{
			if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/flags') === 0)
			{
				$event['location'] = $this->language->lang('FLAGS_VIEWONLINE');
				$event['location_url'] = $this->helper->route('rmcgirr83_nationalflags_display');
			}
		}
	}

	/**
	 * Load the css file
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function viewtopic_template_vars_before($event)
	{
		$flag_display_position = $this->nationalflags->flag_display_position();

		$this->template->assign_vars([
			'S_FLAGS'		=> $this->nationalflags->display_flags_on_forum($this->config['flags_viewforum']),
			$flag_display_position => true,
		]);
	}
	/**
	 * Update viewtopic user data
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function viewtopic_cache_user_data($event)
	{
		$array = $event['user_cache_data'];
		$array['user_flag'] = $event['row']['user_flag'];
		$event['user_cache_data'] = $array;
	}

	/**
	 * Update viewtopic guest data
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function viewtopic_cache_guest_data($event)
	{
		$array = $event['user_cache_data'];
		$array['user_flag'] = 0;
		$event['user_cache_data'] = $array;
	}

	/**
	 * Modify the viewtopic post row
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function viewtopic_modify_post_row($event)
	{
		// If setting in ACP is set to not allow guests and bots to view the flags
		if (!$this->nationalflags->display_flags_on_forum($this->config['flags_viewforum']))
		{
			return false;
		}

		$flag = $this->nationalflags->get_user_flag($event['user_poster_data']['user_flag']);
		$flags = $this->nationalflags->get_flag_cache();

		$event['post_row'] = array_merge($event['post_row'], [
			'USER_FLAG' => $flag,
			'FLAG_POSITION'	=> $this->config['flag_position'],
			'U_FLAG'	=> ($flag) ? $this->helper->route('rmcgirr83_nationalflags_getflags', ['flag_id' => $this->nationalflags->flag_url_identifier($flags[$event['user_poster_data']['user_flag']]['flag_id'])]) : '',
		]);
	}

	/**
	 * Display flag on viewing user profile
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function memberlist_view_profile($event)
	{
		if (!empty($event['member']['user_flag']) && $this->nationalflags->display_flags_on_forum())
		{
			$flag = $this->nationalflags->get_user_flag($event['member']['user_flag']);
			$flags = $this->nationalflags->get_flag_cache();
			$flag_display_position = $this->nationalflags->flag_display_position();

			$this->template->assign_vars([
				'USER_FLAG'		=> $flag,
				'S_FLAGS'		=> true,
				'U_FLAG'		=> ($flag) ? $this->helper->route('rmcgirr83_nationalflags_getflags', ['flag_id' => $this->nationalflags->flag_url_identifier($flags[$event['member']['user_flag']]['flag_id'])]) : '',
				$flag_display_position => true,
			]);
		}
	}

	/**
	 * Display flag on search
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function search_get_posts_data($event)
	{
		$array = $event['sql_array'];
		$array['SELECT'] .= ', u.user_flag';
		$event['sql_array'] = $array;
	}

	/**
	 * Display flag on search
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function search_modify_tpl_ary($event)
	{
		if ($event['show_results'] == 'topics' || !$this->nationalflags->display_flags_on_forum($this->config['flags_search']))
		{
			return false;
		}

		$array = $event['tpl_ary'];

		$flag = $this->nationalflags->get_user_flag($event['row']['user_flag']);
		$flags = $this->nationalflags->get_flag_cache();

		$array = array_merge($array, [
			'USER_FLAG'		=> $flag,
			'U_FLAG'		=> ($flag) ? $this->helper->route('rmcgirr83_nationalflags_getflags', ['flag_id' => $this->nationalflags->flag_url_identifier($flags[$event['row']['user_flag']]['flag_id'])]) : '',
		]);

		$event['tpl_ary'] = $array;
	}

	/**
	 * Search modify search title
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function search_modify_search_title($event)
	{
		if ($event['show_results'] == 'topics' || !$this->nationalflags->display_flags_on_forum($this->config['flags_search']))
		{
			return false;
		}

		$this->template->assign_vars([
			'S_FLAGS'		=> true,
		]);
	}

	/**
	 * Display flag on viewing PM's
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function ucp_pm_view_messsage($event)
	{
		if (!empty($event['user_info']['user_flag']) && $this->nationalflags->display_flags_on_forum())
		{
			$flag = $this->nationalflags->get_user_flag($event['user_info']['user_flag']);

			$array = $event['msg_data'];
			$array['USER_FLAG'] = $flag;
			$array['U_FLAG'] = ($flag) ? $this->helper->route('rmcgirr83_nationalflags_getflags', ['flag_id' => $this->nationalflags->flag_url_identifier($event['user_info']['user_flag'])]) : '';
			$event['msg_data'] = $array;

			$flag_display_position = $this->nationalflags->flag_display_position();
			$this->template->assign_vars([
				'S_FLAGS'		=> true,
				$flag_display_position => true,
			]);
		}
	}

	/* user_flags_modify_template_vars
	* @param 	object 	The event object
	* @return	string	A flag image
	* @access	public
	*/
	public function user_flags_modify_template_vars($event)
	{
		$user_id = $event['row']['user_id'];
		$flag_id = $this->nationalflags->user_flag_id($user_id);
		$template_vars = $event['template_vars'];

		if (!empty($flag_id) && $this->nationalflags->display_flags_on_forum($this->config['flags_memberlist']))
		{
			$template_vars['USERNAME_FULL']	.= '&nbsp;' . $this->nationalflags->get_user_flag($flag_id, 20);
		}

		$event['template_vars'] = $template_vars;
	}

	/* memberlist_prepare_profile_data
	* @param 	object 	The event object
	* @return	string	A flag image
	* @access	public
	*/
	public function memberlist_prepare_profile_data($event)
	{
		$user_id = $event['data']['user_id'];
		$flag_id = $this->nationalflags->user_flag_id($user_id);
		$template_vars = $event['template_data'];

		if (!empty($flag_id) && $this->nationalflags->display_flags_on_forum($this->config['flags_memberlist']))
		{
			$template_vars['USERNAME_FULL']	.= '&nbsp;' . $this->nationalflags->get_user_flag($flag_id, 12);
		}

		$event['template_data'] = $template_vars;
	}

	/* display_forums_modify_template_vars
	* @param 	object 	The event object
	* @return	string	A flag image
	* @access	public
	*/
	public function display_forums_modify_template_vars($event)
	{
		$user_id = $event['row']['forum_last_poster_id'];
		$flag_id = $this->nationalflags->user_flag_id($user_id);
		$template_vars = $event['forum_row'];

		if (!empty($flag_id) && $this->nationalflags->display_flags_on_forum($this->config['flags_forumrow']))
		{
			$template_vars['LAST_POSTER_FULL']	.= '&nbsp;' . $this->nationalflags->get_user_flag($flag_id, 12);
		}

		$event['forum_row'] = $template_vars;
	}

	/* viewforum_modify_page_title
	* @param 	object 	The event object
	* @return	null
	* @access	public
	*/
	public function viewforum_modify_page_title($event)
	{
		$this->template->assign_vars([
			'S_FLAGS'		=> true,
		]);
	}

	/* viewforum_modify_topicrow
	* @param 	object 	The event object
	* @return	string	A flag image
	* @access	public
	*/
	public function viewforum_modify_topicrow($event)
	{
		$topic_starter = $this->nationalflags->user_flag_id($event['row']['topic_poster']);
		$last_post_author = $this->nationalflags->user_flag_id($event['row']['topic_last_poster_id']);
		$template_vars = $event['topic_row'];

		if (!empty($topic_starter) && $this->nationalflags->display_flags_on_forum($this->config['flags_forumrow']))
		{
			$template_vars['TOPIC_AUTHOR_FULL']	.= '&nbsp;' . $this->nationalflags->get_user_flag($topic_starter, 12);
		}
		if (!empty($last_post_author) && $this->nationalflags->display_flags_on_forum($this->config['flags_forumrow']))
		{
			$template_vars['LAST_POST_AUTHOR_FULL']	.= '&nbsp;' . $this->nationalflags->get_user_flag($last_post_author, 12);
		}

		$event['topic_row'] = $template_vars;
	}

	/* recenttopics_modify_tpl_ary
	* @param 	object 	The event object
	* @return	string	A flag image
	* @access	public
	*/
	public function recenttopics_modify_tpl_ary($event)
	{
		$row = $event['row'];
		$topic_starter = $this->nationalflags->user_flag_id($event['row']['topic_poster']);
		$last_post_author = $this->nationalflags->user_flag_id($event['row']['topic_last_poster_id']);
		$template_vars = $event['tpl_ary'];

		if (!empty($topic_starter) && $this->nationalflags->display_flags_on_forum($this->config['flags_forumrow']))
		{
			$template_vars['TOPIC_AUTHOR_FULL'] .= '&nbsp;' . $this->nationalflags->get_user_flag($topic_starter, 12);
		}
		if (!empty($last_post_author) && $this->nationalflags->display_flags_on_forum($this->config['flags_forumrow']))
		{
			$template_vars['LAST_POST_AUTHOR_FULL']	.= '&nbsp;' . $this->nationalflags->get_user_flag($last_post_author, 12);
		}

		$event['tpl_ary'] = $template_vars;
	}

	/* delete_user_after
	* @param 	object 	The event object
	* @return	string	A flag image
	* @access	public
	*/
	public function delete_user_after($event)
	{
		//call function to trash the users_and_flags cache so it's regenerated
		$this->nationalflags->trash_the_cache();
	}

	public function memberlist_modify_memberrow_sql($event)
	{
		$mode = $event['mode'];

		if ($mode != 'group' && ($this->config['flags_display_index'] || $this->nationalflags->display_flags_on_forum()))
		{
			$this->nationalflags->top_flags();
		}
	}
}
