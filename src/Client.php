<?php

namespace LaravelBexio;

use Bexio\AbstractClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Client extends AbstractClient
{
    public function get(string $path, array $queryParams = [])
    {
        return $this->client()->get($this->getFullApiUrl($path), $queryParams)->object();
    }

    public function post(string $path, array $data = [], array $queryParams = [])
    {
        return $this->client()->post($this->getFullApiUrl($path, $queryParams), $data)->object();
    }

    public function put(string $path, array $data = [], array $queryParams = [])
    {
        return $this->client()->put($this->getFullApiUrl($path, $queryParams), $data)->object();
    }

    public function delete(string $path, array $data = [], array $queryParams = [])
    {
        return $this->client()->delete($this->getFullApiUrl($path, $queryParams), $data)->object();
    }

    public function patch(string $path, array $data = [], array $queryParams = [])
    {
        return $this->client()->patch($this->getFullApiUrl($path, $queryParams), $data)->object();
    }

    /**
     * @return PendingRequest
     */
    public function client()
    {
        return Http::withOptions([
            'allow_redirects' => false,
        ])->withToken($this->getAccessToken());
    }
}
