<?php

namespace Yadahan\Cardcom;

use GuzzleHttp;

class Cardcom
{
    /**
     * The base Cardcom Api URL.
     *
     * @var string
     */
    protected $url = 'https://secure.cardcom.co.il/';

    /**
     * The Cardcom terminal number.
     *
     * @var string
     */
    protected $terminal;

    /**
     * The Cardcom terminal username.
     *
     * @var string
     */
    protected $username;

    /**
     *  The Cardcom api name.
     *
     * @var string
     */
    protected $apiName;

    /**
     *  The Cardcom api password.
     *
     * @var string
     */
    protected $apiPassword;

    /**
     *  Credit card.
     *
     * @var string
     */
    protected $card;

    /**
     * Cardcom constructor.
     *
     * @param array $config
     */
    function __construct(array $config)
    {
        $this->config($config);
    }

    /**
     * Set the Cardcom terminal.
     *
     * @param  array  $config
     * @return $this
     */
    public function config(array $config)
    {
        $this->terminal = $config['terminal'];
        $this->username = $config['username'];
        $this->apiName = $config['api_name'] ?? '';
        $this->apiPassword = $config['api_password'] ?? '';

        return $this;
    }

    /**
     * Set the credit card.
     *
     * @param  string  $number
     * @param  string  $month
     * @param  string  $year
     * @param  string  $cvv
     * @return $this
     */
    public function card($number, $month, $year, $cvv = null, $identity = null)
    {
        $this->card['number'] = $number;
        $this->card['month'] = $month;
        $this->card['year'] = $year;
        $this->card['cvv'] = $cvv;
        $this->card['identity'] = $identity;

        return $this;
    }

    /**
     * Charge given amount.
     *
     * @param  int  $amount
     * @param  string  $currency
     * @return mixed
     */
    public function charge($amount, $currency = 'ILS')
    {
        $client = new GuzzleHttp\Client();

        if (is_array($this->card)) {
            $params = [
                'TerminalNumber'    => $this->terminal,
                'username'          => $this->username,
                'sum'               => $amount,
                'Cardnumber'        => $this->card['number'],
                'cardvaliditymonth' => $this->card['month'],
                'cardvalidityyear'  => $this->card['year'],
                'Cvv'               => $this->card['cvv'],
                'Identitynumber'    => $this->card['identity'],
            ];
        }

        $response = $client->request('POST', $this->url . 'BillGoldPost2.aspx', [
            'form_params' => $params,
        ]);

        return $this->chargeRsponse($response->getBody());
    }

    /**
     * Charge credit card response.
     *
     * @param  string  $response
     * @return mixed
     */
    public function chargeRsponse($response)
    {
        $array = explode(';', $response);

        if (!is_array($array)) {
            return $response;
        }

        $data = [
            'code' => $array[0],
            'message' => $array[2],
            'transaction' => $array[1]
        ];

        return $data;
    }
}
