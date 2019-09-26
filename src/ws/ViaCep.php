<?php

namespace Wead\ZipCode\WS;

use GuzzleHttp\Client;
use Wead\ZipCode\Exceptions\ZipCodeNotFoundException;

class ViaCep
{
    private $endPoint = "https://viacep.com.br/ws/";
    private $apiKey = null;
    private $apiSecret = null;

    public function __construct($credential = [])
    {
        if (is_array($credential)) {
            if (isset($credential['apiKey']) && isset($credential['apiSecret'])) {
                $this->apiKey = $credential['apiKey'];
                $this->apiSecret = $credential['apiSecret'];
            }
        }
    }

    public function getAddressFromZipcode($zipCode)
    {
        $zipCode = preg_replace('/[^0-9]/im', '', $zipCode);

        $headers = [
            "Accept" => "application/json",
        ];

        $client = new Client(['base_uri' => "{$this->endPoint}/{$zipCode}/"]);

        try {
            $response = $client->get(
                'json',
                [
                'headers' => $headers,
                'connect_timeout' => 5, // seconds
                'debug' => false,
                ]
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw new ZipCodeNotFoundException("ViaCep request error to find zipcode: {$zipCode}");
        }

        $response = $response->getBody()->getContents();
        $response = json_decode($response);

        return $this->normalizeResponse((array)$response);
    }

    private function normalizeResponse($address)
    {
        if (sizeof($address) > 0 && !isset($address["erro"])) {
            return [
                "status" => true,
                "address" => $address["logradouro"],
                "district" => $address["bairro"],
                "city" => $address["localidade"],
                "state" => $address["uf"],
                "api" => "ViaCep"
            ];
        } else {
            return [
                "status" => false,
                "address" => null,
                "district" => null,
                "city" => null,
                "state" => null,
                "api" => "ViaCep"
            ];
        }
    }
}
