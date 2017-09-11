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
 * Canned Messages MCP module info.
 */
class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\phpbb\cannedmessages\mcp\main_module',
			'title'		=> 'MCP_CANNEDMESSAGES_TITLE',
			'modes'		=> array(
				'manage'	=> array(
					'title'	=> 'MCP_CANNEDMESSAGES_MANAGE',
					'auth'	=> 'ext_phpbb/cannedmessages && acl_m_',
					'cat'	=> array('MCP_CANNEDMESSAGES_TITLE')
				),
			),
		);
	}
}
