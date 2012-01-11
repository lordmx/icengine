<?php

namespace Ice;

Loader::load ('Authorization_Abstract');
/**
 *
 * @desc Авторизация через логинзу.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Authorization_Loginza extends Authorization_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::authorize()
	 */
	public function authorize ($data)
	{
		Loader::load ('Authorization_Loginza_Token');
		$token = Authorization_Loginza_Token::tokenData ();

		Loader::load ('User_Loginza');
		$loginza = User_Loginza::byToken ($token, true, true);

		$user = $loginza ? $loginza->User : null;

		if (!$user)
		{
			$user = $this->autoregister ($token);
		}

		return $user instanceof User ? $user->authorize () : $user;
	}

	/**
	 * @desc Авторегистрация
	 * @param Authorization_Loginza_Token $token
	 * @return User|string
	 */
	public function autoregister (Authorization_Loginza_Token $token)
	{
		Loader::load ('User_Loginza');
		if (!$token->email)
		{
			return "Data_Validator_Loginza_Token::invalid";
		}

		$data = $token->data ('data');

		Loader::load ('Helper_Email');
		$user = User::create (array (
			'name'		=> $token->extractName (),
			'login'		=> (string) $token->identity,
			'email'		=> (string) $token->email,
			'password'	=> md5 (time ()),
			'phone'		=>
				isset ($data ['phone']) && is_string ($data ['phone']) ?
					$data ['phone'] :
					'',
			'active'	=> 1
		));

		$ul = new User_Loginza (array (
			'User__id'	=> $user->key (),
			'identity'	=> (string) $token->identity,
			'email'		=> (string) $token->email,
			'provider'	=> (string) $token->provider,
			'data'		=> json_encode ($data),
			'createdAt'	=> Helper_Date::toUnix ()
		));
		$ul->save ();

		return $user;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isRegistered()
	 */
	public function isRegistered ($login)
	{
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isValidLogin()
	 */
	public function isValidLogin ($login)
	{
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::findUser()
	 */
	public function findUser ($data)
	{
		Loader::load ('Authorization_Loginza_Token');
		$token = Authorization_Loginza_Token::tokenData ();

		Loader::load ('User_Loginza');
		$loginza = User_Loginza::byToken ($token);

		return $loginza ? $loginza->User : null;
	}

}