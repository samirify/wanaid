<?php

namespace Modules\Client\Services\Firebase;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\FetchAuthTokenInterface;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Modules\Client\Exception\RecordNotFoundException;
use Modules\Client\Exception\UnavailableSubDomainException;
use Modules\Client\Models\Firebase\FirebaseUser;

class FirebaseUtil
{
    const GOOGLE_API_TOOLKIT_TYPE = 'identitytoolkit';

    const GOOGLE_API_FIRESTORE_TYPE = 'firestore';

    /** @var string $docsUri */
    private string $docsUri;

    /** @var FetchAuthTokenInterface|ServiceAccountCredentials */
    private FetchAuthTokenInterface|ServiceAccountCredentials $authTokenInterface;

    /**
     * FirebaseUtil constructor
     */
    public function __construct()
    {
        $this->authTokenInterface = new ServiceAccountCredentials(
            [
                'https://www.googleapis.com/auth/datastore',
                'https://www.googleapis.com/auth/cloud-platform'
            ],
            // TOTO: Fix path for prod
            base_path('firebase/keys/firebase.json')
        );

        $this->docsUri = "/v1/projects/{$this->getProjectId()}/databases/(default)/documents";
    }

    /**
     * @return HandlerStack
     */
    private function getClientHandlerStack(): HandlerStack
    {
        $middleware = new AuthTokenMiddleware($this->authTokenInterface);
        $stack = HandlerStack::create();
        $stack->push($middleware);

        return $stack;
    }

    /**
     * @param string $type
     *
     * @return Client
     */
    private function getClient(string $type): Client
    {
        return new Client([
            'handler' => $this->getClientHandlerStack(),
            'base_uri' => "https://{$type}.googleapis.com",
            'auth' => 'google_auth'
        ]);
    }

    /**
     * @return string|null
     */
    public function getProjectId(): ?string
    {
        return $this->authTokenInterface->getProjectId();
    }

    /**
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUsers(): array
    {
        $docResponse = $this->getClient(self::GOOGLE_API_TOOLKIT_TYPE)
            ->post("/v1/projects/{$this->getProjectId()}/accounts:query");

        return json_decode((string)$docResponse->getBody(), true);
    }

    /**
     * @param array $where
     *
     * @return FirebaseUser|null
     * @throws GuzzleException
     * @throws NotFoundException
     */
    public function getUser(array $where = []): ?FirebaseUser
    {
        $matchingRecords = array_filter($this->getUsers()['userInfo'], function ($item) use ($where) {
            $result = array_filter($item, 'is_scalar');
            return count(array_intersect($result, $where)) === count($where);
        });

        // dd(array_values($matchingRecords)[0]);

        $userRecord = null;

        if (count($matchingRecords) === 1) {
            $firstRecord = array_values($matchingRecords)[0];
            $userRecord = (new FirebaseUser(
                $firstRecord['localId'],
                $firstRecord['email'],
                $firstRecord['displayName'],
                $firstRecord['emailVerified'],
            ));
        }

        if (is_null($userRecord)) {
            throw new RecordNotFoundException("User not found!");
        }

        return $userRecord;
    }

    /**
     * @param string $customerId
     * @param string $subDomain
     *
     * @return mixed
     * @throws GuzzleException
     * @throws NotFoundException
     */
    public function getCustomerProjectBySubDomain(string $customerId, string $subDomain): mixed
    {
        $projectsResponse = $this->getClient(self::GOOGLE_API_FIRESTORE_TYPE)
            ->get("{$this->docsUri}/customers/{$customerId}/projects");

        $projectsJson = (string)$projectsResponse->getBody();

        $projectsArray = json_decode($projectsJson, true, JSON_FORCE_OBJECT);

        $projectsDocuments = $projectsArray['documents'] ?? [];

        foreach ($projectsDocuments as $pDoc) {
            $pSubDomain = strtolower($pDoc['fields']['sub_domain']['stringValue'] ?? '');
            if ($pSubDomain === strtolower($subDomain)) {
                return $pDoc;
            }
        }

        throw new RecordNotFoundException("Project not found!");
    }

    /**
     * Check if the sub-domain has not been used by another customer.
     *
     * @param string $subDomain
     *
     * @return bool
     * @throws GuzzleException
     */
    public function checkAvailableSubDomain(string $subDomain): bool
    {
        $subDomainsResponse = $this->getClient(self::GOOGLE_API_FIRESTORE_TYPE)
            ->get("{$this->docsUri}/sub_domains");

        $subDomainsJson = (string)$subDomainsResponse->getBody();

        $subDomainsArray = json_decode($subDomainsJson, true, JSON_FORCE_OBJECT);

        $subDomainsDocuments = $subDomainsArray['documents'] ?? [];

        foreach ($subDomainsDocuments as $sdDoc) {
            $pSubDomain = strtolower($sdDoc['fields']['sub_domain']['stringValue'] ?? '');
            if ($pSubDomain === strtolower($subDomain)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $customerId
     * @param string $projectSubDomain
     *
     * @return void
     * @throws GuzzleException
     */
    private function registerCustomerSubDomain(string $customerId, string $projectSubDomain): void
    {
        $this->getClient(self::GOOGLE_API_FIRESTORE_TYPE)
            ->request('POST', "{$this->docsUri}/sub_domains", [
                'json' => [
                    'fields' => [
                        'sub_domain' => [
                            'stringValue' => $projectSubDomain
                        ],
                        'user_id' => [
                            'stringValue' => $customerId
                        ],
                    ]
                ]
            ]);
    }

    /**
     * @param string $customerId
     * @param array $fields
     *
     * @return void
     * @throws GuzzleException
     * @throws UnavailableSubDomainException
     */
    public function createCustomerProject(string $customerId, array $fields = []): void
    {
        $projectSubDomain = $fields['sub_domain']['stringValue'];

        if (!$this->checkAvailableSubDomain($projectSubDomain)) {
            throw new UnavailableSubDomainException();
        }

        $this->registerCustomerSubDomain($customerId, $projectSubDomain);

        $this->getClient(self::GOOGLE_API_FIRESTORE_TYPE)
            ->request('POST', "{$this->docsUri}/customers/{$customerId}/projects", [
                'json' => [
                    'fields' => $fields
                ]
            ]);
    }

    /**
     * @param string $customerId
     * @param string $projectSubDomain
     * @param array $data
     *
     * @return void
     * @throws GuzzleException
     * @throws NotFoundException
     */
    public function updateCustomerProject(string $customerId, string $projectSubDomain, array $data = []): void
    {
        $project = $this->getCustomerProjectBySubDomain($customerId, $projectSubDomain);

        $project['fields'] = array_merge($project['fields'], $data);

        $this->getClient(self::GOOGLE_API_FIRESTORE_TYPE)
            ->request('PATCH', "/v1/{$project['name']}", [
                'json' => $project
            ]);
    }

    /**
     * @param string $customerId
     * @param string $projectSubDomain
     *
     * @return void
     * @throws GuzzleException
     * @throws NotFoundException
     */
    public function deleteCustomerProject(string $customerId, string $projectSubDomain): void
    {
        $project = $this->getCustomerProjectBySubDomain($customerId, $projectSubDomain);

        $this->getClient(self::GOOGLE_API_FIRESTORE_TYPE)
            ->delete("/v1/{$project['name']}");
    }
}
