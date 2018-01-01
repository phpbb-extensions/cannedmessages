<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\controller;

class mcp_controller
{
	/** @var \phpbb\user */
	protected $user;

	/** @var  \phpbb\template\template */
	protected $template;

	/** @var  string Custom form action */
	protected $u_action;

	/** @var  string Current action */
	protected $action;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\cannedmessages\message\manager */
	protected $manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var string Admin path for images */
	protected $phpbb_admin_images_path;

	/** @var array List of errors */
	protected $errors = array();

	/** @var string The PHP extension in use */
	protected $php_ext;

	/** @var string The root path for phpBB */
	protected $root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\user                           $user              User object
	 * @param \phpbb\template\template              $template          Template object
	 * @param \phpbb\language\language              $language          Language object
	 * @param \phpbb\request\request                $request           Request object
	 * @param \phpbb\log\log                        $log               The phpBB log system
	 * @param \phpbb\cannedmessages\message\manager $manager           Canned Messages manager object
	 * @param string                                $root_path         phpBB root path
	 * @param string                                $adm_relative_path Admin relative path
	 * @param string                                $php_ext           PHP extension
	 */
	public function __construct(\phpbb\user $user, \phpbb\template\template $template, \phpbb\language\language $language, \phpbb\log\log $log, \phpbb\request\request $request, \phpbb\cannedmessages\message\manager $manager, $root_path, $adm_relative_path, $php_ext)
	{
		$this->user = $user;
		$this->template	= $template;
		$this->language = $language;
		$this->language->add_lang('mcp', 'phpbb/cannedmessages');
		$this->language->add_lang('acp/common');
		$this->log = $log;
		$this->manager = $manager;
		$this->request = $request;
		$this->phpbb_admin_images_path = $adm_relative_path . 'images/';
		$this->php_ext = $php_ext;
		$this->root_path = $root_path;
	}

	/**
	 * Set page url
	 *
	 * @param	string	$u_action	Custom form action
	 * @return	void
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	/**
	 * Get MCP page title for Canned Messages module
	 *
	 * @return	string	Language string for Canned Messages MCP module
	 */
	public function get_page_title()
	{
		return $this->language->lang('MCP_CANNEDMESSAGES_TITLE');
	}

	/**
	 * Process user request for manage mode
	 *
	 * @return	void
	 */
	public function mode_manage()
	{
		// Trigger specific action
		$this->action = $this->request->variable('action', '');

		if (in_array($this->action, array('add', 'edit', 'delete')))
		{
			$this->language->add_lang('posting');
			$this->{'action_' . $this->action}($this->request->variable($this->action === 'add' ? 'parent_id' : 'cannedmessage_id', 0));
			if ($this->action !== 'delete')
			{
				return;
			}
		}
		else if (in_array($this->action, array('move_up', 'move_down')))
		{
			$this->move_message($this->action, $this->request->variable('cannedmessage_id', 0));
		}

		// Otherwise default to this
		$this->list_messages();
	}

