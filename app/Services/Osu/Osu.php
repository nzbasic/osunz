<?php

namespace App\Services\Osu;

use App\Contracts\ApiClient;
use App\Services\Osu\Builders\BeatmapBuilder;
use App\Services\Osu\Builders\BeatmapSetBuilder;
use App\Services\Osu\Traits\Auth;
use App\Traits\HasEndpoints;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * The InfoAgent API client
 *
 * Swagger UI: https://api.dev.infoagent.com.au/open-api/v1/ivds/specs/ui/
 * Open API spec in JSON format: https://api.dev.infoagent.com.au/open-api/v1/ivds/nz/specs/json
 */
class Osu implements ApiClient
{
    use HasEndpoints {
        call as parentCall;
    }

    use Auth;

    private PendingRequest $client;

    private ?string $accessToken;

    public function __construct(private ?string $apiUrl = null, private ?string $clientId = null, private ?string $clientSecret = null)
    {
        $this->apiUrl ??= config('services.osu.api_url');
        $this->clientId ??= config('services.osu.client_id');
        $this->clientSecret ??= config('services.osu.client_secret');

        $this->client = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
    }

    public function call(string $method, string $endpoint, array $data = []): mixed
    {
        $this->accessToken = $this->getAccessToken();

        return $this->parentCall($method, $endpoint, $data);
    }

    public function beatmap(int|null $id = null): BeatmapBuilder
    {
        return new BeatmapBuilder($this, $id);
    }

    public function beatmapset(int|null $id = null): BeatmapSetBuilder
    {
        return new BeatmapSetBuilder($this, $id);
    }
}
