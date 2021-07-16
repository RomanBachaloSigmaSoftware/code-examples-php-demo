<?php

namespace Example\Controllers\Examples\Click;

use Example\Controllers\ClickApiBaseController;
use Example\Services\ClickApiClientService;
use Example\Services\RouterService;
use Example\Services\Examples\Click\GetClickwrapsService;

class EG004GetClickwraps extends ClickApiBaseController
{
    private ClickApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "ceg004"; # reference (and URL) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new ClickApiClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }
    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return clickwraps data
     *
     * @return void
     */
    function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = GetClickwrapsService::getClickwraps($this->args, $this->clientService);

            if ($results) {
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Get a list of clickwraps",
                    "Get a list of clickwraps",
                    "Results from the ClickWraps::getClickwraps method:",
                    json_encode(json_encode($results))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    private function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token']
        ];
    }
}
