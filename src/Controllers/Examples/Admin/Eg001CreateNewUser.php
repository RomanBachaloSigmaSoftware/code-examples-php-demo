<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminBaseController;
use Example\Services\AdminApiClientService;
use Example\Services\RouterService;
use Example\Services\Examples\Admin\CreateNewUserService;
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Client\ApiClient.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Client\ApiException.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Configuration.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Api\UsersApi.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Model\NewUserRequest.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Model\NewUserRequestAccountProperties.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Model\PermissionProfileRequest.php";

use DocuSign\OrgAdmin\Api\UsersApi;
use DocuSign\OrgAdmin\Model\NewUserRequestAccountProperties;
use NewUserRequest as GlobalNewUserRequest;

class Eg001CreateNewUser extends AdminBaseController
{
    /** Admin client service */
    private $clientService;

    /** Router service */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "aeg001";  # reference (and url) for this example

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
        $minimum_buffer_min = 3;

        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {

            $organizationId = $GLOBALS['DS_CONFIG']['organization_id'];

            // Call the worker method
            $results = CreateNewUserService::addActiveUser(
                $organizationId,
                $this->args["envelope_args"],
                $this->clientService);

            if ($results) {
                $this->clientService->showDoneTemplate(
                    "Create a new user",
                    "Admin API data response output:",
                    "Results from Users:createUser:",
                    json_encode(($results->__toString()))
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
        $Name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['Name']);
        $FirstName = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['FirstName']);
        $LastName = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['LastName']);
        $Email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['Email']);
        $envelope_args = [
            'Name' => $Name,
            'FirstName' => $FirstName,
            'LastName' => $LastName,
            'Email' => $Email
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];

        return $args;
    }
}
