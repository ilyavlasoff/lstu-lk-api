<?php

namespace App\Service;

use App\Exception\BadExtResponseException;
use App\Exception\ExternalQueryException;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractQueryService
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(HttpClientInterface $httpClient, SerializerInterface $serializer)
    {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
    }

    /**
     * @param string $urlBase
     * @param string $urlPath
     * @param string $method
     * @param string $protocol
     * @param int $correctStatusCode
     * @param array $queryParams
     * @param array $headers
     * @param array $json
     * @param string $body
     * @param string $jwt
     * @param bool $jmsDeserialize
     * @param string $jmsDeserializeType
     * @param bool $jsonDeserialize
     * @return mixed|string
     */
    public function makeQuery(
        string $urlBase,
        string $urlPath,
        string $method,
        string $protocol = 'http',
        $correctStatusCode = 200,
        array $queryParams = [],
        array $headers = [],
        array $json = [],
        $body = '',
        string $jwt = '',
        bool $jmsDeserialize = false,
        string $jmsDeserializeType = '',
        bool $jsonDeserialize = false
    ) {
        $requestParams = [];

        if($queryParams) {
            $requestParams['query'] = $queryParams;
        }

        if($headers) {
            $requestParams['headers'] = $headers;
        }

        if($json) {
            $requestParams['json'] = $json;
        }

        if($body) {
            $requestParams['body'] = $body;
        }

        if($jwt) {
            $requestParams['headers']['Authorization'] = "Bearer $jwt";
        }

        try {
            $response = $this->httpClient->request(
                $method,
                "$protocol://$urlBase/$urlPath",
                $requestParams
            );

            if(is_integer($correctStatusCode)) {
                if($response->getStatusCode() !== $correctStatusCode) {
                    throw new BadExtResponseException($response->getStatusCode(), $response->getContent(false));
                }
            }
            else if(is_array($correctStatusCode)) {
                if(!in_array($response->getStatusCode(), $correctStatusCode)) {
                    throw new BadExtResponseException($response->getStatusCode(), $response->getContent(false));
                }
            }

            if($jmsDeserialize) {
                return $this->serializer->deserialize($response->getContent(false), $jmsDeserializeType, 'json');
            }

            if($jsonDeserialize) {
                return json_decode($response->getContent(false), true);
            }

            return $response->getContent();

        } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
            throw new ExternalQueryException($e);
        }

    }
}