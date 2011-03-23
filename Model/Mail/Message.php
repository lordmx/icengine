<?php
/**
 * 
 * @desc Сообщение
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Message extends Model
{
	
	/**
	 * @desc Создает новое сообщение.
	 * @param string $template_name Имя шаблона.
	 * @param string $to_email Адрес получателя.
	 * @param string $to_name Имя получателя.
	 * @param array $data Данные для шаблона.
	 * @param integer $to_user_id Если получатель - пользователь.
	 * @param integer $mail_provider_id Провайдер сообщений.
	 * @param array $mail_provider_params Параметры для провайдера.
	 * @return Mail_Message Созданное сообщение.
	 */
	public static function create ($template_name, $to_email, $to_name, 
		array $data = array (), $to_user_id = 0, $mail_provider_id = 0,
		array $mail_provider_params = array ())
	{
		Loader::load ('Mail_Template');
		$template = Mail_Template::byName ($template_name);
		
		$message = new Mail_Message (array (
			'Mail_Template__id'		=> $template->id,
			'toEmail'				=> $to_email,
			'toName'				=> $to_name,
		    'sendTries'				=> 0,
			'subject'				=> $template->subject ($data),
		    'time'					=> date ('Y-m-d H:i:s'),
			'body'					=> $template->body ($data),
			'toUserId'				=> (int) $to_user_id,
			'Mail_Provider__id'		=> $mail_provider_id,
			'params'				=> json_encode ($mail_provider_params)
		));
		
		return $message;
	}
	
	/**
	 * @desc Попытка отправки сообщения
	 * @return boolean
	 */
	public function send ()
	{
		$this->update (array (
			'sendDay'		=> Helper_Date::eraDayNum (),
			'sendTime'		=> date ('Y-m-d H:i:s'),
			'sendTries'	    => $this->sendTries + 1
		));
		
		$provider = $this->Mail_Provider__id ? 
			$this->Mail_Provider :
			null;
		
		if (!$provider)
		{
			Loader::load ('Mail_Provider_Mimemail');
			$provider = new Mail_Provider_Mimemail ();
		}
		
		try
		{
			$result = $provider->send (
				$this,
				(array) json_decode ($this->params, true)
    		);
    		
    		if ($result)
    		{
    			$this->update (array (
    				'sended'	=> 1
    			));
    		}
    		
    		return $result;
		}
		catch (Exception $e)
		{
		    Debug::logVar ($e, 'Sendmail error message');
		    return false;
		}
	}
	
}