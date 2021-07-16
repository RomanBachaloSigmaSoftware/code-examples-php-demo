<?php

namespace Example\Services\Examples\Admin;

use DocuSign\OrgAdmin\Api\BulkExportsApi;
use DocuSign\OrgAdmin\Client\ApiException;
use function GuzzleHttp\json_decode;
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Client\ApiClient.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Api\BulkExportsApi.php";

class CheckRequestStatusService
{
    /**
     * Method to get a request status for bulk-export.
     * @throws ApiException
     */
    public static function checkRequestStatus($clientService)
    {
        $apiClient = $clientService->getApiClient();

        $organizationId = $GLOBALS['DS_CONFIG']['organization_id'];
        $exportId = $_SESSION['export_id'];

        $bulkExportsApi = new BulkExportsApi($apiClient);

        $result = $bulkExportsApi->getUserListExport($organizationId, $exportId);

        return json_decode($result->__toString());
    }
}
