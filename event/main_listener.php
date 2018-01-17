<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Canned Messages Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cannedmessages\message\manager */
	protected $manager;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.modify_mcp_modules_display_option' => 'add_lang_to_mcp',
			'core.posting_modify_template_vars'	=> 'posting_modify_template_vars',
			'core.ucp_pm_compose_modify_data'	=> 'posting_modify_template_vars',
		);
	}

	/**
	 * Constructor
	 *
	 * @param \phpbb\template\template				$template			Template object
	 * @param \phpbb\auth\auth						$auth				Permissions object
	 * @param \phpbb\cannedmessages\message\manager $manager      		Canned Messages manager object
	 * @param \phpbb\language\language           	$language     		Language object
	 * @param \phpbb\controller\helper				$controller_helper	Controller helper object
	 */
	public function __construct(\phpbb\template\template $template, \phpbb\auth\auth $auth, \phpbb\cannedmessages\message\manager $manager, \phpbb\language\language $language, \phpbb\controller\helper $controller_helper)
	{
		$this->template = $template;
		$this->auth = $auth;
		$this->manager = $manager;
		$this->language = $language;
		$this->controller_helper = $controller_helper;
	}

	/**
	 * Add ACP lang file with log message keys for the MCP logs
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function add_lang_to_mcp($event)
	{
		if ($event['module']->p_name === 'mcp_logs')
		{
			$this->language->add_lang('info_acp_cannedmessages', 'phpbb/cannedmessages');
		}
	}

	/**
	 * Adds the canned messages to the posting window when user is a moderator
	 */
	public function posting_modify_template_vars()
	{
		if ($this->can_view_cannedmessages())
		{
			$this->language->add_lang('posting', 'phpbb/cannedmessages');
			$this->template->assign_vars(array(
				'S_CANNEDMESSAGES_LIST'		=> $this->manager->get_messages(),
				'U_CANNEDMESSAGE_SELECTED'	=> $this->controller_helper->route('cannedmessage_selected', array('data' => 0)),
			));
		}
	}

	/**
	 * User can view canned messages only if they are moderators
	 *
	 * @return	bool	true if the user is a moderator, false if they are not
	 */
	protected function can_view_cannedmessages()
	{
		return $this->auth->acl_getf_global('m_');
	}
}
