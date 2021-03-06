<?php

namespace Example\Controllers\Examples\Monitor;

use Example\Controllers\MonitorBaseController;
use Example\Services\MonitorApiClientService;
use Example\Services\RouterService;
use Example\Services\JWTService;
use Example\Services\Examples\Monitor\GetMonitoringDataService;
use DocuSign\Monitor\Client\ApiException;

use function GuzzleHttp\json_decode;

class Eg001GetMonitoringData extends MonitorBaseController
{
    /** Monitor client service */
    private $clientService;

    /** Router service */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "meg001";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new MonitorApiClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException for API problems and perhaps file access\Exception too.
     */
    public function createController(): void
    {
        $accessToken =  $_SESSION['ds_access_token'];
        $tokenExpirationTime = $_SESSION['ds_expiration'];
        if (is_null($accessToken) ||
            (time() +  JWTService::TOKEN_REPLACEMENT_IN_SECONDS) > $tokenExpirationTime) {
            $auth = new JWTService();
            $auth->login();
        } else {
            // Call the worker method
            $results = GetMonitoringDataService::getMonitoringData($this->clientService);

            if ($results) {
                $this->clientService->showDoneTemplate(
                    "Monitoring data",
                    "Monitoring data result",
                    "Results from DataSet:GetStream method:",
                    json_encode(json_encode($results))
                );
            }
        }
    }

    /**
     * Get specific template arguments
     *
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
