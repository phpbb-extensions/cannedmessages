<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'MCP_CANNEDMESSAGE_ADD_LOG'			=> '<strong>Canned Message added</strong><br />» %s',
	'MCP_CANNEDMESSAGE_EDIT_LOG'		=> '<strong>Canned Message edited</strong><br />» %s',
	'MCP_CANNEDMESSAGE_DELETE_LOG'		=> '<strong>Canned Message deleted</strong><br />» %s',
	'MCP_CANNEDMESSAGE_MOVE_UP_LOG'		=> '<strong>Moved canned message</strong> %1$s <strong>above</strong> %2$s',
	'MCP_CANNEDMESSAGE_MOVE_DOWN_LOG'	=> '<strong>Moved canned message</strong> %1$s <strong>below</strong> %2$s',
));
