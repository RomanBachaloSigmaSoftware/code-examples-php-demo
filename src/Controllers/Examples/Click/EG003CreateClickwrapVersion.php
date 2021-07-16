<?php

namespace Example\Controllers\Examples\Click;

use DocuSign\Click\Client\ApiException;
use Example\Controllers\ClickApiBaseController;
use Example\Services\ClickApiClientService;
use Example\Services\RouterService;
use Example\Services\Examples\Click\CreateNewClickwrapVersionService;
use Example\Services\Examples\Click\GetClickwrapsService;

class EG003CreateClickwrapVersion extends ClickApiBaseController
{
    private ClickApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "ceg003";  # reference (and URL) for this example

    /**
     * 1. Get available clickwraps
     * 2. Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new ClickApiClientService($this->args);
        $this->routerService = new RouterService();
        # Get available clickwraps
        $clickwraps = CreateNewClickwrapVersionService::getClickwraps(
            $this->routerService,
            $this->args, 
            $this->clientService,
            $this->eg);
        parent::controller($this->eg, $this->routerService, basename(__FILE__), ['clickwraps' => $clickwraps]);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return created clickwrap version
     *
     * @return void
     * @throws ApiException
     */
    function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = CreateNewClickwrapVersionService::createNewClickwrapVersion($this->args, $this::DEMO_DOCS_PATH, $this->clientService);

            if ($results) {
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Creating a new clickwrap version example",
                    "Creating a new clickwrap version example",
                    "Clickwrap version has been created!",
                    json_encode(json_encode($results))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    private function getTemplateArgs(): array
    {
        $clickwrap_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['clickwrap_name']);
        $clickwrap_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['clickwrap_id']);

        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'clickwrap_name' => $clickwrap_name,
            'clickwrap_id' => $clickwrap_id,
        ];
    }
}
