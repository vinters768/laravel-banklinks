<?php namespace vinters\LaravelBanklinks\Latvia;

use vinters\LaravelBanklinks\Banklink\iShop;

class Swedbank extends iShop
{

	protected $configName = 'latvia.swedbank';

	protected $requestUrl = 'https://ib.swedbank.lv/banklink';

}