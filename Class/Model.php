<?php
Loader::load ('Object_Pool');
Loader::load ('Object_Interface');
/**
 * 
 * @desc Базовая модель для всех сущностей.
 * @author Юрий
 * @package IcEngine
 *
 */
abstract class Model implements ArrayAccess
{
	protected  $_addicts = array ();
	
	protected  $_generic;
	
	/**
	 * @desc Компоненты для модели.
	 * Прикрепленные к модели изображения, видео, комментарии и пр.
	 * @var array <Coponent_Collection>
	 */
	protected	$_components = array ();
	
	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected static $_config = array ();
	
	/**
	 * @desc Связанные данные
	 * @var array
	 */
	protected	$_data = array ();
	
	/**
	 * @desc Индекс объекта для подсчета количества
	 * загруженных моделей.
	 * @var integer
	 */
	protected static	$_objectIndex = 0;
	
	/**
	 * @desc Подгруженные объекты
	 * @var array
	 */
	protected	$_joints = array ();
	
	/**
	 * @desc Данные модели
	 * @var array
	 */
	protected	$_fields;
	
	/**
	 * @desc Все данные загружены
	 * @var boolean
	 */
	protected	$_loaded;

	
	/**
	 * @desc Обновленные поля.
	 * @var array <boolean>
	 */
	protected	$_updatedFields = array ();
	
	/**
	 * @param string $method
	 * @param mixed $params
	 * @return mixed
	 * @throws Model_Exception
	 */
	public function __call ($method, $params)
	{
		if (strlen ($method) > 3 && strncmp ($method, 'get', 3) == 0)
		{
			$model = is_null ($this->_generic) ? $this : $this->_generic;
			
			return $model->attr (
				strtolower ($method [3]) .
				substr ($method, 4)
			);
		}
		Loader::load ('Model_Exception');
		throw new Model_Exception ("Method $method not found");
	}
	
	/**
	 * @desc Создает и возвращает модель.
	 * @param array $fields Данные модели.
	 */
	public function __construct (array $fields = array (), $model = null)
	{
		if (!is_null ($model))
		{
			$this->_fields = array ();
			$this->_addicts = $fields;
			$this->_generic = $model;
		}
		else 
		{
			$this->_loaded = false;
		
			self::$_objectIndex++;

			$this->_fields = $fields;

			if ($fields)
			{
				$this->set ($fields);
			}

			$this->_afterConstruct ();
		}
	}
	
	/**
	 * @desc Возвращает поле.
	 * @param string $field Поле
	 * @return mixed
	 */
	public function __get ($field)
	{
		if (!is_null ($this->_generic))
		{
			if (array_key_exists ($field, $this->_addicts))
			{
				return $this->_addicts [$field];
			}
			return $this->_generic->__get ($field);
		}
		
		if (array_key_exists ($field, $this->_fields))
		{
			return $this->_fields [$field];
		}
		
		if (isset ($this->_joints [$field]))
		{
			return $this->_joints [$field];
		}
		
		$join_field = $field . '__id';
		
		if (array_key_exists ($join_field, $this->_fields))
		{
			return $this->_joint ($field, $this->_fields [$join_field]);
		}
		
		if (!$this->_loaded)
		{
			$this->load ();
			if (
				!array_key_exists ($field, $this->_fields) &&
				array_key_exists ($join_field, $this->_fields)
			)
			{
				return $this->_joint ($field, $this->_fields [$join_field]);
			}
		}
		
		return $this->_fields [$field];
	}
	
	/**
	 * (non-PHPDoc)
	 * @return boolean
	 */
	public function __isset ($key)
	{
		if (!is_null ($this->_generic))
		{
			return isset ($this->_addicts [$key]) ||
				$this->_generic->__isset ($key);
		}
		return isset ($this->_fields [$key]);
	}
	
	/**
	 * (non-PHPDoc)
	 * @param string $field Поле.
	 * @param mixed $value Значение.
	 */
	public function __set ($field, $value)
	{
		if (!is_null ($this->_generic))
		{
			if (!$this->_generic->isLoaded ())
			{
				$this->_generic->load ();
			}

			if (array_key_exists ($field, $this->_generic->asRow ())) 
			{
				return $this->_generic->field ($field, $value);
			}

			$this->_addicts [$field] = $value;
			
			return $this;
		}
		if (!array_key_exists ($field, $this->_fields) && !$this->_loaded)
		{
			$this->load ();
		}
		
		if (array_key_exists ($field, $this->_fields))
		{
			$this->_fields [$field] = $value;
		}
		else
		{
			Loader::load ('Model_Exception');
			throw new Model_Exception ('Field unexists "' . $field . '".');
		}
	}
	
