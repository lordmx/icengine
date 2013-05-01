<?php

/**
 * Транспорт данных
 *
 * @author goorus, morph
 */
class Data_Transport implements ArrayAccess
{
    /**
     * Входящие фильтры (для метода receive)
     * 
     * @var array
     * @Generator
     */
    protected $inputFilters = array();
    
    /**
     * Выходящие фильтры
     * 
     * @var array
     * @Generator
     */
    protected $outputFilters = array();
    
    /**
     * Поставщики данных.
     *
     * @var array <Data_Provider_Abstract>
     */
	protected $providers = array();

	/**
	 * Стек начатых транзакций
	 *
     * @var array
	 */
	protected $transactions = array();

    /**
     * Добавить провайдер
     *
     * @param Data_Provider_Abstract $provider
     * @return Data_Transport
     */
	public function appendProvider(Data_Provider_Abstract $provider)
	{
		$this->providers[] = $provider;
		return $this;
	}

    /**
     * Применить фильтры
     * 
     * @param array $filters
     * @param mixed $data
     * @return mixed
     */
    public function applyFilters($filters, $data)
    {
        foreach ($filters as $filter) {
            $data = $filter->filter($data);
        }
        return $data;
    }
    
	/**
	 * Начинает новую транзакцию
     *
	 * @return Data_Transport_Transaction
	 * 		Созданная транзакция
	 */
	public function beginTransaction()
	{
	    $transaction = new Data_Transport_Transaction($this);
	    $this->transactions[] = $transaction;
	    return $transaction;
	}

	/**
     * Получить текущую транзакцию
     *
	 * @return Data_Transport_Transaction
	 * 		Текущая транзакция
	 */
	public function currentTransaction()
	{
	    return end($this->transactions);
	}

	/**
	 * Заканчивает текущую транзакцию
     *
	 * @return Data_Transport_Transaction
	 * 		Законченная транзакция
	 */
	public function endTransaction()
	{
	    return array_pop($this->transactions);
	}

	/**
	 * Получить провайдер по индексу
     *
	 * @param integer $index
	 * @return Data_Provider_Abstract
	 */
	public function getProvider($index)
	{
		return isset($this->providers[$index]) 
            ? $this->providers[$index] : null;
	}

    /**
     * Получить весь пул провайдеров
     *
     * @return array
     */
	public function getProviders()
	{
		return $this->providers;
	}
    
    /**
	 * Проверяет существование поля
     *
	 * @param string $offset Название поля
	 * @return boolean true если поле существует
	 */
	public function offsetExists($offset)
	{
		return $this->receive($offset);
	}

	/**
	 * @see Data_Transport::receive
	 */
	public function offsetGet($offset)
	{
        return $this->receive($offset);
	}

	/**
	 * @see Data_Transport::send
	 */
	public function offsetSet($offset, $value)
	{
		$this->send($offset, $value);
	}

	/**
	 * Исключение поля из модели
     *
	 * @param string $offset название поля
	 */
	public function offsetUnset($offset)
	{

	}
    
    /**
     * Получение данных.
     *
     * @param mixed $_
     * @return mixed
     */
	public function receive()
	{
		$keys = func_get_args();
		$results = array ();
		if ($this->transactions) {
			$buffer = end($this->transactions)->buffer();
			foreach ($keys as $key) {
				$data = null;
				$chunk = isset($buffer[$key]) ? $buffer[$key] : null;
                if ($this->inputFilters && !is_null($chunk)) {
                    $chunk = $this->applyFilters($this->inputFilters, $chunk);
                }
				$results[] = $chunk;
			}
		} else {
            $jcount = count($this->providers);
			for ($i = 0, $icount = count($keys); $i < $icount; $i++) {
				$data = null;
				for ($j = 0; $j < $jcount; ++$j) {
					$provider = $this->providers[$j];
					$chunk = $provider->get($keys[$i]);
                    if (!is_null($chunk)) {
                        $data = $this->inputFilters 
                            ? $this->applyFilters($this->inputFilters, $chunk)
                            : $chunk;
                    }
				}
				$results[] = $data;
			}
		}
		return count($results) == 1 ? $results[0] : $results;
	}

