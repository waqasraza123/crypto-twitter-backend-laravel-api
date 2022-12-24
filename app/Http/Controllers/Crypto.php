<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Crypto extends Controller
{
    public $baseURL = 'https://pro-api.coinmarketcap.com';

    /*
     * returns all cryptocurrencies
     */
    public function all(){

        $path = "/v1/cryptocurrency/listings/latest";
        $apiKey = env("COINMARKETCAP_API");

        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: ' . $apiKey
        ];

        $request = $this->baseURL . $path; // create the request URL


        $curl = curl_init(); // Get cURL resource
        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $request,            // set the request URL
            CURLOPT_HTTPHEADER => $headers,     // set the headers
            CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
        ));

        $response = curl_exec($curl); // Send the request, save the response
        curl_close($curl); // Close request


        //response is already a json response so no need to do
        //json($response)
        return $response;
    }
}
