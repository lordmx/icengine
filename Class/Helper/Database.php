<?php

class Helper_Database 
{
	/**
	 * @desc Получить список колонок таблицы
	 * @param string $table
	 * @return Objective
	 */
	public static function fields ($table)
	{
		$query = Query::instance ()
			->show ('FULL COLUMNS')
			->from ($table);
			
		$status = DDS::execute (
			$query 
		)
			->getResult ()
				->asTable ();
				
		return new Objective ($status);
	}
	
	/**
	 * @desc Получить список таблиц текущей базы данных
	 * @return Objective
	 */
	public static function tables ()
	{
		$query = Query::instance ()
			->show ('TABLE STATUS');
		
		$query->resetPart (Query::FROM);
			
		$status = DDS::execute (
			$query 
		)
			->getResult ()
				->asTable ();
				
		return new Objective ($status);
	}
}