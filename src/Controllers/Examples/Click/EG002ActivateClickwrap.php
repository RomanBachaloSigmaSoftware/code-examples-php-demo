<?php

namespace Example\Controllers\Examples\Click;

use Example\Controllers\ClickApiBaseController;
use Example\Services\ClickApiClientService;
use Example\Services\RouterService;
use Example\Services\Examples\Click\ActivateClickwrapService;

class EG002ActivateClickwrap extends ClickApiBaseController
{
    private ClickApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "ceg002";  # reference (and URL) for this example
    
    /**
     * 1. Get available inactive clickwraps
     * 2. Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new ClickApiClientService($this->args);
        $this->routerService = new RouterService();

        # Step 1. Get available inactive clickwraps
        $inactiveClickwraps = ActivateClickwrapService::getInactiveClickwraps(
            $this->routerService, 
            $this->clientService, 
            $this->args, 
            $this->eg);
        parent::controller($this->eg, $this->routerService, basename(__FILE__), ['clickwraps' => $inactiveClickwraps]);
    }
    
    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Display activated clickwrap data
     *
     * @return void
     */
    function createController()
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = ActivateClickwrapService::activateClickwrap($this->args, $this->clientService);

            if ($results) {
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Activate Clickwrap",
                    "Activate Clickwrap",
                    "Clickwrap activated",
                    json_encode(json_encode($results))
                );
            }

        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    private function getTemplateArgs(): array
    {
        $clickwrap_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['clickwrap_id']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'clickwrap_id' => $clickwrap_id,
        ];
    }
}
