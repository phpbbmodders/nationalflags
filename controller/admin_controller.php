<?php
/**
*
* National Flags extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Rich McGirr (RMcGirr83)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rmcgirr83\nationalflags\controller;

use phpbb\cache\service as cache_service;
use phpbb\config\config;
use phpbb\db\driver\driver_interface as db;
use phpbb\pagination;
use phpbb\controller\helper;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\log\log;
use phpbb\extension\manager;
use phpbb\path_helper;
use rmcgirr83\nationalflags\core\nationalflags;
use phpbb\files\factory;
use phpbb\filesystem\filesystem;
use phpbb\language\language;

/**
* Admin controller
*/
class admin_controller
{
	protected $ext_path;
	protected $ext_path_web;
	protected $images;

	/**
	* define our constants
	**/
	const MAX_WIDTH = 32;
	const MAX_HEIGHT = 24;

	/** @var cache $cache */
	protected $cache;

	/** @var config $config */
	protected $config;

	/** @var db $db */
	protected $db;

	/** @var pagination $pagination */
	protected $pagination;

	/** @var helper $helper*/
	protected $helper;

	/** @var request $request */
	protected $request;

	/** @var template $template */
	protected $template;

	/** @var user $user */
	protected $user;

	/** @var log $log */
	protected $log;

	/** @var ext_manager $ext_manager */
	protected $ext_manager;

	/** @var path_helper $path_helper */
	protected $path_helper;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var array flag_constants */
	protected $flag_constants;

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

	/** @var  filesystem $filesystem */
	protected $filesystem;

	/** @var language $language */
	protected $language;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor
	*
	* @param cache						$cache				Cache object
	* @param config						$config				Config object
	* @param db							$db					Database object
	* @param pagination					$pagination			Pagination object
	* @param helper           			$helper     	    Controller helper object
	* @param request					$request			Request object
	* @param template					$template			Template object
	* @param user						$user				User object
	* @param log						$log				Log object
	* @param ext_manager				$ext_manager		Extension manager object
	* @param path_helper				$path_helper		Path helper object
	* @param string                    	$root_path      	phpBB root path
	* @param string                    	$php_ext        	phpEx
	* @param array						$flag_constants		Constants for the extension
	* @param string						$flags_table		Name of the table used to store flag data
	* @param nationalflags				$nationalflags		methods for the extension
	* @param files_factory				$files_factory		File classes factory
	* @param filesystem					$filesystem			Filesystem classes filesystem
	* @param language					$language			Language object
	* @return \rmcgirr83\nationalflags\controller\admin_controller
	* @access public
	*/
	public function __construct(
			cache_service $cache,
			config $config,
			db $db,
			pagination $pagination,
			helper $helper,
			request $request,
			template $template,
			user $user,
			log $log,
			manager $ext_manager,
			path_helper $path_helper,
			string $root_path,
			string $php_ext,
			array $flag_constants,
			string $flags_table,
			nationalflags $nationalflags,
			factory $files_factory,
			filesystem $filesystem,
			language $language,
			\rmcgirr83\nationalflags\core\flag_images $images)
	{
		$this->images = $images;
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->pagination = $pagination;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->log = $log;
		$this->ext_manager	 = $ext_manager;
		$this->path_helper	 = $path_helper;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->flag_constants = $flag_constants;
		$this->flags_table = $flags_table;
		$this->nationalflags = $nationalflags;
		$this->files_factory = $files_factory;
		$this->filesystem = $filesystem;
		$this->language = $language;

		$this->ext_path = $this->ext_manager->get_extension_path('rmcgirr83/nationalflags', true);
		$this->ext_path_web = $this->path_helper->update_web_root_path($this->ext_path);
	}

