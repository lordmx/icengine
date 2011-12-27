<?php

namespace Ice;

/**
 *
 * @desc Контроллер для компновки ресурсов представления.
 * Предназначен для сбора js, css файлов в один.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Controller_View_Resource extends Controller_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
	public function index ()
	{
		$config = $this->config ();
		list (
			$type,
			$params,
			$name_filter
		) = $this->_input->receive (
			'type',
			'params',
			'name'
		);

		$vars = array (
			'{$ice}' => rtrim (Core::path (), '\\/'),
			'{$root}' => rtrim (Core::root (), '\\/'),
			'{$app}' => Core::bootstrap ()->getAppDir ()
		);

		if ($params)
		{
			foreach ($params as $k => $v)
			{
				$vars ['{$' . $k . '}'] = $v;
			}
		}

		$reses = array ();

		Loader::load ('View_Resource_Manager');

		foreach ($config->targets as $name => $target)
		{
			if (
				($type && $type != $target->type) ||
				($name_filter && $name_filter != $name)
			)
			{
				continue;
			}

			$res = array ();

			foreach ($target->sources as $source)
			{
				if (is_string ($source))
				{
					$src_dir = Core::root ();
					$src_files = array ($source);
				}
				else
				{
					$src_dir = strtr ($source->dir, $vars);

					$src_files = is_scalar ($source->file)
						? array ($source->file)
						: $source->file->__toArray ();
				}

				foreach ($src_files as $src_file)
				{
					$src_file = strtr ($src_file, $vars);

					$res = array_merge (
						$res,
						View_Resource_Manager::patternLoad (
							$src_dir,
							$src_file,
							$target->type
						)
					);
				}
			}

			$packer = View_Resource_Manager::packer ($target->type);
			$packer_config = $target->packer_config;

			if ($packer_config && $packer_config->state_file)
			{
				$packer_config->state_file = strtr (
					$packer_config->state_file,
					$vars
				);
			}

			$dst_file = strtr ($target->file, $vars);
			$packer->pushConfig ($packer_config);
			$packer->pack ($res, $dst_file, $packer_config);
			$packer->popConfig ();

			$reses [$name] = array (
				'type'	=> $target->type,
				'url'	=> strtr ($target->url, $vars),
				'ts'	=> $packer->cacheTimestamp ()
			);
		}

		$this->_output->send ('reses', $reses);
	}

}