<?php namespace vinters\LaravelBanklinks\Banklink;

class Solo extends Banklink
{

	protected $algorithm = 'md5';

	protected $signatureField = 'MAC';

	protected $signatureReturnedField = 'RETURN_MAC';

	protected $version = '0003';

	protected $keyvers = '0001';

	protected $language = 4;

	protected $currency = 'EUR';

	protected $date = 'EXPRESS';

	protected $orderIdField = 'RETURN_REF';

	protected function getServiceId($type)
	{
		switch ($type) {
			case self::PAYMENT_REQUEST:
				return '1001';
			case self::PAYMENT_SUCCESS:
				return '1101';
			case self::PAYMENT_CANCEL:
				return '1901';
			case self::PAYMENT_RETURN:
				return '1201';
		}

		throw new \LogicException(sprintf('Invalid service type: %s', $type));
	}

	public function getPaymentRequestData($orderId, $sum, $description, $refNr = null)
	{

		$requestData = array(
			'VERSION'  => $this->version,
			'STAMP'    => $orderId,
			'RCV_ID'   => $this->sellerId,
			'RCV_NAME' => $this->sellerName,
			'LANGUAGE' => $this->language,
			'AMOUNT'   => $sum,
			'REF'      => $refNr,
			'DATE'     => $this->date,
			'MSG'      => $description,
			'MAC'      => '',
			'RETURN'   => $this->callbackUrl,
			'CANCEL'   => $this->cancelUrl,
			'REJECT'   => $this->cancelUrl,
			'CONFIRM'  => 'YES',
			'KEYVERS'  => $this->keyvers,
			'CUR'      => $this->currency
		);

		return $requestData;
	}

	public function getPaymentRequestFields()
	{
		return array(
			'VERSION',
			'STAMP',
			'RCV_ID',
			'AMOUNT',
			'REF',
			'DATE',
			'CUR',
		);
	}

	public function getPaymentSuccessFields()
	{
		return array(
			'RETURN_VERSION',
			'RETURN_STAMP',
			'RETURN_REF',
			'RETURN_PAID'
		);
	}

	public function getPaymentReturnFields()
	{
		return $this->getPaymentSuccessFields();
	}

	public function getPaymentCancelFields()
	{
		return $this->getPaymentSuccessFields();
	}

	protected function getRequestSignature($data, $fields)
	{
		$hash = '';

		foreach ($fields as $fieldName)
		{
			$content = !empty( $data[$fieldName] ) ? $data[$fieldName] : '';

			$hash .= $content . '&';
		}

		$hash .= $this->privateKey . '&';

		return strtoupper(hash($this->algorithm, $hash));
	}

	protected function validateSignature( $data, $fields )
	{

		$fields = $this->getPaymentSuccessFields();

		$hash = $this->getRequestSignature( $data, $fields );

		return $hash == $data['RETURN_MAC'];

	}

}