	/**
	 * @desc Метод вызывается из конструктора после завершения инициализации.
	 */
	protected function _afterConstruct ()
	{
		
	}
	
	/**
	 * @desc Присоединить сущность.
	 * @param string $model_name
	 * @param array $data
	 * @return Model Присоединенная модель.
	 */
	protected function _joint ($model_name, $key = null)
	{
		$model = is_null ($this->_generic) ? $this : $this->_generic;

		if ($key !== null)
		{
			$model->setJoint (
				$model_name,
				Model_Manager::byKey ($model_name, $key)
			);
		}
		
		return $model->getJoint ($model_name);
	}
	
	public function addicts ()
	{
		return $this->_addicts;
	}
	
	/**
	 * @desc Возвращает массив, создержащий все поля модели.
	 * @return array
	 */
	public function asRow ()
	{
		if (is_null ($this->_generic))
		{
			return $this->_fields;
		}
		return array_merge (
			$this->_addicts,
			$this->_generic->asRow ()
		);
	}
	
	/**
	 * @desc Возвращает или устанавливает значение атрибута.
	 * @param string|array $key Название атрибута или массив пар 
	 * (название => значение).
	 * @param mixed $value [optional] Новое значение атрибута.
	 * @return mixed Если не задан второй параметр, возвращает значение 
	 * аттрибута, иначе null.
	 */
	public function attr ($key)
	{
		if (!is_null ($this->_generic))
		{
			return call_user_func_array (
				array ($this->_generic, __METHOD__),
				func_get_args ()
			);
		}
		if (func_num_args () == 1)
		{
			if (!is_array ($key))
			{
				return $this->getAttribute ($key);	
			}
			else
			{
				$this->setAttribute ($key);
				return;
			}
		}
		
		$v = func_get_arg (1);
		$this->setAttribute ($key, $v);
	}
	
	/**
	 * @desc Имя класса модели.
	 * @return string
	 */
	public function className ()
	{
		return get_class ($this); 
	}
	
	/**
	 * @desc Возвращает коллекцию связанных компонентов или
	 * элемент коллекции с указанным индексом.
	 * @param string $type Тип компонентов.
	 * @param integer|null|stdClass $index|$value [optional] Индекс 
	 * для получения или коллекция для установки значения.
	 * @return Component_Collection Коллекция связанных компонентов.
	 */
	public function component ($type)
	{
		if (!is_null ($this->_generic))
		{
			return call_user_func_array (
				array ($this->_generic, __METHOD__),
				func_get_args ()
			);
		}
		
		$index = null;
		
		if (func_num_args () > 1)
		{
			$arg1 = func_get_arg (1);
			if (is_null ($arg1) || ($arg1 instanceof stdClass))
			{
				$this->_components [$type] = $arg1;
				return ;
			}
			
			if (is_int ($arg1))
			{
				$index = $arg1;
			}
		}
		
		if (!isset ($this->_components [$type]))
		{
			$this->_components [$type] = Component::getFor ($this, $type);
		}
		
		return is_null ($index) ? 
			$this->_components [$type] : 
			$this->_components [$type]->item ($index);
	}
	
	/**
	 * @desc Загружает и возвращает конфиг для модели
	 * @param string $class Класс модели, если отличен от get_class ($this)
	 * @return Objective
	 */
	public static function config ($class = null)
	{
		if (is_array (static::$_config))
		{
			static::$_config = Config_Manager::get (
				$class ? $class : get_called_class (),
				static::$_config
			);
		}
		return static::$_config;
	}
	
	/**
	 * @desc Устанавливает или получает связанные данные объекта
	 * @param string $key Ключ.
	 * @param mixed $value [optional] Значение (не обязательно).
	 * @return mixed Текущее значение или null.
	 */
	public function data ($key)
	{
		if (!is_null ($this->_generic))
		{
			return call_user_func_array (
				array ($this->_generic, __METHOD__),
				func_get_args ()
			);
		}
		if (func_num_args () == 1)
		{
			return isset ($this->_data [$key]) ? $this->_data [$key] : null;
		}
		
		$this->_data [$key] = func_get_arg (1);
	}
	
