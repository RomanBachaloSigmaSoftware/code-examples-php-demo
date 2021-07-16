<?php

namespace Example\Controllers\Examples\Rooms;

use DocuSign\Rooms\Model\FormGroup;
use DocuSign\Rooms\Model\FormGroupForCreate;
use Example\Controllers\RoomsApiBaseController;
use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;
use Example\Services\Examples\Rooms\CreateFormGroupService;

class EG007CreateFormGroup extends RoomsApiBaseController
{
    private RoomsApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "reg007";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new RoomsApiClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return created FormGroup
     *
     * @return void
     */
    function createController()
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = CreateFormGroupService::worker($this->args, $this->clientService);
            if ($results) {
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Create a form group",
                    "Create a form group",
                    "Results of FormGroups::createFormGroup",
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
        $form_group_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['form_group_name']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'form_group_name' => $form_group_name,
        ];
    }
}
