<?php

class Router
{
	public static $_prefixPart = '/vipgeo_showplace';
	
	/**
	 * @var Route
	 */
	private static $_route;
	
	/**
	 * @return Route
	 */
	public static function getRoute ()
	{
		return self::$_route;
	}
	
	public static function parse () 
	{
		//$url = ltrim (Request::uri (), self::$_prefixPart);
		$url = Request::uri ();
		if (
			substr ($url, 0, strlen (self::$_prefixPart)) == self::$_prefixPart
		)
		{
			$url = substr ($url, strlen (self::$_prefixPart));
		}
		
		$quest = strpos ($url, '?');
		if ($quest !== false)
		{
			$url = substr ($url, 0, $quest);
		}
		
		$p = strpos ($url, '?');
		if ($p !== false)
		{
			$url = substr ($url, 0, $p);
		}
		
		$url = $url ? $url : '/';
		
		$route = (array) explode ('/', trim ($url, '/'));
		
		self::$_route = self::locate ($url);
		
		if (!self::$_route)
		{
			return;
		}
		
		$parts = (array) explode ('/', trim (self::$_route->route, '/'));

		$len = min (sizeof ($route), sizeof ($parts));
		
		for ($i = 0; $i < $len; $i++)
		{
			$st = strpos ($parts [$i], ':');
			if ($st !== false)
			{
				Request::param (
					substr ($parts [$i], $st + 1), 
					isset ($route [$i]) ? substr ($route [$i], $st) : 0
				);
			}
		}
	}
	
	/**
	 * @return array
	 */
	public static function actions ()
	{
		if (!self::$_route)
		{
			return array ();
		}
		
		return self::$_route->actions ();
	}
	
	/**
	 * 
	 * @param string $url
	 * @return Route
	 */
	public static function locate ($url)
	{
		$url = '/' . trim ($url, '/') . '/';
		
		// Заменяем /12345678/ на /?/
		$template = preg_replace ('#/[0-9]{1,}/#i', '/?/', $url);
		$template = preg_replace ('#/[0-9]{1,}/#i', '/?/', $template);
		
		$select = new Query ();
		$select
			->select (array ('Route' => array ('id', 'route', 'View_Render__id')))
			->select (array ('View_Render' => array ('name' => 'viewRenderName')))
			->from ('Route')
			->from ('View_Render')
			->where ('? RLIKE template', $template)
			->where ('Route.View_Render__id = View_Render.id')
			->order (array ('weight' => Query::DESC))
			->limit (1);
		
		$row = DDS::execute ($select)->getResult ()->asRow ();
		
		if (!$row)
		{
			return null;
		}
		
		Loader::load ('Route');
		return new Route ($row);
	}
	
}