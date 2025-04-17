<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\migrations;

class emoji_support extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\cannedmessages\migrations\install_cannedmessages_schema',
			'\phpbb\cannedmessages\migrations\update_cannedmessages_schema',
		];
	}

	public function update_schema()
	{
		return [
			'change_columns'	=> [
				$this->table_prefix . 'cannedmessages'	=> [
					'cannedmessage_content'	=> ['MTEXT_UNI', ''],
				],
			],
		];
	}

	public function revert_schema()
	{
	}
}
