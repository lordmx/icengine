<?php

/**
 * 
 * Абстрактный класс валидатора
 * @author Юрий
 *
 */

abstract class Data_Validator_Abstract 
{
	
	/**
	 * Валидация строки
	 * @param string $data
	 * 		Данные
	 * @return true|string
	 * 		true, если данные прошли валидацию или 
	 * 		строка ошибки.
	 */
	public function validate ($data)
	{
		return true;
	}
	
	/**
	 * Валидация поля с использованием схемы
	 * @param string $field
	 * 		Название поля.
	 * @param stdClass $data
	 * 		Все данные.
	 * @param stdClass $scheme
	 * 		Схема.
	 * @return true|string
	 * 		true, если данные прошли валидацию или
	 * 		строка ошибки.
	 */
	public function validateEx ($field, stdClass $data, stdClass $scheme)
	{
		return $this->validate ($data->$field);
	}
	
}