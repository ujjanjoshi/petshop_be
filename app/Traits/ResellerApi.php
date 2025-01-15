<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Cache;

use App\Traits\LogApi;

/**
 * PETLINK  ResellerAPI
 */
trait ResellerApi
{
    use LogApi;

    /**
     * @var GuzzleHttp\Client
     */
    protected $gzClient;

    /**
     * @var base_url
     */
    private   $options;

    protected $errMessage = '';

    /**
     *
     */
    public function petInit()
    {
        $url = config('app.peturl');
        if (substr($url, -1) != '/') {
            $url .= '/';
        }

        $this->gzClient     = new Client([ 'base_uri'  => $url ]);
        $this->options['headers'] = [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'RESELLERID'    => config('app.petid'),
            'SECURITYTOKEN' => config('app.petapikey'),
        ];
    }
    /**
     * Make request to the petlink server
     *
     * @return mixed json or false
     */
    public function petRequest(string $method, string $uri, array $options = [])
    {
        $this->errMessage = '';
        $options = array_merge($this->options, $options);
       
        $this->debug('Request: '. $method .' '. $uri ." options=". json_encode($options, JSON_PRETTY_PRINT));
        try {
            $response = $this->gzClient->request($method, $uri, $options)->getBody();
        } catch (BadResponseException $e) {
            $errMessage = $e->getResponse()->getBody()->getContents();
            $this->error($errMessage);

            $err = json_decode($errMessage);
            if ($err && !empty($err->error)) {
                $errMessage  = "Error ". $err->error->code ?? '';
                $errMessage .= ": ". $err->error->message ?? '';
            }
            $this->errMessage = $errMessage;
            return false;
        }
        $retval   = json_decode($response) ?? $response;
        $this->debug('Response: '. json_encode($retval, JSON_PRETTY_PRINT));

        return $retval;
    }
    /**
     * Error message on the return data
     *
     * @param   object  $json
     * @return  string  $errMsg
     */
    public function petErrorMessage($json = null)
    {
        if ($json == null) {
            return $this->errMessage;
        }
        if (empty($json->error)) {
            return "Unknown Error: ". json_encode($json);
        }
        $errMsg  = "Error ";
        $errMsg .= $json->error->code ?? '';
        $errMsg .= ": ";
        $errMsg .= $json->error->message ?? '';

        return $errMsg;
    }
}
