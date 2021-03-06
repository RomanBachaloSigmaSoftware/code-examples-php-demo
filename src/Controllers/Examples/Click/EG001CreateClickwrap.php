<?php

namespace Example\Controllers\Examples\Click;

use DocuSign\Click\Client\ApiException;
use Example\Controllers\ClickApiBaseController;
use Example\Services\ClickApiClientService;
use Example\Services\RouterService;
use Example\Services\Examples\Click\CreateClickwrapService;

class EG001CreateClickwrap extends ClickApiBaseController
{
    private ClickApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "ceg001";  # reference (and URL) for this example

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
     * 3. Return created clickwrap data
     *
     * @return void
     * @throws ApiException
     */
    function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = CreateClickwrapService::createClickwrap($this->args, self::DEMO_DOCS_PATH, $this->clientService);

            if ($results) {
                $clickwrap_name = $results['clickwrapName'];
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Creating a clickwrap example",
                    "Creating a clickwrap example",
                    "Clickwrap $clickwrap_name has been created!",
                    json_encode(json_encode($results))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $clickwrap_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['clickwrap_name']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'clickwrap_name' => $clickwrap_name
        ];
    }
}
