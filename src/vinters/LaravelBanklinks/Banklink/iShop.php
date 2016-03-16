<?php namespace vinters\LaravelBanklinks\Banklink;

abstract class iShop extends Banklink
{

    protected $requestEncoding = 'UTF-8';

    protected $version = '008';

    protected $language = 'EST';

    protected $currency = 'EUR';

    protected $signatureField = 'VK_MAC';

    protected $signatureReturnedField = 'VK_MAC';

    protected $orderIdField = 'VK_REF';

    protected function getServiceId($type)
    {
        switch ($type) {
            case self::PAYMENT_REQUEST:
                return '1012';
            case self::PAYMENT_SUCCESS:
                return '1111';
            case self::PAYMENT_CANCEL:
                return '1911';
        }

        throw new \LogicException(sprintf('Invalid service type: %s', $type));
    }

    protected function getAdditionalFields()
    {
        return array(
            'VK_ENCODING' => $this->requestEncoding
        );
    }

    public function getPaymentRequestData($orderId, $sum, $description, $refNr = null)
    {

        $requestData = array(
            'VK_SERVICE' => $this->getServiceId(self::PAYMENT_REQUEST),
            'VK_VERSION' => $this->version,
            'VK_SND_ID'  => $this->sellerId,
            'VK_STAMP'   => $orderId,
            'VK_AMOUNT'  => $sum,
            'VK_CURR'    => $this->currency,
            'VK_ACC'     => $this->sellerAccountNumber,
            'VK_NAME'    => $this->sellerName,
            'VK_REF'     => $refNr,
            'VK_MSG'     => $description,
            'VK_RETURN'  => $this->callbackUrl,
            'VK_CANCEL'  => $this->cancelUrl,
            'VK_LANG'    => $this->language,
            'VK_DATETIME'=> \Carbon\Carbon::now()->toIso8601String()
        );

        return $requestData;
    }

    public function getPaymentRequestFields()
    {
        return array(
            'VK_SERVICE',
            'VK_VERSION',
            'VK_SND_ID',
            'VK_STAMP',
            'VK_AMOUNT',
            'VK_CURR',
            'VK_REF',
            'VK_MSG',
            'VK_RETURN',
            'VK_CANCEL',
            'VK_DATETIME'
        );
    }

    public function getPaymentSuccessFields()
    {
        return array(
            'VK_SERVICE',
            'VK_VERSION',
            'VK_SND_ID',
            'VK_REC_ID',
            'VK_STAMP',
            'VK_T_NO',
            'VK_AMOUNT',
            'VK_CURR',
            'VK_REC_ACC',
            'VK_REC_NAME',
            'VK_SND_ACC',
            'VK_SND_NAME',
            'VK_REF',
            'VK_MSG',
            'VK_T_DATETIME'
        );
    }

    public function getPaymentCancelFields()
    {
        return array(
            'VK_SERVICE',
            'VK_VERSION',
            'VK_SND_ID',
            'VK_REC_ID',
            'VK_STAMP',
            'VK_REF',
            'VK_MSG'
        );
    }

    protected function validateSignature($data, $fields)
    {
        $hash = $this->generateHash($data, $fields);

        $key = openssl_pkey_get_public(file_get_contents($this->publicKey));

        return (openssl_verify( $hash , base64_decode($data[ $this->signatureReturnedField ]), $key) === 1);
    }

    protected function getRequestSignature($data, $fields)
    {
        $hash = $this->generateHash($data, $fields);

        $keyId = openssl_pkey_get_private(file_get_contents($this->privateKey), $this->passphrase);

        openssl_sign($hash, $signature, $keyId);
        openssl_free_key($keyId);

        $result = base64_encode($signature);

        return $result;
    }

    protected function generateHash(array $data, $fields)
    {
        $hash = '';

        foreach ($fields as $fieldName)
        {

            // if ( empty( $data[ $fieldName ]))
            // {
            //     continue;
            // }

            $content = $data[ $fieldName ];

            $hash .= sprintf("%03d", mb_strlen($content)) . $content;
        }

        return $hash;
    }

    protected function getEncodingField()
    {
        return 'VK_ENCODING';
    }

    public function isCancelResponse( $data )
    {
        return $this->isValidResponse( $data, $this->getPaymentCancelFields() ) && $data['VK_SERVICE'] == $this->getServiceId( self::PAYMENT_CANCEL );
    }

    public function isPaidResponse( $data )
    {
        return $this->isValidResponse( $data, $this->getPaymentSuccessFields() ) && $data['VK_SERVICE'] == $this->getServiceId( self::PAYMENT_SUCCESS );
    }

}