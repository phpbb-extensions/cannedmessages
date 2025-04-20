<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, 2025 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\tests\functional;

/**
 * @group functional
 */
class view_test extends \phpbb_functional_test_case
{
	/**
	 * @inheritdoc
	 */
	protected static function setup_extensions()
	{
		return ['phpbb/cannedmessages'];
	}

	public function test_mcp()
	{
		$this->add_lang_ext('phpbb/cannedmessages', 'info_mcp_cannedmessages');

		$this->login();

		$crawler = self::request('GET', 'mcp.php?i=main');
		$this->assertContainsLang('MCP_CANNEDMESSAGES_TITLE', $crawler->filter('#tabs')->text());
	}
}
