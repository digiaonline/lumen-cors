<?php namespace Nord\Lumen\Cors;

use Illuminate\Support\Facades\Facade;

class CorsFacade extends Facade
{

	/**
	 * @inheritdoc
	 */
	protected static function getFacadeAccessor()
	{
		return 'Nord\Lumen\Cors\CorsService';
	}
}