	/**
	 * Get list of messages
	 */
	protected function list_messages()
	{
		$parent_id = $this->request->variable('parent_id', 0);

		// Parent breadcrumb(s)
		if ($parent_id)
		{
			$parents = $this->manager->get_parents($parent_id);
			if (count($parents))
			{
				// Add a 'main' placeholder
				array_unshift($parents, [
					'cannedmessage_name'	=> $this->language->lang('CANNEDMESSAGE_LIST'),
					'cannedmessage_id'		=> 0,
				]);

				foreach ($parents as $parent)
				{
					$this->template->assign_block_vars('parents', array(
						'PARENT_NAME'	=> $parent['cannedmessage_name'],
						'U_PARENT'		=> $this->get_main_u_action($parent['cannedmessage_id']),
					));
				}
			}
		}

		foreach ($this->manager->get_messages($parent_id) as $cannedmessage_id => $cannedmessage_row)
		{
			$this->template->assign_block_vars('cannedmessages', array(
				'CANNEDMESSAGE_ID'		=> $cannedmessage_id,
				'CANNEDMESSAGE_NAME'	=> $cannedmessage_row['cannedmessage_name'],
				'U_CANNEDMESSAGE'		=> $cannedmessage_row['is_cat'] ? $this->get_main_u_action($cannedmessage_id) : false,
				'U_MOVE_UP'				=> $this->get_main_u_action($parent_id) . "&amp;action=move_up&amp;cannedmessage_id={$cannedmessage_id}&amp;hash=" . generate_link_hash('up' . $cannedmessage_id),
				'U_MOVE_DOWN'			=> $this->get_main_u_action($parent_id) . "&amp;action=move_down&amp;cannedmessage_id={$cannedmessage_id}&amp;hash=" . generate_link_hash('down' . $cannedmessage_id),
				'U_EDIT'				=> $this->get_main_u_action($parent_id) . "&amp;action=edit&amp;cannedmessage_id={$cannedmessage_id}",
				'U_DELETE'				=> $this->get_main_u_action($parent_id) . "&amp;action=delete&amp;cannedmessage_id={$cannedmessage_id}",
			));
		}

		$this->template->assign_vars(array(
			'U_ACTION_ADD'				=> $this->get_main_u_action($parent_id) . '&amp;action=add',
			'ICON_MOVE_UP'				=> '<img src="' . htmlspecialchars($this->phpbb_admin_images_path) . 'icon_up.gif" alt="' . $this->language->lang('MOVE_UP') . '" title="' . $this->language->lang('MOVE_UP') . '" />',
			'ICON_MOVE_UP_DISABLED'		=> '<img src="' . htmlspecialchars($this->phpbb_admin_images_path) . 'icon_up_disabled.gif" alt="' . $this->language->lang('MOVE_UP') . '" title="' . $this->language->lang('MOVE_UP') . '" />',
			'ICON_MOVE_DOWN'			=> '<img src="' . htmlspecialchars($this->phpbb_admin_images_path) . 'icon_down.gif" alt="' . $this->language->lang('MOVE_DOWN') . '" title="' . $this->language->lang('MOVE_DOWN') . '" />',
			'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . htmlspecialchars($this->phpbb_admin_images_path) . 'icon_down_disabled.gif" alt="' . $this->language->lang('MOVE_DOWN') . '" title="' . $this->language->lang('MOVE_DOWN') . '" />',
			'ICON_EDIT'					=> '<img src="' . htmlspecialchars($this->phpbb_admin_images_path) . 'icon_edit.gif" alt="' . $this->language->lang('EDIT') . '" title="' . $this->language->lang('EDIT') . '" />',
			'ICON_EDIT_DISABLED'		=> '<img src="' . htmlspecialchars($this->phpbb_admin_images_path) . 'icon_edit_disabled.gif" alt="' . $this->language->lang('EDIT') . '" title="' . $this->language->lang('EDIT') . '" />',
			'ICON_DELETE'				=> '<img src="' . htmlspecialchars($this->phpbb_admin_images_path) . 'icon_delete.gif" alt="' . $this->language->lang('DELETE') . '" title="' . $this->language->lang('DELETE') . '" />',
			'ICON_DELETE_DISABLED'		=> '<img src="' . htmlspecialchars($this->phpbb_admin_images_path) . 'icon_delete_disabled.gif" alt="' . $this->language->lang('DELETE') . '" title="' . $this->language->lang('DELETE') . '" />',
		));
	}

	/**
	 * Add a message
	 *
	 * @param $parent_id int  Optionally set what parent ID the canned message is being added for
	 */
	protected function action_add($parent_id)
	{
		if ($this->request->is_set_post('submit'))
		{
			$cannedmessage = $this->data_setup(array());

			if ($this->action_save($cannedmessage))
			{
				$this->success('CANNEDMESSAGE_CREATED', 'ADD', $cannedmessage);
			}
		}
		else
		{
			$cannedmessage = $this->data_setup([
				'parent_id'	=> $parent_id,
			]);
		}

		$this->page_setup($cannedmessage);
	}

