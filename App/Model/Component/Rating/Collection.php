<?php

namespace Ice;

/**
 *
 * @desc Компонент рейтинг для сущностей
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Component_Rating_Collection extends Component_Collection
{

	/**
	 * @desc Возвращает рейтинг, связанный с сущностью.
	 * Если такого не сущетсвует, создает его.
	 * @return Component_Rating
	 */
	public function single ()
	{
		$rating = $this->first ();
		if (!$rating)
		{
			$rating = Component_Rating::create (array (
				'table'			=> $this->_model->table (),
				'rowId'			=> $this->_model->key (),
				'value'			=> 0,
				'votes'			=> 0,
				'changeTime'	=> Helper_Date::toUnix ()
			))->save ();
			$this->add ($rating);
		}
		return $rating;
	}

	/**
	 * @desc Изменить рейтинг
	 * @param integer $change Изменение рейтинга.
	 * @return Component_Rating_Collection
	 */
	public function increment ($change)
	{
		$this->single ()->increment ($change);
		return $this;
	}

	/**
	 * @desc Возвращает значение рейтинга.
	 * @return integer
	 */
	public function value ()
	{
		$this->single ()->value;
	}

	/**
	 * @desc Возвращает количество голосов.
	 * @return integer
	 */
	public function votes ()
	{
		$this->single ()->votes;
	}

}