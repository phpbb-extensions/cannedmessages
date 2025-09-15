<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\tests\event;

use phpbb\auth\auth;
use phpbb\cannedmessages\event\main_listener;
use phpbb\cannedmessages\message\manager;
use phpbb\controller\helper;
use phpbb\event\data;
use phpbb\language\language;
use phpbb\template\template;
use phpbb_test_case;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener_test extends phpbb_test_case
{
	/** @var MockObject|auth */
	protected auth|MockObject $auth;

	/** @var MockObject|helper */
	protected helper|MockObject $controller_helper;

	/** @var MockObject|language */
	protected language|MockObject $language;

	/** @var MockObject|manager */
	protected MockObject|manager $manager;

	/** @var MockObject|template */
	protected template|MockObject $template;

	/** @var main_listener */
	protected main_listener $listener;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->auth = $this->getMockBuilder(auth::class)
			->disableOriginalConstructor()
			->getMock();
		$this->controller_helper = $this->getMockBuilder(helper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->language = $this->getMockBuilder(language::class)
			->disableOriginalConstructor()
			->getMock();
		$this->manager = $this->getMockBuilder(manager::class)
			->disableOriginalConstructor()
			->getMock();
		$this->template = $this->getMockBuilder(template::class)
			->disableOriginalConstructor()
			->getMock();
		$this->listener = new main_listener(
			$this->template,
			$this->auth,
			$this->manager,
			$this->language,
			$this->controller_helper
		);
	}

	/**
	 * Test the event listener is constructed correctly
	 */
	public function test_construct()
	{
		self::assertInstanceOf(EventSubscriberInterface::class, $this->listener);
	}

	/**
	 * Test the event listener is subscribing events
	 */
	public function test_getSubscribedEvents()
	{
		self::assertEquals([
			'core.modify_mcp_modules_display_option',
			'core.posting_modify_template_vars',
			'core.ucp_pm_compose_modify_data',
		], array_keys(main_listener::getSubscribedEvents()));
	}

	public static function add_lang_to_mcp_data(): array
	{
		return [
			['mcp_logs', true],
			['acp_logs', false],
		];
	}

	/**
	 * @dataProvider add_lang_to_mcp_data
	 * @param $module_name
	 * @param $expected
	 */
	public function test_add_lang_to_mcp($module_name, $expected)
	{
		// Set expected calls for add_lang()
		$this->language->expects(($expected ? self::once() : self::never()))
			->method('add_lang')
			->with('info_acp_cannedmessages', 'phpbb/cannedmessages');

		// Mock up some event data
		$data_map = [
			'module' => (object) ['p_name' => $module_name],
		];

		// Define event data object
		$data = new data($data_map);

		// Call the method
		$this->listener->add_lang_to_mcp($data);
	}

	public static function posting_modify_template_vars_data(): array
	{
		return [
			[true],
			[false],
		];
	}

	/**
	 * @dataProvider posting_modify_template_vars_data
	 * @param $expected
	 */
	public function test_posting_modify_template_vars($expected)
	{
		$calls = $expected ? 'once' : 'never';

		// Set expected auth calls
		$this->auth->expects(self::atMost(1))
			->method('acl_getf_global')
			->with('m_')
			->willReturn($expected);

		// Set expected calls for add_lang()
		$this->language->expects($this->$calls())
			->method('add_lang')
			->with('posting', 'phpbb/cannedmessages');

		// Set expected calls for assign_vars()
		$this->template->expects($this->$calls())
			->method('assign_vars');

		// Set expected calls for get_messages()
		$this->manager->expects($this->$calls())
			->method('get_messages');

		// Set expected calls for route()
		$this->controller_helper->expects($this->$calls())
			->method('route');

		// Call the method
		$this->listener->posting_modify_template_vars();
	}
}
