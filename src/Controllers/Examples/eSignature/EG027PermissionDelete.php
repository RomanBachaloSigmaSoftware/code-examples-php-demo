<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Services\Examples\eSignature\PermissionDeleteService;

class EG027PermissionDelete extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg027"; # Reference (and URL) for this example

    /**
     * Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        $permission_profiles = $this->clientService->getPermissionsProfiles($this->args);
        parent::controller(
            $this->eg,
            $this->routerService,
            basename(__FILE__),
            null,
            null,
            $permission_profiles
        );
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 1. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = PermissionDeleteService::worker($this->args, $this->clientService);

            if ($results) {
                # That need an envelope_id
                $this->clientService->showDoneTemplate(
                    "Deleting a permission profile",
                    "Deleting a permission profile",
                    $results
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
        $permission_profile_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['permission_profile_id']);
        $permissions_args = [
            'permission_profile_id' => $permission_profile_id,
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'permission_args' => $permissions_args
        ];

        return $args;
    }
}