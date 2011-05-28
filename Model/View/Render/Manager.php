<?php
/**
 * 
 * @desc Менеджер рендеринга
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
abstract class View_Render_Manager extends Manager_Abstract
{

	/**
	 * 
	 * @var array <View_Render_Abstract>
	 */
	private static $_views = array ();
	
	/**
	 * @var array
	 */
	private static $_templatesToRender = array ();
	
	/**
	 * 
	 * @var string
	 */
	private static $_templateExtension = '.tpl';
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Рендер по умолчанию
		 * @var string
		 */
		'default_view'		=> 'Smarty'
	);
	
	/**
	 * @desc Выводит результат работы шаблонизатора в браузер.
	 */
	public static function display ()
	{
		self::getView ()->display ();
	}
	
	/**
	 * @desc Возвращает текущий рендер.
	 * @return View_Render_Abstract
	 */
	public static function getView ()
	{
		if (!self::$_views)
		{
			Loader::load ('View_Render');
			$config = self::config ();
			self::pushViewByName ($config ['default_view']);
			//self::$_view = new View_Render (array('name' => self::$_defaultView));
		} 
		
		return end (self::$_views);
	}
	
	/**
	 * @return string
	 */
	public static function getTemplateExtension ()
	{
		return self::$_templateExtension;
	}
	
	/**
	 * @return View_Render_Abstract
	 */
	public static function popView ()
	{
//		echo 'pop' . count (self::$_views) . ' ' . end (self::$_views)->name;
		$view = array_pop (self::$_views);
		$view->popVars ();
		return $view;
	}
	
	/**
	 * 
	 * @param View_Render_Abstract $view
	 * @return View_Render_Abstract
	 */
	public static function pushView (View_Render_Abstract $view)
	{
		self::$_views [] = $view;
		return $view;
	}
	
	/**
	 * 
	 * @param integer $id
	 * @return View_Render_Abstract
	 */
	public static function pushViewById ($id)
	{
		$view = Model_Manager::byKey ('View_Render', $id);
		return self::pushView ($view);
	}
	
	/**
	 * 
	 * 
	 * @param string $name
	 * @return View_Render_Abstract
	 */
	public static function pushViewByName ($name)
	{
		$view = View_Render::byName ($name);
		$view->pushVars ();	
		return self::pushView ($view);
	}
	
	/**
	 * 
	 * @param string $value
	 */
	public static function setTemplateExtension ($value)
	{
		self::$_templateExtension = $value;
	}
	
	/**
	 * @desc Обработка шаблонов из стека.
	 * @param array $outputs
	 */
	public static function render (array $outputs)
	{
		return self::getView ()->render ($outputs);
	}
	
	/**
	 * @desc Рендер одной итерации диспетчера.
	 * @param Controller_Dispatcher_Iteration $iteration
	 * @return string
	 */
	public static function fetchIteration (
		Controller_Dispatcher_Iteration $iteration)
	{
		/**
		 * 
		 * @var $transaction Data_Transport_Transaction
		 */
		$transaction = $iteration->getTransaction ();
		
		$view = self::getView ();
		$view->assign ($transaction->buffer ());
		
		$result = $view->fetch ($iteration->getTemplate ());

		return $result;
	}
	
}
