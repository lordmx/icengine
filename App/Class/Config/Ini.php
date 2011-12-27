<?phpnamespace Ice;if (!class_exists (__NAMESPACE__ . '\\Config_Array')){	include __DIR__ . '/Array.php';}/** * * @desc Конфиг из ini файла. * @author Юрий Шведов, Илья Колесников * @package Ice * */class Config_Ini extends Config_Array{	/**	 *	 * @param string $path	 * 		Путь до ini файла	 */	public function __construct ($path = null)	{		if ($path)		{			$ini = parse_ini_file($path);			parent::__construct ($ini);		}    }}