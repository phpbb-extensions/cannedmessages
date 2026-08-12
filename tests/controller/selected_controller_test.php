<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\tests\controller;

class selected_controller_test extends \phpbb_test_case
{
	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\auth\auth */
	protected $auth;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\cannedmessages\message\manager */
	protected $manager;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\request\request */
	protected $request;

	/** @var \phpbb\cannedmessages\controller\selected_controller */
	protected $controller;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->auth = $this->getMockBuilder('\phpbb\auth\auth')
			->disableOriginalConstructor()
			->getMock();
		$this->manager = $this->getMockBuilder('\phpbb\cannedmessages\message\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();

		$this->controller = new \phpbb\cannedmessages\controller\selected_controller(
			$this->auth,
			$this->manager,
			$this->request
		);
	}

	public function test_moderator_can_retrieve_message()
	{
		$this->auth->expects(self::once())
			->method('acl_getf_global')
			->with('m_')
			->willReturn(true);
		$this->request->expects(self::once())
			->method('is_ajax')
			->willReturn(true);
		$this->manager->expects(self::once())
			->method('get_message')
			->with(42)
			->willReturn(['cannedmessage_content' => 'Reply &amp; details']);

		$response = $this->controller->handle(42, 'retrieve');

		self::assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		self::assertSame('Reply & details', json_decode($response->getContent(), true));
	}

	public function test_non_moderator_cannot_retrieve_message()
	{
		$this->auth->expects(self::once())
			->method('acl_getf_global')
			->with('m_')
			->willReturn(false);
		$this->request->expects(self::never())
			->method('is_ajax');
		$this->manager->expects(self::never())
			->method('get_message');

		try
		{
			$this->controller->handle(42, 'retrieve');
			self::fail('Expected an authorization exception.');
		}
		catch (\phpbb\exception\http_exception $exception)
		{
			self::assertSame(403, $exception->getStatusCode());
			self::assertSame('NOT_AUTHORISED', $exception->getMessage());
		}
	}

	public function test_non_ajax_request_is_rejected()
	{
		$this->auth->expects(self::once())
			->method('acl_getf_global')
			->with('m_')
			->willReturn(true);
		$this->request->expects(self::once())
			->method('is_ajax')
			->willReturn(false);
		$this->manager->expects(self::never())
			->method('get_message');

		$this->expectException('\phpbb\exception\http_exception');
		$this->expectExceptionMessage('NOT_AUTHORISED');

		$this->controller->handle(42, 'retrieve');
	}
}
