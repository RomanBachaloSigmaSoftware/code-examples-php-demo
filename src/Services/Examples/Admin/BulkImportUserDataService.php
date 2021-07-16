<?php

namespace Example\Services\Examples\Admin;



use DocuSign\Monitor\Client\ApiException;
use DocuSign\OrgAdmin\Client\ApiClient;
use DocuSign\OrgAdmin\Configuration;
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Client\ApiClient.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Client\ApiException.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Configuration.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Api\BulkImportsApi.php";

use DocuSign\OrgAdmin\Model\OrganizationImportResponse;
use InvalidArgumentException;
use function GuzzleHttp\json_decode;

class BulkImportUserDataService
{
    /**
     * Method to prepare headers and create a bulk-import.
     * @throws ApiException for API problems.
     */
    public static function bulkImportUserData()
    {
        $config = new Configuration();
        $accessToken =  $_SESSION['ds_access_token'];

        $config->setAccessToken($accessToken);
        $config->setHost('https://api-d.docusign.net/management');
        $config->addDefaultHeader("Content-Disposition", "attachment; filename=myfile.csv");
        $apiClient = new ApiClient($config);

        $organizationId = $GLOBALS['DS_CONFIG']['organization_id'];

        $userData = "AccountID,UserName,UserEmail,PermissionSet\n" .
            $GLOBALS['DS_CONFIG']['account_id'] . ",FirstLast1,exampleuser1@example.com,DS Viewer";

        $result = BulkImportUserDataService::createBulkImport($organizationId, $userData, $apiClient);

        $_SESSION['import_id'] = strval($result->getId());

        return json_decode($result->__toString());
    }

    /**
     * Method to call a request method and transform responce into OrganizationImportResponse
     * @param $organization_id
     * @param $userData
     * @param $apiClient
     * @return OrganizationImportResponse
     * @throws ApiException for API problems.
     */
    public static function createBulkImport($organization_id, $userData, $apiClient): OrganizationImportResponse
    {
        list($response) = BulkImportUserDataService::createRequestForBulkImport($organization_id, $userData, $apiClient);
        return $response;
    }

    /**
     * Method to create a POST request to the server.
     * @param $organization_id
     * @param $_tempBody
     * @param $apiClient
     * @return array
     * @throws ApiException for API problems.
     */
    public static function createRequestForBulkImport($organization_id, $_tempBody, $apiClient): array
    {
        if ($organization_id === null) {
            throw new InvalidArgumentException('Missing the required parameter $organization_id when calling createBulkImportAddUsersRequest');
        }

        $resourcePath = "/v2/organizations/" . $organization_id . "/imports/bulk_users/add";
        $httpBody = $_tempBody ?? '';

        $queryParams = $headerParams = [];
        $headerParams['Accept'] ??= $apiClient->selectHeaderAccept(['application/json']);
        $headerParams['Content-Type'] = $apiClient->selectHeaderContentType(['text/csv']);

        if (strlen($apiClient->getConfig()->getAccessToken()) !== 0) {
            $headerParams['Authorization'] = 'Bearer ' . $apiClient->getConfig()->getAccessToken();
        }

        try {
            list($response, $statusCode, $httpHeader) = $apiClient->callApi(
                $resourcePath,
                'POST',
                $queryParams,
                $httpBody,
                $headerParams,
                '\DocuSign\OrgAdmin\Model\OrganizationImportResponse',
                '/v2/organizations/{organizationId}/imports/bulk_users/add'
            );

            return [$apiClient->getSerializer()->deserialize($response, '\DocuSign\OrgAdmin\Model\OrganizationImportResponse', $httpHeader), $statusCode, $httpHeader];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\OrgAdmin\Model\OrganizationImportResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }
}