	/**
	 * Display the options a user can configure for this extension
	 *
	 * @return null
	 * @access public
	 */
	public function display_options()
	{
		// Create a form key for preventing CSRF attacks
		add_form_key('nationalflags_settings');

		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('nationalflags_settings'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action));
			}

			if (!function_exists('validate_data'))
			{
				include($this->root_path . 'includes/functions_user.' . $this->php_ext);
			}

			$check_row = [
				'flags_num_display' => $this->request->variable('flags_num_display', 0),
			];
			$validate_row = [
				'flags_num_display' => ['num', false, 1, 100],
			];
			$error = array_map([$this->language, 'lang'], validate_data($check_row, $validate_row));

			if (!sizeof($error))
			{
				// Set the options the user configured
				$this->set_options();

				// Add option settings change action to the admin log
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FLAG_CONFIG_SAVED');

				// Option settings have been updated and logged
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->language->lang('FLAG_SETTINGS_CHANGED') . adm_back_link($this->u_action));
			}
		}

		// Set output vars for display in the template
		$this->template->assign_vars([
			'ERROR'				=> isset($error) ? ((sizeof($error)) ? implode('<br>', $error) : '') : '',
			'FLAGS_AVATARS'		=> $this->config['flags_avatars'] ? true : false,
			'FLAGS_NUM_DISPLAY'	=> $this->config['flags_num_display'],
			'FLAGS_DISPLAY_INDEX'	=> $this->config['flags_display_index'] ? true : false,
			'ALLOW_FLAGS'		=> $this->config['allow_flags'] ? true : false,
			'FLAGS_REQUIRED'	=> $this->config['flags_required'] ? true : false,
			'FLAGS_DISPLAY_MSG'	=> $this->config['flags_display_msg'] ? true : false,
			'FLAGS_DISPLAY_TO_GUESTS'	=> $this->config['flags_display_to_guests'] ? true : false,
			'FLAG_POSITION'		=> $this->flag_position($this->config['flag_position']),
			'FLAGS_VIEWFORUM'	=> $this->config['flags_viewforum'] ? true : false,
			'FLAGS_FORUMROW'	=> $this->config['flags_forumrow'] ? true : false,
			'FLAGS_SEARCH'		=> $this->config['flags_search'] ? true : false,
			'FLAGS_MEMBERLIST'	=> $this->config['flags_memberlist'] ? true : false,
			'S_FLAGS'			=> true,

			'U_ACTION'			=> $this->u_action,
		]);
	}

	/**
	 * Set the options a user can configure
	 *
	 * @return null
	 * @access protected
	 */
	protected function set_options()
	{
		$this->config->set('flags_num_display', $this->request->variable('flags_num_display', 0));
		$this->config->set('allow_flags', $this->request->variable('allow_flags', 0));
		$this->config->set('flags_required', $this->request->variable('flags_required', 0));
		$this->config->set('flags_display_msg', $this->request->variable('flags_display_msg', 0));
		$this->config->set('flags_display_index', $this->request->variable('flags_display_index', 0));
		$this->config->set('flags_display_to_guests', $this->request->variable('flags_display_to_guests', 0));
		$this->config->set('flag_position', $this->request->variable('flag_position', 0));
		$this->config->set('flags_viewforum', $this->request->variable('flags_viewforum', 0));
		$this->config->set('flags_forumrow', $this->request->variable('flags_forumrow', 0));
		$this->config->set('flags_search', $this->request->variable('flags_search', 0));
		$this->config->set('flags_memberlist', $this->request->variable('flags_memberlist', 0));
		$this->config->set('flags_avatars', $this->request->variable('flags_avatars', 0));
	}

	/**
	 * Display the flags
	 *
	 * @return null
	 * @access public
	 */
	public function display_flags()
	{
		$start = $this->request->variable('start', 0);
		$pagination_url = $this->u_action;

		$sql = 'SELECT f.*, COUNT(u.user_flag) as user_count
			FROM ' . $this->flags_table . ' f
				LEFT JOIN ' . USERS_TABLE . " u on f.flag_id = u.user_flag AND " . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]) . "
			GROUP BY f.flag_id
			ORDER BY f.flag_name ASC";
		$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);

		// for counting of all the flags
		// used for pagination
		$result2 = $this->db->sql_query($sql);
		$row2 = $this->db->sql_fetchrowset($result2);
		$total_count = (int) sizeof($row2);
		$this->db->sql_freeresult($result2);
		unset($row2);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_count = $this->language->lang('FLAG_USERS', (int) $row['user_count']);

			$flag_default = !empty($row['flag_default']) ? true : false;
			$this->template->assign_block_vars('flags', [
				'FLAG_NAME'		=> $row['flag_name'],
				'FLAG_DEFAULT'	=> $flag_default,
				'FLAG_IMG'		=> $this->images->url($row['flag_image']),
				'FLAG_ID'		=> $row['flag_id'],
				'USER_COUNT'	=> $user_count,
				'U_FLAG'		=> $this->helper->route('rmcgirr83_nationalflags_getflags', ['flag_id' => $this->nationalflags->flag_url_identifier($row['flag_id'])]),
				'U_EDIT'		=> $this->u_action . "&amp;flag_id={$row['flag_id']}&amp;action=edit",
				'U_DELETE'		=> $this->u_action . "&amp;flag_id={$row['flag_id']}&amp;action=delete"
			]);
		}
		$this->db->sql_freeresult($result);

		$start = $this->pagination->validate_start($start, $this->config['topics_per_page'], $total_count);
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total_count, $this->config['topics_per_page'], $start);

		$this->template->assign_vars([
			'TOTAL_FLAGS'	=> $total_count,
			'S_FLAGS'	=> true,
		]);
	}

	/**
	 * Add a flag
	 *
	 * @return null
	 * @access public
	 */
	public function add_flag()
	{
		// Add form key
		add_form_key('add_flag');

		$errors = [];

		$flag_row = [
			'flag_name'			=> ucfirst($this->request->variable('flag_name', '', true)),
			'flag_image'		=> (!$this->can_upload_flag()) ? $this->request->variable('flag_image', '') : '',
			'flag_default'		=> $this->request->variable('flag_default', 0),
		];

		if ($this->request->is_set_post('submit'))
		{
			$errors = $this->check_flag($flag_row['flag_name'], $errors, 'add_flag', $flag_row['flag_image']);

			if (!sizeof($errors) && $this->can_upload_flag())
			{
				$flag_row['flag_image'] = $this->flag_upload($errors, 'add_flag');
			}

			if (!sizeof($errors))
			{
				// if this flag is set to default, change all other flags to not be set as default
				if ($flag_row['flag_default'])
				{
					$this->change_flag_default();
				}

				$sql = 'INSERT INTO ' . $this->flags_table . ' ' . $this->db->sql_build_array('INSERT', $flag_row);
				$this->db->sql_query($sql);

				$this->log_message('LOG_FLAG_ADD', $flag_row['flag_name'], 'MSG_FLAG_ADDED');
			}
		}
		$flag_list =  $this->list_flag_names();
		$flag_count = sizeof(explode(",", $flag_list));
		$this->template->assign_vars([
			'L_TITLE'		=> $this->language->lang('FLAG_ADD'),
			'L_IMAGES_ON_SERVER'	=> $this->language->lang('IMAGES_ON_SERVER', $flag_count),
			'U_ACTION'		=> $this->u_action . '&amp;action=add',
			'U_BACK'		=> $this->u_action,
			'FLAG_NAME'		=> $flag_row['flag_name'],
			'FLAG_IMAGE'	=> $flag_row['flag_image'],
			'ERROR_MSG'		=> (sizeof($errors)) ? implode('<br>', $errors) : '',
			'FLAG_LIST'		=> $flag_list,

			'S_ADD_FLAG'	=> true,
			'S_ERROR'		=> (sizeof($errors)) ? true : false,
			'S_UPLOAD_FLAG'	=> $this->can_upload_flag(),
			'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',
		]);
	}

	/**
	 * Edit a flag
	 *
	 * @param int $flag_id The flag identifier to edit
	 * @return null
	 * @access public
	 */
	public function edit_flag($flag_id)
	{

		$sql = 'SELECT *
			FROM ' . $this->flags_table . '
			WHERE flag_id =' . (int) $flag_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error($this->language->lang('FLAG_ERROR_NOT_EXIST') . adm_back_link($this->u_action . '&amp;mode=manage'), E_USER_WARNING);
		}

		// Add form key
		add_form_key('edit_flag');

		$errors = [];

		$flag_row = [
			'flag_name'			=> ucfirst($this->request->variable('flag_name', '', true)),
			'flag_image'		=> (!$this->can_upload_flag()) ? $this->request->variable('flag_image', $row['flag_image']) : '',
			'flag_default'		=> $this->request->variable('flag_default', 0),
		];

		if ($this->request->is_set_post('submit'))
		{
			$errors = $this->check_flag($flag_row['flag_name'], $errors, 'edit_flag', $flag_row['flag_image']);

			if (!sizeof($errors) && $this->can_upload_flag())
			{
				$flag_row['flag_image'] = $this->flag_upload($errors, 'edit_flag', $row['flag_image']);
			}

			if (!sizeof($errors))
			{
				// if this flag is set to default, change all other flags to not be set as default
				if ($flag_row['flag_default'])
				{
					$this->change_flag_default();
				}

				$sql = 'UPDATE ' . $this->flags_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $flag_row) . '
					WHERE flag_id = ' . (int) $flag_id;
				$this->db->sql_query($sql);

				$this->cache->destroy('_user_flags');
				if ($row['flag_image'] !== $flag_row['flag_image'])
				{
					$this->remove_unused_image($row['flag_image']);
				}

				$this->log_message('LOG_FLAG_EDIT', $flag_row['flag_name'], 'MSG_FLAG_EDITED');

			}
		}

		$found_flag = $this->images->url($row['flag_image']);
		$flag_list =  $this->list_flag_names();
		$flag_count = sizeof(explode(",", $flag_list));
		$this->template->assign_vars([
			'L_TITLE'		=> $this->language->lang('FLAG_EDIT'),
			'L_IMAGES_ON_SERVER'	=> $this->language->lang('IMAGES_ON_SERVER', $flag_count),
			'U_ACTION'		=> $this->u_action . "&amp;flag_id=$flag_id&amp;action=edit",
			'U_BACK'		=> $this->u_action . '&amp;mode=manage',
			'ERROR_MSG'		=> (sizeof($errors)) ? implode('<br>', $errors) : '',

			'FLAG_NAME'		=> $row['flag_name'],
			'FLAG_IMAGE'	=> $row['flag_image'],
			'FLAG_ID'		=> $row['flag_id'],
			'FLAG_DEFAULT'	=> $row['flag_default'],
			'FOUND_FLAG'	=> (!empty($found_flag)) ? $found_flag : '',
			'FLAG_LIST'		=> $flag_list,

			'S_ADD_FLAG'	=> true,
			'S_UPLOAD_FLAG'	=> $this->can_upload_flag(),
			'S_ERROR'		=> (sizeof($errors)) ? true : false,
			'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',
			]
		);
	}

	/**
	 * Delete a flag
	 *
	 * @param int $flag_id The flag identifier to delete
	 * @return null
	 * @access public
	 */
	public function delete_flag($flag_id)
	{
		if (confirm_box(true))
		{
			// Grab the flag name for the log...
			$sql = 'SELECT flag_name, flag_image
				FROM ' . $this->flags_table . '
				WHERE flag_id = ' .(int) $flag_id;
			$result = $this->db->sql_query($sql);
			$flag_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$flag_row)
			{
				trigger_error($this->language->lang('FLAG_ERROR_NOT_EXIST'), E_USER_WARNING);
				return;
			}

			$this->db->sql_transaction('begin');
			// Delete the flag...
			$this->db->sql_query('DELETE FROM ' . $this->flags_table . ' WHERE flag_id = ' . (int) $flag_id);

			// Reset the flag for users
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_flag = 0
				WHERE user_flag = ' . (int) $flag_id;
			$this->db->sql_query($sql);

			$this->db->sql_transaction('commit');
			$this->nationalflags->trash_the_cache();
			$this->cache->destroy('_user_flags');
			$this->remove_unused_image($flag_row['flag_image']);

			$this->log_message('LOG_FLAGS_DELETED', $flag_row['flag_name'], 'MSG_FLAGS_DELETED');
		}
		else
		{
			// display a count of users who have this flag
			$sql = 'SELECT COUNT(user_flag) AS flag_count
				FROM ' . USERS_TABLE . '
				WHERE user_flag = ' . (int) $flag_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$message = $this->language->lang('MSG_CONFIRM');
			if (!empty($row['flag_count']))
			{
				$message .= $this->language->lang('MSG_FLAG_CONFIRM_DELETE', (int) $row['flag_count']);
			}
			confirm_box(false, $message, build_hidden_fields([
				'id'		=> (int) $flag_id,
				'mode'		=> 'manage',
				'action'	=> 'delete'])
			);
			// Use a redirect to take the user back to the previous page
			// if the user chose to not delete the flag from the confirmation page.
			redirect("{$this->u_action}");
		}
	}

	/* check flag
	*
	* a function to run flag validation on
	* @param string	$form_key	The forum key add_flag/edit_flag
	* @param string	$flag_image	The flag image
	* @param string	$flag_name	The flag name
	* @param array	$errors		The possible generated errors
	* @return array
	* @access private
	*/
	private function check_flag($flag_name, $errors, $form_key = '', $flag_image = false)
	{
		if (!check_form_key($form_key))
		{
			$errors[] = $this->language->lang('FORM_INVALID');
		}

		if (empty($flag_name))
		{
			$errors[] = $this->language->lang('FLAG_ERROR_NO_FLAG_NAME');
		}

		if (!$this->can_upload_flag())
		{
			if (empty($flag_image) || !$this->images->exists($flag_image))
			{
				$errors[] = $this->language->lang('FLAG_ERROR_NO_FLAG_IMG');
			}
		}

		if ($form_key == 'add_flag')
		{
			//we don't want two flags with the same name...right?
			$sql = 'SELECT flag_name
				FROM ' . $this->flags_table . "
				WHERE flag_name = '" . $this->db->sql_escape($flag_name) . "'";
			$result = $this->db->sql_query($sql);

			if ($this->db->sql_fetchrow($result))
			{
				$errors[] = $this->language->lang('FLAG_NAME_EXISTS');
			}
			$this->db->sql_freeresult($result);
		}

		return $errors;
	}

	/**
	* Check if user is able to upload a flag
	*
	* @return bool True if user can upload, false if not
	*/
	protected function can_upload_flag()
	{
		return $this->images->can_upload() && (bool) ini_get('file_uploads');
	}

	/**
	* List current flag names
	*/
	protected function list_flag_names()
	{
		// ensure cache for the flags is built
		$this->nationalflags->cache_flags();

		$data = $this->cache->get('_user_flags');

		// because some nubs try and remove all the flags via the db so don't show an error in the ACP
		if (sizeof($data))
		{
			foreach ($data as $key => $row)
			{
				$flag[$key] = $row['flag_image'];
			}
			array_multisort($flag, SORT_NATURAL, $data);

			return implode($this->language->lang('COMMA_SEPARATOR'), $flag);
		}

		return false;
	}

	/**
	* Copy a flag image to server.
	*
	* @param array $errors Upload errors, passed by reference
	* @param string $action ACP action
	* @param string $old_flag Database-owned filename retained for an edit without an upload
	* @return	false|string	String if no errors, else false
	*/
	private function flag_upload(&$errors, $action = '', $old_flag = '')
	{
		$upload = $this->files_factory->get('upload')
			->set_error_prefix('FLAG_IMAGE_')
			->set_allowed_extensions(['gif', 'png', 'jpeg', 'jpg'])
			->set_allowed_dimensions(self::MAX_WIDTH, self::MAX_HEIGHT, self::MAX_WIDTH, self::MAX_HEIGHT);
		$file = $upload->handle_upload('files.types.form', 'flag_upload');

		// Only the database-owned filename may be retained on an edit without an upload.
		if ($action === 'edit_flag' && $file->get('realname') === '')
		{
			return $old_flag;
		}
		if (!$file->error)
		{
			try
			{
				$this->images->prepare_upload_directory();
				$file->clean_filename('unique_ext');
				$file->move_file($this->images->upload_directory());
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				$errors[] = $this->language->lang('FLAG_IMAGE_UPLOAD_DIRECTORY');
				return false;
			}
		}
		if ($file->error)
		{
			$file->remove();
			$errors = array_merge($errors, $file->error);
			return false;
		}

		return 'upload/' . $file->get('realname');
	}

	/** Remove managed images only after the final database reference is gone. */
	private function remove_unused_image($image)
	{
		$sql = 'SELECT COUNT(*) AS image_count FROM ' . $this->flags_table .
			" WHERE flag_image = '" . $this->db->sql_escape($image) . "'";
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('image_count');
		$this->db->sql_freeresult($result);
		if (!$count)
		{
			$this->images->remove($image);
		}
	}

	/**
	 * Change flag setting to not be default
	 *
	 * @return null
	 * @access private
	*/
	private function change_flag_default()
	{
		$this->db->sql_query('UPDATE ' . $this->flags_table . ' SET flag_default = 0 WHERE flag_default = 1');
	}

	/**
	 * Log Message either edit, add or delete
	 *
	 * @return message
	 * @access private
	*/
	private function log_message($log_message, $flag_name, $user_message)
	{
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_message, time(), [$flag_name]);

		$this->cache->destroy('_user_flags');
		$this->nationalflags->trash_the_cache();
		$this->nationalflags->cache_flags();

		trigger_error($this->language->lang($user_message) . adm_back_link($this->u_action));
	}

	/**
	* Display drop down of areas to display the flag
	*/
	private function flag_position($flag_position)
	{
		$s_flag_position = '';
		foreach ($this->flag_constants as $name => $value)
		{
			$selected = ($value == $flag_position) ? ' selected="selected"' : '';
			$position_name = $this->language->lang('FLAG_POSITION_' . strtoupper($name));
			$s_flag_position .= "<option value='{$value}'$selected>$position_name</option>";
		}

		return $s_flag_position;
	}

	/**
	 * Set page url
	 *
	 * @param string $u_action Custom form action
	 * @return null
	 * @access public
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
