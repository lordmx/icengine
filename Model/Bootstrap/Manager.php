<?php
/**
 * 
 * @desc Менеджер загрузчиков
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Bootstrap_Manager
{
	
	/**
	 * @desc Загрузчики
	 * @var array <Bootstrap_Abstract>
	 */
	protected static $_items;
	
	/**
	 * @desc Текущий загрузчик
	 * @var Bootstrab_Abstract
	 */
	protected static $_current;
	
	/**
	 * @desc Создает и возвращает загрузчик.
	 * @param string $name
	 * @return Bootstrap_Abstract
	 */
	public static function get ($name)
	{
		if (!isset (self::$_items [$name]))
		{
			$class = 'Bootstrap_' . $name;
			Loader::load ($class);
			self::$_items [$name] = new $class ();
		}
		
		if (!self::$_current)
		{
			self::$_current = self::$_items [$name];
		}
		
		return self::$_items [$name];
	}
	
	/**
	 * @desc Возвращает текущий загрузчик.
	 * @return Bootstrap_Abstract
	 */
	public static function current ()
	{
		return self::$_current;
	}
	
}