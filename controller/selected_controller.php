<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\controller;

class selected_controller
{
	/** @var \phpbb\cannedmessages\message\manager */
	protected $manager;

	/** @var \phpbb\request\request */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cannedmessages\message\manager      $manager      Canned Messages manager object
	 * @param \phpbb\request\request   $request	Request object
	 */
	public function __construct(\phpbb\cannedmessages\message\manager $manager, \phpbb\request\request $request)
	{
		$this->manager = $manager;
		$this->request = $request;
	}

	/**
	 * Handle request.
	 *
	 * @param	int 	$data	Canned message ID
	 * @param	string	$mode	retrieve
	 * @return	\Symfony\Component\HttpFoundation\JsonResponse	A Symfony JsonResponse object
	 * @throws	\phpbb\exception\http_exception
	 */
	public function handle($data, $mode)
	{
		if (!empty($data) && $this->request->is_ajax())
		{
			$response = $this->{$mode}($data);

			return new \Symfony\Component\HttpFoundation\JsonResponse($response);
		}

		throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
	}

	/**
	 * Retrieves a canned message
	 *
	 * @param  int $cannedmessage_id Canned message to retrieve
	 * @return string The canned message contents
	 */
	protected function retrieve($cannedmessage_id)
	{
		$cannedmessage = $this->manager->get_message($cannedmessage_id);

		return isset($cannedmessage['cannedmessage_content']) ? html_entity_decode($cannedmessage['cannedmessage_content']) : '';
	}
}
