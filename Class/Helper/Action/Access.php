<?php

class Helper_Action_Access
{
    
	public static function denied ()
	{
		$dispatcher = IcEngine::$application->frontController->getDispatcher ();
		$dispatcher
			->flushActions (true)
			->push (array (
				'controller'	=> 'Authorization',
				'action'		=> 'accessDenied'
			));
	}
	
}