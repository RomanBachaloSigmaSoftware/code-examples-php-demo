<?php

namespace Example\Controllers\Examples\Admin;
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Client\ApiClient.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Api\BulkExportsApi.php";
use DocuSign\OrgAdmin\Api\BulkExportsApi;
use Example\Controllers\AdminBaseController;
use Example\Services\AdminApiClientService;
use Example\Services\RouterService;
use Example\Services\Examples\Admin\BulkExportUserDataService;

use function GuzzleHttp\json_decode;

class Eg002BulkExportUserData extends AdminBaseController
{
    /** Admin client service */
    private $clientService;

    /** Router service */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "aeg002";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new AdminApiClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException for API problems.
     */
    public function createController(): void
    {
        $accessToken =  $_SESSION['ds_access_token'];
        $minimum_buffer_min = 3;

        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = BulkExportUserDataService::getExportsData($this->clientService);

            if ($results) {
                $this->clientService->showDoneTemplate(
                    "Bulk export user data",
                    "Admin API data response output:",
                    "Results from UserExport:getUserListExports method:",
                    json_encode(json_encode($results))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Get specific template arguments
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];

        return $args;
    }
}
