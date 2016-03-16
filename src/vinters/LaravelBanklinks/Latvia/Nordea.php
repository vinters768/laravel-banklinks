<?php namespace vinters\LaravelBanklinks\Latvia;

use vinters\LaravelBanklinks\Banklink\iShop;

class Nordea extends iShop
{

	protected $configName = 'latvia.nordea';

	protected $requestUrl = 'https://netbank.nordea.com/pnbepay/epayp.jsp';

}
