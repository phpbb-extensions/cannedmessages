<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, David Colón, http://www.davidiq.com
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
			'title'		=> 'MCP_DEMO_TITLE',
			'modes'		=> array(
				'front'	=> array(
					'title'	=> 'MCP_DEMO',
					'auth'	=> 'ext_phpbb/cannedmessages',
					'cat'	=> array('MCP_DEMO_TITLE')
				),
			),
		);
	}
}