	/**
	 * @desc Удаление модели.
	 */
	public function delete ()
	{
		$model = is_null ($this->_generic) ? $this : $this->_generic;
		
		$key = $model->key ();
		
		if ($key)
		{
			Model_Manager::remove ($model);
		}
	}
	
	/**
	 * @desc Возвращает коллекцию моделей типа $model,
	 * связанных по первичному ключу с этой моделью.
	 * В модели $model должно существовать поле "THISMODEL__id",
	 * где THISMODEL - название этой модели.
	 * @param string $model_name
	 * @return Model_Collection
	 */
	public function external ($model_name)
	{
		$model = is_null ($this->_generic) ? $this : $this->_generic;
		
		$field = '`' . $model_name . '`.`' . $model->modelName () . '__id`';
		return Model_Collection_Manager::byQuery (
			$model_name,
			Query::instance ()
				->where ($field, $model->key ())
		);
	}
	
	/**
	 * @desc Получение или установка значения
	 * @param string $key Поле.
	 * @param mixed $value Значение (не обязательно).
	 * Если указано значение, оно будет записано в поле.
	 * @return mixed Если $value не передан, будет возвращено значение поля.
	 */
	public function field ($key)
	{
		if (func_num_args () > 1)
		{
			$this->__set ($key, func_get_arg (1));
		}
		else
		{
			return $this->__get ($key);
		}
	}
	
	/**
	 * @desc Освободить модель и поместить ее в пул моделей
	 */
	public function free ()
	{
		Object_Pool::push ($this);
	}
	
	public function generic ()
	{
		return $this->_generic;
	}
	
	/**
	 * @desc Получение значения атрибута
	 * @param string $key Название атрибута.
	 * @return mixed Значение атрибута.
	 * Если атрибута не существует, возвращает null.
	 */
	public function getAttribute ($key)
	{
		$model = is_null ($this->_generic) ? $this : $this->_generic;
		
		return Attribute_Manager::get ($model, $key);
	}
	
	/**
	 * @desc Получить значения полей
	 * @return array<string>
	 */
	public function getFields ()
	{
		return $this->asRow ();
	}
	
	/**
	 * @desc Проверяет существование поля в модели.
	 * @return boolean
	 */
	public function hasField ($field)
	{
		if (!is_null ($this->_generic))
		{
			return isset ($this->_addicts [$field]) ||
				$this->_generic->hasField ($field);
		}
		
		if (!isset ($this->_fields))
		{
			if ($this->_loaded)
			{
				return false;
			}
			
			$this->load ();
		}
		
		return isset ($this->_fields [$field]);
	}
	
	public function getJoint ($model)
	{
		if (!is_null ($this->_generic))
		{
			return $this->_generic->getJoin ($model);
		}
		
		return $this->_joints [$model];
	}
	
	/**
	 * @desc Определяет загружена ли модель
	 * @return boolean
	 */
	public function isLoaded ()
	{
		if (!is_null ($this->_generic))
		{
			return $this->_generic->isLoaded ();
		}
		
		return $model->_loaded;
	}
	
	/**
	 * @desc Возвращает значение первичного ключа
	 * @return string|null
	 */
	public function key ()
	{
		$model = is_null ($this->_generic) ? $this : $this->_generic;
		
		$kf = $model->keyField ();
		
		if (!$model->hasField ($kf))
		{
			return null;
		}
		
		return $model->field ($kf);
	}
	
	/**
	 * @desc Имя поля первичного ключа
	 * @return string
	 */
	public function keyField ()
	{
		return Model_Scheme::keyField ($this->modelName ());
	}
	
	/**
	 * @desc Имя класса модели
	 * @return string
	 */
	public function modelName ()
	{
		return get_class ($this);
	}
	
	/**
	 * @desc Проверяет существование поля.
	 * @param string $offset Название поля
	 * @return boolean true если поле существует
	 */
	public function offsetExists ($offset)
	{
		if (!is_null ($this->_generic))
		{
			return $this->_generic->hasField ($offset);
		}
		
		return isset ($this->_fields [$offset]);
	}

	/**
	 * @see Model::__get
	 */
	public function offsetGet ($offset)
	{
		return $this->__get ($offset);
	}

	/**
	 * @see Model::__set
	 */
	public function offsetSet ($offset, $value)
	{
		$this->__set ($offset, $value);
	}

