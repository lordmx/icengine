<?php

class View_Resource extends Objective
{
	
	/**
	 * Время модификации файла
	 * @var integer
	 */
	protected $_filemtime;
	
	/**
	 * Ссылка.
	 * @var string
	 */
	public $href;
	
	/**
	 * Тип ресурса.
	 * @var string
	 */
	public $type;
	
	/**
	 * Путь до файла в системе.
	 * @var string
	 */
	public $filePath;
	
	/**
	 * Не использовать сжатие ресурсов при упаковке.
	 * @var boolean
	 */
	public $nopack;
	
	/**
	 * Содержимое ресурса.
	 * @return string
	 */
	public function content ()
	{
		return file_get_contents (
			rtrim (IcEngine::root (), '/') . $this->href
		);
	}
	
	/**
	 * Время модификации файла.
	 * @return integer
	 */
	public function filemtime ()
	{
		if (!$this->_filemtime)
		{
			$this->_filemtime = filemtime ($this->filePath ());
		}
		return $this->_filemtime;
	}
	
	/**
	 * Путь до файла в фс.
	 * @return string
	 */
	public function filePath ()
	{
		if (!$this->filePath)
		{
			$this->filePath = rtrim (IcEngine::root (), '/') . $this->href;
		}
		return $this->filePath;
	}
	
}