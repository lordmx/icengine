<?php

namespace Ice;

/**
 *
 * @desc Для выбора потомков от указанного раздела.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Content_Category_Option_Parent extends Model_Option
{

	public function before ()
	{
		$this->query->where ('parentId', $this->params ['id']);
	}

}