	/**
	 * Получает все значения из всех провайдеров.
	 * Не рекомендуется использовать.
     *
     * @param boolean $raw
	 * @return array Массив пар (ключ => значение)
	 */
	public function receiveAll($raw = false)
	{
		if ($this->transactions) {
			return end($this->transactions)->buffer();
		}
		$result = array ();
		foreach ($this->providers as $provider){
			$result = array_merge($result, $provider->getAll());
		}
        if ($this->inputFilters && !$raw) {
            foreach ($result as &$value) {
                $value = $this->applyFilters($this->inputFilters, $value);
            }
        }
		return $result;
	}

    /**
     * Получить с транспортов данные отфильтрованные по входному массиву
     *
     * @param array $array
     * @return array
     */
    public function receiveAssoc($array)
    {
        if (!$array) {
            return;
        }
        $keyValues = $this->receiveAll();
        $result = array();
        foreach ($array as $keyName) {
            if (isset($keyValues[$keyName])) {
                $result[$keyName] = $keyValues[$keyName];
            }
        }
        return $result;
    }

	/**
	 * Очистка данных всех провайдеров и сброс транзаций.
	 *
     * @return Data_Transport
	 */
	public function reset ()
	{
		$this->transactions = array ();
		for ($i = 0, $count = sizeof($this->providers); $i < $count; ++$i) {
			$this->providers[$i]->clear();
		}
		return $this;
	}

    /**
     * Отправить данные в транспорт или в буффер транзации, если она начата
     *
     * @param string|array $key
     * @param mixed $data
     * @return Data_Transport
     */
	public function send($key, $data = null, $providerIndex = null)
	{
		if ($this->transactions) {
            if ($this->outputFilters) {
                foreach ($data as &$value) {
                    $value = $this->applyFilters($this->outputFilters, $value);
                }
            }
			$this->currentTransaction()->send($key, $data);
		} else {
            $args = func_get_args();
            if (count($args) == 2 && is_array($key) && is_numeric($data)) {
                $providerIndex = $data;
                foreach ($key as $currentKey => $currentValue) {
                    $this->sendForce(
                        $currentKey, $currentValue, $providerIndex
                    );
                }
            } else {
                $this->sendForce($key, $data, $providerIndex);
            }
		}
		return $this;
	}

	/**
	 * Отправить данные в транспорт
     *
	 * @param string|array $key
	 * @param mixed $data
	 * @return Data_Transport
	 */
	public function sendForce($key, $data, $providerIndex)
	{
		if (!is_array($key)) {
			$key = array($key => $data);
		}
        $count = $count = sizeof($this->providers);
		foreach ($key as $currentKey => $currentValue) {
            if (!is_null($currentValue)) {
                if ($this->outputFilters) {
                    $currentValue = $this->applyFilters(
                        $this->outputFilters, $currentValue
                    );
                }
                if ($providerIndex) {
                    $this->providers[$providerIndex]->set(
                        $currentKey, $currentValue
                    );
                } else {
                    for ($i = 0; $i < $count; $i++){
                        $this->providers[$i]->set($currentKey, $currentValue);
                    }
                }
            }
		}
		return $this;
	}

    /**
     * Изменить пул провайдеров
     *
     * @param array|Data_Provider_Abstract $providers
     * @return Data_Transport
     */
	public function setProviders($providers)
	{
		$this->providers = $providers;
		return $this;
	}
    
    /**
     * Getter for "inputFilters"
     *
     * @return array
     */
    public function getInputFilters()
    {
        return $this->inputFilters;
    }
        
    /**
     * Setter for "inputFilters"
     *
     * @param array inputFilters
     */
    public function setInputFilters($inputFilters)
    {
        $this->inputFilters = $inputFilters;
    }
    
    
    /**
     * Getter for "outputFilters"
     *
     * @return array
     */
    public function getOutputFilters()
    {
        return $this->outputFilters;
    }
        
    /**
     * Setter for "outputFilters"
     *
     * @param array outputFilters
     */
    public function setOutputFilters($outputFilters)
    {
        $this->outputFilters = $outputFilters;
    }
    
}