	/**
	 * @desc Исключение поля из модели.
	 * @param string $offset название поля
	 */
	public function offsetUnset ($offset)
	{
		if (!is_null ($this->_generic))
		{
			return $this->_generic->unsetField ($offset);
		}
		
		unset ($this->_fields [$offset]);
	}
	
	/**
	 * @desc Сбросить модель
	 */
	public function reset ()
	{
		if (!is_null ($this->_generic))
		{
			$this->_addicts = array ();
			$this->_generic->reset ();
			
			return;
		}
		$this->_attributes = array ();
		$this->_data = array ();
		$this->_fields = array ();
		$this->_joints = array ();
		$this->_loaded = false;
	}
	
	/**
	 * @desc Название ресурса модели.
	 * Состоит из название модели и первичного ключа.
	 * @return string
	 */
	public function resourceKey ()
	{
		$model = is_null ($this->_generic) ? $this : $this->_generic;
		
		return $model->modelName () . '__' . $model->key ();
	}
	
	/**
	 * @desc Сохранение данных модели
	 * @param boolean $hard_insert
	 * @return Model
	 */
	public function save ($hard_insert = false)
	{
		$model = is_null ($this->_generic) ? $this : $this->_generic;
		
		Model_Manager::set ($model, $hard_insert);
		
		return $this;
	}
	
	/**
	 * @desc Установка значений полей без обновления источника.
	 * При использовании этого метод не проверяется сущестовование полей
	 * у модели. Это позволяет установить поля для создаваемой модели,
	 * однако может привести к ошибкам в дальнейшем при сохранее, если 
	 * были заданы несуществующие поля.
	 * @param string|array $field Имя поля или массив пар "поле - значение".
	 * @param string $value Значение поля для случае, если первым параметром 
	 * передано имя.
	 */
	public function set ($field, $value = null)
	{
		if (!is_null ($this->_generic))
		{
			if ($this->_generic->hasField ($field)) 
			{
				$this->_generic->field ($field, $value);
				return $this;
			}
			
			$this->_addicts [$field] = $value;

			return $this;
		}
		
		$this->_loaded = true;
		
		$fields = is_array ($field) ? $field : array ($field => $value);
		
		$this_model = $this->modelName ();
		
		foreach ($fields as $key => $value)
		{
			$p = strpos ($key, '__');
			if ($p === false)
			{
				$this->_fields [$key] = $value;
			}
			else
			{
				$model = substr ($key, 0, $p);
				$field = substr ($key, $p + 2);
			
				if ($model == $this_model)
				{
					$this->_fields [$field] = $value;
				}
				else
				{
					$this->_fields [$key] = $value;
				}
			}
		}
	}
	
	/**
	 * @desc Устанавливает значение аттрибута.
	 * @param string|array $key Название аттрибута или массив 
	 * пар (название => значение).
	 * @param mixed $value [optional] Новое значение аттрибута.
	 */
	public function setAttribute ($key, $value = null)
	{
		$model = is_null ($this->_generic) ? $this : $this->_generic;
		
		Attribute_Manager::set ($model, $key, $value);
	}
	
	public function setJoint ($model, $value)
	{
		if (!is_null ($this->_generic))
		{
			$this->_generic->setJoin ($model, $value);
			return;
		}
		$this->_joints [$model] = $value;
	}
	
	/**
	 * @desc Тихое получение или установка поля.
	 * @param string $key Название поля.
	 * @param mixed $value [optional] Значение поля.
	 * @return mixed Текущее значение поля или null.
	 */
	public function sfield ($key)
	{
		if (!is_null ($this->_generic))
		{
			if (array_key_exists ($field, $this->_addicts))
			{
				return $this->_addicts [$field];
			}

			return $this->_generic->sfield ($field);
		}
		
		if (func_num_args () > 1)
		{
			$this->set ($key, func_get_arg (1));
		}
		
		return $this->hasField ($key) ?
			$this->_fields [$key] :
			null;
	}
	
	/**
	 * @desc Таблица БД
	 * @see Model::modelName ()
	 * @return string
	 */
	public function table ()
	{
		return $this->modelName ();
	}

	/**
	 * @desc Возвращает имя сущности
	 * @return string
	 */
	public function title ()
	{
		$model = is_null ($this->_generic) ? $this : $this->_generic;
		
		return $model->name;
	}
	
