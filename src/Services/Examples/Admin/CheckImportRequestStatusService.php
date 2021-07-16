<?php

namespace Example\Services\Examples\Admin;

use DocuSign\OrgAdmin\Api\BulkImportsApi;

include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Api\BulkImportsApi.php";

class CheckImportRequestStatusService
{
    /**
     * Method to check the request status of bulk-import.
     * @param $clientService
     * @return string
     * @throws \DocuSign\OrgAdmin\Client\ApiException
     */
    public static function checkRequestStatus($clientService): string
    {
        // create a bulk exports api instance
        $bulkImportsApi = new BulkImportsApi($clientService->getApiClient());

        $organizationId = $GLOBALS['DS_CONFIG']['organization_id'];
        $importId = $_SESSION['import_id'];

        return $bulkImportsApi->getBulkUserImportRequest($organizationId, $importId)->__toString();
    }
}
