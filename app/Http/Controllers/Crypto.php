<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Crypto extends Controller
{
    protected $baseURL;
    protected $apiKey;

    public function __construct(){

        $this->baseURL = 'https://pro-api.coinmarketcap.com';
        $this->apiKey = env("COINMARKETCAP_API");
    }

    /*
     * returns all cryptocurrencies
     */
    public function all(){

        $path = "/v1/cryptocurrency/listings/latest";
        $requestUrl = $this->baseURL . $path; // create the request URL

        $response = $this->getCurlResponse($requestUrl);

        //response is already a json response so no need to do
        //json($response)
        return response($response);
    }


    protected function getCurlResponse($requestUrl){

        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: ' . $this->apiKey
        ];


        $curl = curl_init(); // Get cURL resource
        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $requestUrl,            // set the request URL
            CURLOPT_HTTPHEADER => $headers,     // set the headers
            CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
        ));

        $response = curl_exec($curl); // Send the request, save the response
        curl_close($curl); // Close request

        return $response;
    }

    /*
     * shows a currency's meta
     */
    public function meta($currencyId){

        $path = "/v2/cryptocurrency/info?id=" . $currencyId;
        $requestUrl = $this->baseURL . $path; // create the request URL

        $response = $this->getCurlResponse($requestUrl);

        //response is already a json response so no need to do
        //json($response)
        return $response;
    }
}
