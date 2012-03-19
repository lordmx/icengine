<?php
/**
 * 
 * @desc Ресурс прав доступа.
 * Роли получают доступ не на модель, а на ресурсы.
 * Названия ресурсов следует строить 
 * @package IcEngine
 * 
 */
class Acl_Resource extends Model
{
	
    /**
     * @desc Разделитель составных частей имени ресурса.
     * @var string
     */
	const NAME_PART_DELIM = '\\';
	
	/**
	 * @desc Роли, имеющие доступ
	 * @var Acl_Role_Collection
	 */
	public $_roles;
	
	/**
	 * 
	 * @param string|array $name
	 * @param $autocreate
	 * @return Acl_Resource
	 */
	public static function byNameAuto ($name)
	{
	    $name = is_array ($name) ? 
	        implode (self::NAME_PART_DELIM, $name) :
	        implode (self::NAME_PART_DELIM, func_get_args ());
	        
	    if (empty ($name))
		{
			throw new Exception ('Empty resource name.');
		}
		
		$resource = self::byNameCheck ($name);

		if (!$resource)
		{
			$resource = new Acl_Resource (array (
				'name'		=> $name
			));
			
			return $resource->save ();
		}
		
		return $resource;
	}
	
	/**
	 * @desc Проверяет существование ресурса. Возвращает ресурс или null.
	 * @param string|array $name
	 * @return Acl_Resource Ресурс или null.
	 */
	public static function byNameCheck ($name)
	{
	    $name = is_array ($name) ? 
	        implode (self::NAME_PART_DELIM, $name) :
	        implode (self::NAME_PART_DELIM, func_get_args ());

		if (empty ($name))
		{
			return null;
		}
		
		return Model_Manager::byQuery (
		    __CLASS__,
		    Query::instance ()
		   		->where ('name', $name)
		);
	}
	
	/**
	 * @desc Создать несколько ресурсов
	 * @param array $names
	 * @param array $add_names
	 * @return array
	 */
	public static function create (array $names, array $add_names)
	{
		$resources = array ();
		
		$names = implode (self::NAME_PART_DELIM, $names);
		
		foreach ($add_names as $name)
		{
			$resources [] = self::byNameAuto (
				$names,
				$name
			);
		}
		
		return $resources;
	}
	
	/**
	 * @desc Возвращает коллекцию ролей, имеющих доступ к этому ресурсу.
	 * @return Acl_Role_Collection Коллекция ролей.
	 */
	public function roles ()
	{
		Loader::load ('Helper_Link');
		if (!$this->_roles)
		{
		    $this->_roles = Helper_Link::linkedItems ($this, 'Acl_Role');
		}
		
		return $this->_roles;
	}
	
	/**
	 * @desc Имеет ли пользователь доступ.
	 * @param User $user Пользователь
	 * @return boolean true, если пользователь имеет доступ, иначе - false.
	 */
	public function userCan (User $user)
	{
		return $user->hasRole ('editor') || $this->roles ()->userAttached ($user);
	}
	
}