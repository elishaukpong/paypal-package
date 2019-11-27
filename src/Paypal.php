<?php

/**
 *
 *  This file is part of the Paypal Laravel package.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @version         1.0
 *
 *  @author          Elisha Ukpong
 *  @license         MIT Licence
 *  @copyright       (c) Elisha Ukpong <ishukpong418@gmail.com>
 *
 *  @link            https://github.com/drumzminister
 *
 */

namespace Drumzminister\Paypal;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

/**
 * Class Paypal
 * @package Drumzminister\Paypal
 */

class Paypal
{
    /**
     * @var ApiContext
     */
    private $apiContext;

    /**
     * @var object
     */
    private $config;

    /**
     * @var string
     */
    private $approvalLink;

    /**
     * @var string|null
     */
    private $authorizationURL;
    /**
     * @var bool|string
     */
    private $paymentResult;

    /**
     * PayPal constructor.
     */
    function __construct()
    {
        if (!session_id()) {
            session_start();
        }

        $this->config = (object)[
            'return_url' => config()->get('paypal.callback_url'),
            'cancel_url' => config()->get('paypal.cancel_url'),
            'app_name' => config()->get('app.name'),
            'client_id' => config()->get('paypal.client_id'),
            'secret' => config()->get('paypal.secret')
        ];

        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->config->client_id,
                $this->config->secret
            )
        );
        if (!\Storage::exists('payments/Paypal.log')) {
            \Storage::put('payments/Paypal.log', '');
        }

        $this->apiContext->setConfig(
            array(
                'log.LogEnabled' => true,
                'log.FileName' => storage_path('app/payments/Paypal.log'),
                'log.LogLevel' => 'DEBUG'
            )
        );

    }

    function getAPIContext()
    {
        return $this->apiContext;
    }

    /**
     * @param $amount
     * @return null|string
     */
    function charge($amount)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amountObject = new Amount();
        $amountObject->setTotal($amount);
        $amountObject->setCurrency('USD');

        $item = new Item();
        $item->setName($this->config->app_name . " credit charge")
            ->setPrice($amount)
            ->setCurrency('USD')
            ->setQuantity(1);

        $itemList = new ItemList();
        $itemList->setItems(array($item));

        $transaction = new Transaction();
        $transaction->setAmount($amountObject)
            ->setDescription("payment for cart items")
            ->setItemList($itemList);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->config->return_url)->setCancelUrl($this->config->cancel_url);

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions(array($transaction))
            ->setRedirectUrls($redirectUrls);
        $this->authorizationURL = $payment->create($this->apiContext)->getApprovalLink();
        return $this;
    }

    public function redirectToPaypal()
    {
        return redirect()->to($this->authorizationURL);
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {

        try {
            if(\Storage::exists('payments/access-token.txt'))
            {
                $accessTokenJson = \Storage::get('payments/access-token.txt');
                $accessTokenArr = json_decode($accessTokenJson, true);
                if ($accessTokenArr['current_time'] + $accessTokenArr['expires_in'] > time() - 60) {
                    return $accessTokenArr['access_token'];
                }
            }
        } catch (FileNotFoundException $e) {
            return redirect('payments.index')->with('error','PayPal token error, plz fix');
        }

        $accessTokenRequest = curl_init();

        curl_setopt($accessTokenRequest, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($accessTokenRequest, CURLOPT_HEADER, false);
        curl_setopt($accessTokenRequest, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($accessTokenRequest, CURLOPT_POST, true);
        curl_setopt($accessTokenRequest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($accessTokenRequest, CURLOPT_USERPWD, $this->config->client_id . ":" . $this->config->secret);
        curl_setopt($accessTokenRequest, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $accessTokenJson = curl_exec($accessTokenRequest);

        $accessTokenArr = json_decode($accessTokenJson, true);
        $accessTokenArr['current_time'] = time();

        $accessTokenJson = json_encode($accessTokenArr);

        \Storage::put('payments/access-token.txt', $accessTokenJson);

        return $accessTokenArr['access_token'];


    }

    public function getPaymentData()
    {
        return $this->executePayment()->extractPaymentData();
    }

    function executePayment()
    {
        $paymentID = request()->get('paymentId');
        $payerID = request()->get('PayerID');

        $aryData = array("payer_id" => $payerID);

        $data_string = json_encode($aryData);

        $header = array(
            "Content-Type: application/json",
            'Content-Length: ' . strlen($data_string),
            "Authorization: Bearer " . $this->getAccessToken()
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/payments/payment/' . $paymentID . '/execute/');

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($aryData));


        $this->paymentResult =  curl_exec($ch);

        return $this;
    }

    public function extractPaymentData()
    {
        return json_decode($this->paymentResult, true);
    }

}



