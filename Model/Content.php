<?php
/**
 * 
 * @desc Модель контента
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Content extends Model_Factory
{
	
	/**
	 * @desc Вовзращает контент по названию модели и ключу.
	 * В случае, если  такой модели не существовало, будет возвращена пустая
	 * модель.
	 * @param string $model_name Название модели.
	 * @param mixed $key Ключ
	 * @return Content_Abstract
	 */
	public static function byName ($model_name, $key)
	{
		$model_name = 'Content_' . ucfirst ($name);

		$content = Model_Manager::get (
			$model_name,
			$key
		);

		return $content;
	}
	
	/**
	 * @desc Расширение модели
	 * @return Content_Extending
	 */
	public function extending ()
	{
		if (!$this->extending)
		{
			return null;
		}
		
		$extending = Model_Manager::byKey ($this->extending, $this->id);
				
		if (!$extending && $this->extending && $this->id)
		{
			// Расширение не создано
			$extending = Model_Manager::get (
				$this->extending,
				$this->id
			)->firstSave ();
		}
		
		return $extending;
	}
	
	public function title ()
	{
		return $this->title . ' ' . $this->url;
	}
	
}