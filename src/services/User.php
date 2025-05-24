<?php

namespace PwJsonApi;

/**
 * Service for user related endpoints
 */
class User extends Service
{
	public function __construct()
	{
		parent::__construct();

		$this->setBasePath('/user');

		$this->listen('/')
			->get(function () {
				// Return member data
				return new Response($this->getUserDataFromRegister());
			})
			->post(function () {
				// Save member data
				// Post data to Sense etc etc...

				// ...and return saved member data
				return (new Response($this->getUserDataFromRegister()))->with([
					'message' => 'Member data saved successfully!',
				]);
			});

		$this->listen('/logout')->post(function () {
			return (new Response())->with([
				'message' => 'You have logged out successfully!',
			]);
		});
	}

	public function getUserDataFromRegister()
	{
		return [
			'first_name' => 'Avoine',
			'last_name' => 'Testaaja',
		];
	}
}
