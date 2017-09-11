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
	'CANNEDMESSAGES_EXPLAIN_MANAGE'		=> 'Use this form to add, remove, edit, and re-order canned messages and categories.',
	'CANNEDMESSAGES_EXPLAIN_ADD_EDIT'	=> 'This form is for creating and editing messages or categories.',
	'CREATE_CANNEDMESSAGE'				=> 'Create new canned message',
	'CANNEDMESSAGE_NAME'				=> 'Canned Message Name',
	'NO_CANNEDMESSAGES'					=> 'No canned messages',
	'CANNEDMESSAGE_IS_CAT'				=> 'Is category',
	'CANNEDMESSAGE_CONTENT'				=> 'Message content',
	'NO_PARENT'							=> 'None',
	'CANNEDMESSAGE_PARENT'				=> 'Message parent',
	'MESSAGE_NAME_REQUIRED'				=> 'Message name is required',
	'MESSAGE_CONTENT_REQUIRED'			=> 'Message content is required when message is not a category',
	'CANNEDMESSAGE_UPDATED'				=> 'Canned message has been updated.',
	'CANNEDMESSAGE_CREATED'				=> 'Canned message has been created.',
));
