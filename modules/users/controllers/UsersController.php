<?php

class UsersController extends Controller {

	public function __construct()
	{
		$this->beforeFilter('logged_in', array(
			'only' => array('getProfile'),
		));

		$this->beforeFilter('logged_out', array(
			'only' => array('getLogin', 'postLogin')
		));
	}

	public function getProfile()
	{
		$this->page->addBreadcrumb('Profile');

		$this->page->setContent('users::profile');
	}

	/**
	 * Show the login form.
	 *
	 * @return void
	 */
	public function getLogin()
	{
		$this->page->addBreadcrumb('Login');

		$this->page->setContent('users::login');
	}

	/**
	 * Log the user in.
	 *
	 * @return RedirectResponse
	 */
	public function postLogin()
	{
		$form = Validator::make(Input::all(), array(
			'email'    => array('required', 'email'),
			'password' => array('required'),
		));

		if($form->passes())
		{
			$credentials = array(
				'email'    => Input::get('email'),
				'password' => Input::get('password'),
			);

			if(Auth::attempt($credentials))
			{
				return Redirect::to('users/profile');
			}

			else
			{
				$errors = new MessageBag;

				$errors->add('login', 'Invalid credentials.');
			}
		}

		else
		{
			$errors = $form->messages();
		}

		Input::flash();

		return Redirect::to('users/login')->withErrors($errors);
	}

	/**
	 * Log the user out.
	 *
	 * @return RedirectResponse
	 */
	public function getLogout()
	{
		Auth::logout();

		return Redirect::to('users/login');
	}
}