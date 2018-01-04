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
	'CANNEDMESSAGE_LIST'				=> 'Canned Message List',
	'NO_CANNEDMESSAGES'					=> 'No canned messages',
	'CANNEDMESSAGE_IS_CAT'				=> 'Is category',
	'CANNEDMESSAGE_CONTENT'				=> 'Message content',
	'NO_PARENT'							=> 'None',
	'CANNEDMESSAGE_PARENT'				=> 'Message parent',
	'MESSAGE_NAME_REQUIRED'				=> 'Message name is required',
	'MESSAGE_CONTENT_REQUIRED'			=> 'Message content is required when message is not a category',
	'CANNEDMESSAGE_UPDATED'				=> 'Canned message has been updated.',
	'CANNEDMESSAGE_CREATED'				=> 'Canned message has been created.',
	'CANNEDMESSAGE_INVALID_ITEM'		=> 'Canned message not specified.',
	'CANNEDMESSAGE_INVALID_PARENT'		=> 'Canned message parent does not exist.',
	'CANNEDMESSAGE_PARENT_IS_NOT_CAT'	=> 'Canned message parent is not a category.',
	'CANNEDMESSAGE_HAS_CHILDREN'		=> 'Canned message category has children and cannot be changed to be a message. Please remove children first.',
	'CANNEDMESSAGE_HAS_CHILDREN_DEL'	=> 'Canned message category has children and cannot be deleted. Delete the children first before attempting to delete the category.',
	'CANNEDMESSAGES_DEL_CONFIRM'		=> 'Are you sure you want to delete the <i>%s</i> Canned Message?',
	'CANNEDMESSAGES_DEL_CAT_CONFIRM'	=> 'Are you sure you want to delete the <i>%s</i> Canned Message category?',
	'CANNEDMESSAGE_DELETED'				=> 'Canned message has been deleted.',
	'CANNEDMESSAGE_CAT_DELETED'			=> 'Canned message category has been deleted.',
));