	/**
	 * Edit a message
	 *
	 * @param $cannedmessage_id	integer	The message ID to edit
	 */
	protected function action_edit($cannedmessage_id)
	{
		if ($this->request->is_set_post('submit'))
		{
			$cannedmessage = $this->data_setup([
				'cannedmessage_id'	=> $cannedmessage_id,
			]);

			if ($this->action_save($cannedmessage))
			{
				$this->success('CANNEDMESSAGE_UPDATED', 'EDIT', $cannedmessage);
			}
		}
		else
		{
			$cannedmessage = $this->data_setup($this->manager->get_message($cannedmessage_id));
		}

		$this->page_setup($cannedmessage);
	}

	/**
	 * Saves canned message data
	 *
	 * @param $cannedmessage_data array The data to save
	 * @return bool  Save result
	 */
	protected function action_save($cannedmessage_data)
	{
		if (!check_form_key('phpbb_cannedmessages'))
		{
			$this->errors[] = $this->language->lang('FORM_INVALID');
			return false;
		}

		if (empty($cannedmessage_data['cannedmessage_name']))
		{
			$this->errors[] = $this->language->lang('MESSAGE_NAME_REQUIRED');
		}

		if (!$cannedmessage_data['is_cat'] && empty($cannedmessage_data['cannedmessage_content']))
		{
			$this->errors[] = $this->language->lang('MESSAGE_CONTENT_REQUIRED');
		}

		if (count($this->errors))
		{
			return false;
		}

		$result = $this->manager->save_message($cannedmessage_data);

		if ($result !== true)
		{
			$this->errors[] = $this->language->lang($result);
			return false;
		}

		return true;
	}

	/**
	 * Delete a canned message
	 *
	 * @param $cannedmessage_id int The canned message ID to delete
	 */
	protected function action_delete($cannedmessage_id)
	{
		$cannedmessage = $this->manager->get_message($cannedmessage_id);

		if ($cannedmessage['is_cat'] && $this->manager->has_children($cannedmessage))
		{
			trigger_error($this->language->lang('CANNEDMESSAGE_HAS_CHILDREN_DEL') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->get_main_u_action($cannedmessage['parent_id']) . '">', '</a>'));
		}

