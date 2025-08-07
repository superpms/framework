<?php

namespace pms\helper\hrb;

use pms\helper\fetch\Client;
use pms\helper\fetch\Response;

abstract class HRBClient implements HRBClientInterface
{
    protected array $arguments = [];
    protected array $headers = [];

    protected array $query = [];

    protected function before(HRBRequestInterface $request): HRBRequestInterface
    {
        return $request;
    }

    protected function after(Response $client):mixed
    {
        return $client->getJsonBody();
    }

    public function execute(HRBRequestInterface $request): mixed
    {
        $method = $request->getMethod();

        $request->setHeaders([...$this->headers, ...$request->getHeaders()])
            ->setArguments([...$this->arguments, ...$request->getArguments()])
            ->setQuery([...$this->query, ...$request->getQuery()]);
        $request = $this->before($request);

        $uri = $this->buildUrlWithQuery($request->getUri(), $request->getQuery());

        $httpClient = new Client();

        $res = $httpClient->setUrl($uri)
            ->addHeader($request->getHeaders())
            ->setMethod($method)
            ->setData($request->getArguments())->execute();
        $res = $this->after($res);

        return $request->callback($res);

    }

    private function buildUrlWithQuery(string $url, array $query = []): string
    {
        if (empty($query)) {
            return $url;
        }
        if (!str_contains($url, '?')) {
            $url .= '?';
        } else {
            $url .= '&';
        }
        return $url . http_build_query($query);
    }

}