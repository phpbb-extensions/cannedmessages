<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\tests\manager;

class manager_base extends \phpbb_database_test_case
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\cannedmessages\message\manager
	 */
	protected $manager;

	/**
	 * @inheritdoc
	 */
	protected static function setup_extensions()
	{
		return ['phpbb/cannedmessages'];
	}

	/**
	 * @inheritdoc
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/cannedmessages.xml');
	}

	/**
	 * @inheritdoc
	 */
	public function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();

		$config = new \phpbb\config\config(array('cannedmessages.table_lock.cannedmessages_table' => 0));
		$lock = new \phpbb\lock\db('cannedmessages.table_lock.cannedmessages_table', $config, $this->db);

		$this->manager = new \phpbb\cannedmessages\message\manager(
			new \phpbb_mock_cache(),
			new \phpbb\cannedmessages\message\nestedset(
				$this->db,
				$lock,
				'phpbb_cannedmessages'
			)
		);
	}
}
