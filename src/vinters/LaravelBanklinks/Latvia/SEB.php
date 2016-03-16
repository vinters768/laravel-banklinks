<?php namespace vinters\LaravelBanklinks\Latvia;

use vinters\LaravelBanklinks\Banklink\iShop;

class SEB extends iShop
{

	protected $configName = 'latvia.seb';

	protected $requestUrl = 'https://www.seb.ee/cgi-bin/unet3.sh/un3min.r';

}