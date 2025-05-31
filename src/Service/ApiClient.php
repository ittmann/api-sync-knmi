<?php

namespace App\Service;

use App\Model\DataSet;
use Monolog\Logger;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiClient
{
    public function __construct(
        private readonly HttpClientInterface $client
    ) {
    }

    /**
     * @param string $url
     * @param array<string, array<string, mixed>> $options
     * @return ResponseInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function fetch(string $url, array $options): ResponseInterface
    {
        return $this->client->request('POST', $url, $options);
    }

    /**
     * @param DataSet $syncEnum
     * @param array<string, array<string, mixed>>|null $options
     * @return ResponseInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getApiResponse(DataSet $syncEnum, array|null $options): ResponseInterface
    {
        return $this->fetch($syncEnum->getApiUrl(), $options ?? []);
    }

    /**
     * @param string $stations 'ALL' or station numbers separated by ':'
     */
    public function getWaarnemingen(DataSet $syncEnum, string $start, string $end, string $stations): ResponseInterface
    {
        $body =
        array_merge([
            'stns' => $stations,
            'fmt' => 'json',
            ], [
            'start' => $start,
            'end' => $end,
        ]);

        return $this->getApiResponse($syncEnum, ['body' => $body]);
    }
}
