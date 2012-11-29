<?php

/**
 * Менеджер конфигурация
 * 
 * @author goorus, morph
 */
class Config_Manager
{
	/**
	 * Путь до конфигов от корня сайта
	 * 
     * @var string
	 */
	protected $pathToConfig = array('Ice/Config/');

	/**
	 * Флаг означающий, что идет процесс загрузки конфига,
	 * необходим для предотвращения бесконечной рекурсии при
	 * загрузке конфигов для менеджера ресурсов.
	 * 
     * @var boolean
	 */
	protected $inLoading = false;

	/**
	 * Добавляет путь для загрузки конфигураций
	 * 
     * @param string $path
	 */
	public function addPath($path)
	{
		$this->pathToConfig[] = $path;
	}

	/**
	 * Пустой конфиг.
	 * 
     * @return Config_Array
	 */
	public function emptyConfig()
	{
		return new Config_Array(array());
	}

    /**
     * Получить пути до конфигураций
     * 
     * @return type
     */
	public function getPaths()
	{
		return $this->pathToConfig;
	}

	/**
	 * Загружает и возвращает конфиг.
	 * 
     * @param string $type Тип конфига.
	 * @param string|array $config [optional] Название или конфиг по умолчанию.
	 * 		Если параметром $config переданы настройки по умолчанию,
	 * 		результатом функции будет смержованный конфиг.
	 * @return Objective
	 */
	public function get($type, $config = '')
	{
		$resourceKey = $type . 
            (is_string($config) && $config ? '/' . $config : '');
        if ($this->inLoading) {
			return $this->load($type, $config);
		}
		$this->inLoading = true;
        $serviceLocator = IcEngine::serviceLocator();
        $resourceManager = $serviceLocator->getService('resourceManager');
		$storedConfig = $resourceManager->get('Config', $resourceKey);
		$this->inLoading = false;
		if (!$storedConfig) {
			$storedConfig = $this->load($type, $config);
			$resourceManager->set('Config', $resourceKey, $storedConfig);
		}
		return $storedConfig;
	}

	/**
	 * Загрузка реального конфига, игнорируя менеджер ресурсов.
	 * 
     * @param string $type Тип конфига.
	 * @param string|array $config [optional] Название или конфиг по умолчанию.
	 */
	public function getReal($type, $config = null)
	{
		return $this->load($type, $config);
	}

    /**
	 * Загружает конфиг из файла и возвращает класс конфига.
	 * 
     * @param string $type Тип конфига.
	 * @param string|array $config Название конфига или конфиг по умолчанию.
	 * @return Config_Array|Objective Заруженный конфиг.
	 */
	protected function load($type, $config = '')
	{
		$paths = (array) $this->pathToConfig;
		$result = null;
		foreach ($paths as $path)
		{
			$filename = IcEngine::root() . $path.
				str_replace('_', '/', $type) .
				(is_string($config) && $config ? '/' . $config : '') .
				'.php';
			if (is_file($filename)) {
				$ext = ucfirst(strtolower(substr(strrchr($filename, '.'), 1)));
				$className = 'Config_' . $ext;
				if (is_null($result)) {
					$result = new $className($filename);
				} else {
					$result = $result->merge(new $className($filename));
				}
			} else {
				$result = $this->emptyConfig();
			}
			if ($result) {
				return is_array($config) ? $result->merge($config) : $result;
			}
		}
	}
    
	/**
	 * @desc Меняет путь до конфига
	 * @param mixed $path
	 */
	public static function setPathToConfig ($path)
	{
		self::$_pathToConfig = $path;
	}
}