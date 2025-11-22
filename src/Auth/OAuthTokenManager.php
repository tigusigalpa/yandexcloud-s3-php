<?php

namespace Tigusigalpa\YandexCloudS3\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Tigusigalpa\YandexCloudS3\Exceptions\AuthenticationException;

/**
 * OAuth token manager for Yandex Cloud resources
 * Used for accessing user accounts and resource management
 */
class OAuthTokenManager
{
    private const USER_ACCOUNT_ENDPOINT = 'https://iam.api.cloud.yandex.net/iam/v1/yandexPassportUserAccounts:byLogin';
    private const USER_ACCOUNT_GET_ENDPOINT = 'https://iam.api.cloud.yandex.net/iam/v1/userAccounts';
    private const FOLDERS_ENDPOINT = 'https://resource-manager.api.cloud.yandex.net/resource-manager/v1/folders';
    private const CLOUDS_ENDPOINT = 'https://resource-manager.api.cloud.yandex.net/resource-manager/v1/clouds';

    private Client $httpClient;
    private string $oauthToken;

    public function __construct(string $oauthToken, ?Client $httpClient = null)
    {
        $this->oauthToken = $oauthToken;
        $this->httpClient = $httpClient ?? new Client();
    }

    /**
     * Get user account information by login
     *
     * @param  string  $login  Yandex Passport user login
     * @return array User account data including 'id' (Subject ID)
     * @throws AuthenticationException
     */
    public function getUserByLogin(string $login): array
    {
        $s3Auth = new S3AuthManager($this->oauthToken, $this->httpClient);
        $iamToken = $s3Auth->getValidIamToken();

        try {
            $response = $this->httpClient->get(self::USER_ACCOUNT_ENDPOINT, [
                'query' => [
                    'login' => $login,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $iamToken,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['id'])) {
                throw new AuthenticationException('User account response does not contain id field');
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Error getting user by login: ' . $e->getMessage());
        }
    }

    /**
     * Get user account information by UserAccountId
     *
     * @param  string  $userAccountId  User Account ID (Subject ID)
     * @return array User account data
     * @throws AuthenticationException
     */
    public function getUserAccount(string $userAccountId): array
    {
        $s3Auth = new S3AuthManager($this->oauthToken, $this->httpClient);
        $iamToken = $s3Auth->getValidIamToken();

        try {
            $response = $this->httpClient->get(self::USER_ACCOUNT_GET_ENDPOINT . '/' . $userAccountId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $iamToken,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['id'])) {
                throw new AuthenticationException('User account response does not contain id field');
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Error getting user account: ' . $e->getMessage());
        }
    }

    /**
     * Get list of clouds
     *
     * @return array
     * @throws AuthenticationException
     */
    public function listClouds(): array
    {
        $s3Auth = new S3AuthManager($this->oauthToken, $this->httpClient);
        $iamToken = $s3Auth->getValidIamToken();

        try {
            $response = $this->httpClient->get(self::CLOUDS_ENDPOINT, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $iamToken,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['clouds'] ?? [];
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Error getting clouds list: ' . $e->getMessage());
        }
    }

    /**
     * Get list of folders in cloud
     *
     * @param  string  $cloudId
     * @return array
     * @throws AuthenticationException
     */
    public function listFolders(string $cloudId): array
    {
        $s3Auth = new S3AuthManager($this->oauthToken, $this->httpClient);
        $iamToken = $s3Auth->getValidIamToken();

        try {
            $response = $this->httpClient->get(self::FOLDERS_ENDPOINT, [
                'query' => [
                    'cloudId' => $cloudId,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $iamToken,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['folders'] ?? [];
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Error getting folders list: ' . $e->getMessage());
        }
    }
}