		if (confirm_box(true))
		{
			$this->manager->delete_message((int) $cannedmessage['cannedmessage_id']);
			$this->success($cannedmessage['is_cat'] ? 'CANNEDMESSAGE_CAT_DELETED' : 'CANNEDMESSAGE_DELETED', 'DELETE', $cannedmessage);
		}
		else
		{
			$title = ($cannedmessage['is_cat'] ? 'CANNEDMESSAGES_DEL_CAT_CONFIRM' : 'CANNEDMESSAGES_DEL_CONFIRM');
			confirm_box(false, $this->language->lang($title, $cannedmessage['cannedmessage_name']));
		}
	}

	/**
	 * Move a message up or down
	 *
	 * @param $direction string  The direction in which to move the message
	 * @param $cannedmessage_id int  The message ID that will be moved
	 */
	protected function move_message($direction, $cannedmessage_id)
	{
		$cannedmessage = $this->manager->get_message($cannedmessage_id);

		if (!$cannedmessage)
		{
			trigger_error($this->language->lang('CANNEDMESSAGE_INVALID_ITEM') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->get_main_u_action(0) . '">', '</a>'));
		}

		$result = $this->manager->move_message($cannedmessage_id, $direction);

		if ($result !== false)
		{
			$this->log(strtoupper($direction), array($cannedmessage['cannedmessage_name'], $result));
		}

		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array('success' => $result !== false));
		}
	}

	/**
	 * Sets up the canned message data
	 *
	 * @param $cannedmessage array  Data of existing canned message data
	 * @return array  Information from either the sent in data or from the request object
	 */
	protected function data_setup($cannedmessage)
	{
		return [
			'cannedmessage_id'			=> isset($cannedmessage['cannedmessage_id']) ? $cannedmessage['cannedmessage_id'] : 0,
			'cannedmessage_name'		=> $this->request->variable('cannedmessage_name', isset($cannedmessage['cannedmessage_name']) ? $cannedmessage['cannedmessage_name'] : '', true),
			'parent_id'					=> $this->request->variable('cannedmessage_parent', isset($cannedmessage['parent_id']) ? $cannedmessage['parent_id'] : 0),
			'is_cat'					=> $this->request->variable('is_cat', isset($cannedmessage['is_cat']) ? $cannedmessage['is_cat'] : 0),
			'cannedmessage_content'		=> $this->request->variable('cannedmessage_content', isset($cannedmessage['cannedmessage_content']) ? $cannedmessage['cannedmessage_content'] : '', true),
		];
	}

	/**
	 * Sets up the page elements for canned messages
	 *
	 * @param $cannedmessage_data array  The canned message data with which to set up the page
	 */
	protected function page_setup($cannedmessage_data)
	{
		add_form_key('phpbb_cannedmessages');

		$u_action = $this->get_main_u_action($cannedmessage_data['parent_id']);

		if ($this->action === 'edit')
		{
			$u_action .= "&amp;action=edit&amp;cannedmessage_id={$cannedmessage_data['cannedmessage_id']}&amp;hash=" . generate_link_hash('edit' . $cannedmessage_data['cannedmessage_id']);
		}
		else
		{
			$u_action .= '&amp;action=add';
		}

		$cannedmessage_content_preview = false;

		if (!empty($cannedmessage_data['cannedmessage_content']) && $this->request->is_set_post('preview'))
		{
			if (!class_exists('parse_message'))
			{
				include "{$this->root_path}includes/message_parser.{$this->php_ext}";
			}

			$message_parser = new \parse_message($cannedmessage_data['cannedmessage_content']);
			$cannedmessage_content_preview = $message_parser->format_display(true, true, true, false);
		}

		$has_errors = (bool) count($this->errors);
		$this->template->assign_vars(array(
			'S_ERROR'   => $has_errors,
			'ERROR_MSG' => $has_errors ? implode('<br />', $this->errors) : '',

			'S_CANNEDMESSAGE_ADD_OR_EDIT'	=> true,
			'U_ACTION'						=> $u_action,
			'U_ACTION_CANCEL'				=> $this->get_main_u_action($cannedmessage_data['parent_id']),
			'CANNESMESSAGE_NAME'			=> $cannedmessage_data['cannedmessage_name'],
			'S_CANNEDMESSAGES_LIST'			=> $this->manager->get_categories(),
			'S_CANNEDMESSAGE_SELECTED'		=> $cannedmessage_data['parent_id'],
			'IS_CAT'						=> $cannedmessage_data['is_cat'],
			'CANNEDMESSAGE_CONTENT'			=> $cannedmessage_data['cannedmessage_content'],
			'S_BBCODE_ALLOWED'				=> true,
			'CANNEDMESSAGE_CONTENT_PREVIEW'	=> $cannedmessage_content_preview,
		));
	}

	/**
	 * Gets the main u_action value
	 *
	 * @param $parent_id int The parent ID to append to the u_action, if needed
	 * @return string The proper u_action
	 */
	protected function get_main_u_action($parent_id)
	{
		return $this->u_action . ($parent_id > 0 ? "&amp;parent_id={$parent_id}" : '');
	}

	/**
	 * Log action
	 *
	 * @param	string			$action		Performed action in uppercase
	 * @param	string|array	$log_info	Info to add to the log can either be a string or an array
	 * @return	void
	 */
	public function log($action, $log_info)
	{
		if (!is_array($log_info))
		{
			$log_info = array($log_info);
		}
		$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, "MCP_CANNEDMESSAGE_{$action}_LOG", time(), $log_info);
	}

	/**
	 * Creates log for successful action and displays success message.
	 *
	 * @param $message 		 string The lang key to use for the success message
	 * @param $log_type 	 string The log type to create
	 * @param $cannedmessage array 	The canned message data to use for log and redirect
	 */
	protected function success($message, $log_type, $cannedmessage)
	{
		$this->log($log_type, $cannedmessage['cannedmessage_name']);

		$redirect = $this->get_main_u_action($cannedmessage['parent_id']);
		meta_refresh(3, $redirect);
		trigger_error($this->language->lang($message) . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
	}
}
