<?php
namespace Solunes\Sales;

use Illuminate\Support\Facades\Facade;

class SalesFacade extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'sales';
	}
}