	/**
	 * @desc Загрузка данных модели.
	 * @param mixed $key Первичный ключ.
	 * @return Model Эта модель.
	 */
	public function load ()
	{
		$model = is_null ($this->_generic) ? $this : $this->_generic;
		
		$model->loaded ();
		
		return Model_Manager::get (
			$model->modelName (),
			$model->key (),
			$model
		);
	}
	
	public function loaded ()
	{
		$this->_loaded = true;
	}
	
	/**
	 * @desc Проверить модель на валидность по полям
	 * @param mixed (string,string|array<string>|Query) $fields
	 * @return boolean
	 */
	public function validate ($fields)
	{
		if (!is_null ($this->_generic))
		{
			return $this->_generic->validate ($fields);
		}
		
		$args = func_get_args ();
		if (sizeof ($args) == 2)
		{
			$args = array ($args [0] => $args [1]);
		}
		else
		{
			$args = $args [0];
			if ($args instanceof Query)
			{
				$tmp = array ();
				$args = $args->getPart (Query::WHERE);
				
				foreach ($args as $arg)
				{
					$tmp [$arg [Query::WHERE]] = $arg [Query::VALUE];
				}
			}
		}
		/**
		 * @var Model $valid
		 */
		$valid = true;
		
		foreach ((array) $args as $field => $value)
		{
			if ($this->_fields [$field] != $value)
			{
				$valid = false;
				break;
			}
		}
		return $valid;
	}
	
	/**
	 * @desc Удаляет поле из объекта.
	 * Используется в Model_Manager для удаления первичного ключа перед 
	 * вставкой в БД.
	 * @param string $name Имя поля.
	 * @return Model Эта модель.
	 */
	public function unsetField ($name)
	{
		if (!is_null ($this->_generic))
		{
			if (array_key_exists ($name, $this->_addicts))
			{
				unset ($this->_addicts [$name]);
				return;
			}
			else
			{
				$this->_generic->unsetField ($name);
				return;
			}
		}
		
		if (array_key_exists ($name, $this->_fields))
		{
			unset ($this->_fields [$name]);
		}
		return $this;
	}
	
	/**
	 * @desc Обновление данных модели и полей в БД.
	 * @param array $data Массив пар (поле => значение).
	 * @return Model Эта модель.
	 */
	public function update (array $data)
	{
		if (!is_null ($this->_generic))
		{
			if (!$this->_generic->isLoaded ())
			{
				$this->_generic->load ();
			}

			foreach ($data as $field=>$value)
			{
				if (!$this->_generic->hasField ($field))
				{
					$this->_addicts [$field] = $value;
					unset ($data [$field]);
				}
			}

			if ($data)
			{
				$this->_generic->update ($data);
			}
			
			return $this;
		}
		
		foreach ($data as $key => $value)
		{
			$this->_updatedFields [$key] = true;
		}
		$this->set ($data);
		return $this->save ();
	}
	
	/**
	 * @desc Аккуратное обновление модели. Используется, когда в модели
	 * могут присутствовать посторонние поля (результаты запросов опций и
	 * т.п.). В таком случае все поля будут помещены в буфер, модель будет
	 * сохранена, после чего отложенные поля будут обратно наложены.
	 * @param array $data Массив пар (поле => значение).
	 * @return Model Эта модель.
	 */
	public function updateCarefully (array $data)
	{
		if (!is_null ($this->_generic))
		{
			if (!$this->_generic->isLoaded ())
			{
				$this->_generic->load ();
			}

			foreach ($data as $field=>$value)
			{
				if (!$this->_generic->hasField ($field))
				{
					$this->_addicts [$field] = $value;
					unset ($data [$field]);
				}
			}

			if ($data)
			{
				$this->_generic->update ($data);
			}
			
			return $this;
		}
		
		foreach ($data as $key => $value)
		{
			$this->_updatedFields [$key] = true;
		}
		
		$this->set ($data);
		
		$pseudos = array ();
		
		// Список существующий в модели полей
		$scheme = Model_Scheme::fieldsNames ($this->modelName ());
		
		foreach ($this->_fields as $name => $value)
		{
			if (array_search ($name, $scheme) === false)
			{
				// Псевдополе
				$pseudos [$name] = $value;
				unset ($this->_fields [$name]);
			}
		}
		
		$this->save ();
		
		$this->_fields = array_merge (
			$this->_fields,
			$pseudos
		);
		
		return $this;
	}
	
}