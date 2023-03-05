<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\mcp;

/**
 * Canned Messages MCP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Main MCP module
	 *
	 * @throws \Exception
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \phpbb\cannedmessages\controller\mcp_controller $mcp_controller */
		$mcp_controller = $phpbb_container->get('phpbb.cannedmessages.mcp.controller');

		// Make the $u_action url available in the MCP controller
		$mcp_controller->set_page_url($this->u_action);

		// Load a template for our MCP page
		$this->tpl_name = 'mcp_cannedmessages_' . $mode;

		// Set the page title for our MCP page
		$this->page_title = $mcp_controller->get_page_title();

		$mcp_controller->{'mode_' . $mode}();
	}
}
