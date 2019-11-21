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

class main_listener_test extends \phpbb_test_case
{
	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\auth\auth */
	protected $auth;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\controller\helper */
	protected $controller_helper;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\language\language */
	protected $language;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\cannedmessages\message\manager */
	protected $manager;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\template\template */
	protected $template;

	/** @var \phpbb\cannedmessages\event\main_listener */
	protected $listener;

	/**
	 * {@inheritDoc}
	 */
	public function setUp()
	{
		parent::setUp();

		$this->auth = $this->getMockBuilder('\phpbb\auth\auth')
			->disableOriginalConstructor()
			->getMock();
		$this->controller_helper = $this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->manager = $this->getMockBuilder('\phpbb\cannedmessages\message\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()
			->getMock();
		$this->listener = new \phpbb\cannedmessages\event\main_listener(
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
		$this->assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->listener);
	}

	/**
	 * Test the event listener is subscribing events
	 */
	public function test_getSubscribedEvents()
	{
		$this->assertEquals([
			'core.modify_mcp_modules_display_option',
			'core.posting_modify_template_vars',
			'core.ucp_pm_compose_modify_data',
		], array_keys(\phpbb\cannedmessages\event\main_listener::getSubscribedEvents()));
	}

	public function add_lang_to_mcp_data()
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
		$this->language->expects(($expected ? $this->once() : $this->never()))
			->method('add_lang')
			->with('info_acp_cannedmessages', 'phpbb/cannedmessages');

		// Mock up some event data
		$data_map = [
			'module' => (object) ['p_name' => $module_name],
		];

		// Define event data object
		$data = new \phpbb\event\data($data_map);

		// Call the method
		$this->listener->add_lang_to_mcp($data);
	}

	public function posting_modify_template_vars_data()
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
		$this->auth->expects($this->atMost(1))
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
