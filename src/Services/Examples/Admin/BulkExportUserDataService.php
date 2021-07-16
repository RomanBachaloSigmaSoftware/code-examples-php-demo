<?php

namespace Example\Services\Examples\Admin;


use DocuSign\OrgAdmin\Api\BulkExportsApi;
use DocuSign\OrgAdmin\Client\ApiException;
use function GuzzleHttp\json_decode;
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Client\ApiClient.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Api\BulkExportsApi.php";

class BulkExportUserDataService
{
    /**
     * Method to get user bulk-exports from your organization.
     * @throws ApiException
     */
    public static function getExportsData($clientService)
    {
        $apiClient = $clientService->getApiClient();

        $organizationId = $GLOBALS['DS_CONFIG']['organization_id'];
        $bulkExportsApi = new BulkExportsApi($apiClient);

        $result = $bulkExportsApi->getUserListExports($organizationId);

        $_SESSION['export_id'] = strval($result->getExports()[0]->getId());

        return json_decode($result->__toString());
    }
}
