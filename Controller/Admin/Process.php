<?php
/**
 * 
 * @desc
 * @author Юрий Шведов
 * 
 */
class Controller_Admin_Process extends Controller_Abstract
{
	
	/**
	 * @desc 
	 * @var Config
	 */
	protected $_config = array (
		// see Helper_Process
		'titles'	=> array (
			0	=> 'none',
			1	=> 'ongoin',
			2	=> 'fail',
			3	=> 'success',
			4	=> 'pause',
			5	=> 'stoped'
		)
	);
	
	/**
	 * @desc Вовзращает строковое представление статуса
	 * @param integer $status
	 * @return string
	 */
	public function _titleOf ($status)
	{
		return $this->config ()->titles [$status];
	}
	
	/**
	 * @desc Вывод текущего состояния с возможностью редактирвоанияs
	 * @param Model $model
	 * @param string $field
	 */
	public function status ($model, $field)
	{
		if (!$field)
		{
			$field = 'status';
		}
		
		$title = $this->_titleOf ($model->$field);
		
		$this->_output->send (array (
			'model'		=> $model,
			'field'		=> $field,
			'status'	=> $model->$field,
			'title'		=> $title
		));
	}
	
	/**
	 *
	 * @param string $model
	 * @param integer $key
	 * @param string $field
	 * @param integer $status 
	 */
	public function change ($model, $key, $field, $status)
	{
		$resource = 'Table/' . Model_Scheme::table ($model) . '/' . $field;
		$resource = Acl_Resource::byNameCheck ($resource);
		
		if (!$resource || !$resource->userCan (User::getCurrent ()))
		{
			$this->replaceAction ('Error', 'accessDenied');
			return;
		}
		
		$model = Model_Manager::byKey ($model, $key);
		
		$model->update (array (
			$field	=> $status
		));
		
		Loader::load ('Admin_Log');

		$log = new Admin_Log (array (
			'User__id'		=> User::id (),
			'action'		=> __METHOD__,
			'table'			=> $model->table (),
			'rowId'			=> $key,
			'field'			=> $field,
			'value'			=> $status,
			'createdAt'		=> Helper_Date::toUnix ()
		));

		$log->save ();
		
		$this->_output->send (array (
			'model'	=> $model,
			'data'	=> array (
				'status'	=> $model->$field,
				'title'		=> $this->_titleOf ($model->$field)
			)
		));
	}
	
}
