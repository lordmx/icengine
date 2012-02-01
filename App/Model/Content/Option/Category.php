<?php

namespace Ice;

/**
 *
 * @desc Для выбора контента из заданной категории
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Content_Option_Category extends Model_Option
{

	public function before ()
	{
		$this->query->where ('Content_Category__id', $this->params ['id']);
	}

}