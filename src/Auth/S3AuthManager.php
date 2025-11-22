<?php

namespace Tigusigalpa\YandexCloudS3\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Tigusigalpa\YandexCloudS3\Exceptions\AuthenticationException;

/**
 * S3-compatible authentication manager for Yandex Cloud
 */
class S3AuthManager
{
    private const IAM_TOKEN_ENDPOINT = 'https://iam.api.cloud.yandex.net/iam/v1/tokens';

    private Client $httpClient;
    private string $oauthToken;
    private ?string $iamToken = null;
    private ?int $iamTokenExpiry = null;

    public function __construct(string $oauthToken, ?Client $httpClient = null)
    {
        if (empty($oauthToken)) {
            throw new AuthenticationException('OAuth token cannot be empty');
        }

        $this->oauthToken = $oauthToken;
        $this->httpClient = $httpClient ?? new Client();
    }

    /**
     * Get valid IAM token (refresh if necessary)
     * IAM tokens are valid for 12 hours
     *
     * @return string
     * @throws AuthenticationException
     */
    public function getValidIamToken(): string
    {
        // Check if token needs refresh (tokens valid for 12 hours)
        if ($this->iamToken === null || $this->iamTokenExpiry === null || time() >= $this->iamTokenExpiry) {
            $this->iamToken = $this->getIamToken();
            // 12 hours minus 5 minutes buffer
            $this->iamTokenExpiry = time() + (12 * 60 * 60) - 300;
        }

        return $this->iamToken;
    }

    /**
     * Get new IAM token from OAuth token
     *
     * @return string
     * @throws AuthenticationException
     */
    public function getIamToken(): string
    {
        try {
            $response = $this->httpClient->post(self::IAM_TOKEN_ENDPOINT, [
                'json' => [
                    'yandexPassportOauthToken' => $this->oauthToken,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['iamToken'])) {
                throw new AuthenticationException('Failed to get IAM token from response');
            }

            return $data['iamToken'];
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Error getting IAM token: ' . $e->getMessage());
        }
    }

    /**
     * Get OAuth token manager to access user and resource management
     *
     * @return OAuthTokenManager
     */
    public function getOAuthTokenManager(): OAuthTokenManager
    {
        return new OAuthTokenManager($this->oauthToken, $this->httpClient);
    }
}
