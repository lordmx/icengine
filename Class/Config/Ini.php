<?php
if (!class_exists ('Config_Array'))
class Config_Ini extends Config_Array
	public function __construct ($path = null)
	{
		{
			$ini = parse_ini_file($path);
			parent::__construct ($ini);
		}
    }
} 