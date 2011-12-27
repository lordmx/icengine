<?php

namespace Ice;

/**
 *
 * @desc Абстрактный класс какого-либо API
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Api_Abstract extends Model_Factory_Delegate
{

	public function log ($text)
	{
		Loader::load ('Api_Log');
		$log = new Api_Log (array (

		));
		$log->save ();
	}

	/**
	 * @desc Возвращает данные о связи пользователя с API
	 * @param User $user Пользователь
	 * @return User_Open_Api_Abstract
	 */
	public function userAttachment (User $user)
	{
		return Model_Manager::getInstance ()->byOptions (
			'User_Api',
			array (
				'name'	=> 'Api',
				'id'	=> $this->id
			)
		);
	}

}