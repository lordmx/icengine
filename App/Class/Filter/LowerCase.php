<?php

namespace Ice;

class Filter_LowerCase
{

	public function filter ($data)
	{
		return mb_strtolower ($data);
	}

}