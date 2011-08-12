<?phpif (!class_exists ('Objective')){	require_once dirname (__FILE__) . '/../Objective.php';}/** *  * @desc Конфиг из массива * @author Юрий * @package IcEngine * */class Config_Array extends Objective{	/**	 * 	 * @param array $data	 */	public function __construct (array $data)	{		parent::__construct ($data);	}		/**	 * @desc Получение конфига на основе базового и изменений.	 * @param array|Objective $base_config	 * @return Objective	 */	public function merge ($base_config)	{		if (is_array ($base_config))		{			$base_config = new Objective ($base_config);		}		$this->_data = array_merge (			$base_config->asArray (),			$this->_data		);		return $this;	}}