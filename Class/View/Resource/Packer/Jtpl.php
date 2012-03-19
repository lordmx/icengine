<?php
/**
 * 
 * @desc Упаковщик Jtpl ресурсов представления.
 * @author Юрий
 * @package IcEngine
 *
 */
Loader::load ('View_Resource_Packer_Abstract');

class View_Resource_Packer_Jtpl extends View_Resource_Packer_Abstract
{
	/**
	 * @desc Класс Packer'a
	 * @var string
	 */
	const PACKER = 'class.JavaScriptPacker.php';
	
	public function __construct ()
	{
		Loader::requireOnce (self::PACKER, 'includes');
	}
	
	public function packOne (View_Resource $resource)
	{
		if (
			$this->config ()->item_prefix &&
			isset ($resource->filePath)
		)
		{
			$result = strtr (
				$this->config ()->item_prefix,
				array (
					'{$source}' => $resource->filePath,
					'{$src}'	=> $resource->localPath
				)
			);
		}
		else
		{
			$result = '';
		}
		
		$content = $resource->content ();
		$content = str_replace (
			array ('\\',	'"',	"\r\n",			"\n",			"\r"),
			array ('\\\\',	'\\"',	'"+"\\r\\n"+"',	'"+"\\n"+"',	'"+"\\r"+"'),
			$content
		);
		
		$content = 
			'View_Render.templates[\'' . $resource->localPath . '\']="' . $content . '";';
		
		if (
			isset ($this->_currentResource->nopack) &&
			$this->_currentResource->nopack
		)
		{
			$result .= $content . "\n";
		}
	    else
	    {
			$packer = new JavaScriptPacker ($content, 0);
			$result .= $packer->pack ();
	    }
	    
		return $result . $this->config ()->item_postfix;
	}
	
}