<?php

/**
 * @desc Результат работы транслятора
 * @package IcEngine
 * @author Илья Колесников, Юрий Шведов
 */
class Query_Translator_Result
{
	/**
	 * @desc Транслятор примененный к запросу
	 * @var Query_Translator
	 */
	protected $_translator;

	/**
	 * @desc Оттранслированный запрос
	 * @var mixed
	 */
	protected $_translatedQuery;

	/**
	 * @desc Первоначальный запрос с биндами
	 * @var Query
	 */
	protected $_query;

	/**
	 * (non-PHPDoc)
	 * @param Query $query
	 * @param type $translated_query
	 * @param Query_Translator $translator
	 */
	public function __construct (Query $query, $translated_query,
		Query_Translator $translator)
	{
		$this->_query = $query;
		$this->_translatedQuery = $translated_query;
		$this->_translator = $translator;
	}

	/**
	 * @desc Применить переменные
	 * @param array $vars
	 */
	public function applyVars ($vars)
	{
		foreach ($vars as $var => $value)
		{
			$this->_translatedQuery = str_replace (
				'{' . $var . '}',
				$value,
				$this->_translatedQuery
			);
		}
	}

	/**
	 * @desc Получить транслятор
	 * @return Query_Translator
	 */
	public function getTranslator ()
	{
		return $this->_translator;
	}

	/**
	 * @desc Получить оттранслированный запрос
	 * @return mixed
	 */
	public function getTranslatedQuery ()
	{
		return $this->_translatedQuery;
	}

	/**
	 * @desc Получить запрос
	 * @return Query
	 */
	public function getQuery ()
	{
		return $this->_query;
	}
}