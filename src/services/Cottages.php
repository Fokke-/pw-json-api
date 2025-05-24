<?php

namespace PwJsonApi;

class Cottages extends Service
{
	public function __construct()
	{
		parent::__construct();

		$this->setBasePath('/cottages');

		$this->listen('/')
			->get(function () {
				return new Response($this->getCottages());
			})
			->post(function () {
				return new Response([
					'post' => true,
				]);
			});

		$this->listen('/book')
			->get(function () {
				return new Response([
					'forms' => [
						'booking' => [],
					],
				]);
			})
			->post(function () {
				return (new Response([], 201))->with([
					'message' => 'Your booking was saved',
				]);
			});
	}

	public function getCottages(): array
	{
		return [
			'tossa' => 'on',
		];
	}
}
