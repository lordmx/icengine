<?php

/**
 * @desc DDS Default Data Source
 *
 * Easy way to call querys to DB like
 * DDS::execute ($query)
 *
 * @author goorus
 */
class DDS
{
	/**
	 * Источник данных по умолчанию
	 * 
     * @var Data_Source_Abstract
	 */
	protected $source;

	/**
	 * Выполняет запрос и возвращает текущний источник
	 * 
     * @param Query_Abstract $query Запрос
	 * @param Query_Options $options Опции
     * @param boolean $auto Пытаться ли автоматически получить источник данных
	 * @return Data_Source_Abstract источник данных
	 */
	public function execute(Query_Abstract $query, $options = null, 
        $auto = true)
	{
        $dataSource = $this->source;
        if ($auto) {
            $fromParts = $query->getPart(Query::FROM);
            $from = reset($fromParts);
            $scheme = IcEngine::serviceLocator()->getService('modelScheme');
            $dataSource = $scheme->dataSource($from[Query::TABLE]);
        }
		return $dataSource->execute($query, $options);
	}

	/**
	 * Возвращает текущий источник по умолчанию
     * 
	 * @return Data_Source_Abstract
	 */
	public function getDataSource()
	{
		return $this->source;
	}

	/**
     * Инициализирован ли dds
     * 
	 * @return boolean
	 */
	public function inited()
	{
		return (bool) $this->source;
	}

	/**
	 * Изменить источник данных по умолчанию
     * 
	 * @param Data_Source_Abstract $source
	 */
	public function setDataSource(Data_Source_Abstract $source)
	{
		$this->source = $source;
	}
}