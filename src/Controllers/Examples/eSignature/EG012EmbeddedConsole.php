<?php
/**
 * Example 012: Embedded console
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\ConsoleViewRequest;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Services\Examples\eSignature\EmbeddedConsoleService;

class EG012EmbeddedConsole extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg012";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);
        if ($token_ok) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = EmbeddedConsoleService::worker($this->args, $this->clientService);

            if ($results) {
                # Redirect the user to the NDSE view
                # Don't use an iFrame!
                # State can be stored/recovered using the framework's session or a
                # query parameter on the returnUrl
                header('Location: ' . $results["redirect_url"]);
                exit;
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
        $starting_view = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['starting_view']);
        $envelope_id= isset($_SESSION['envelope_id']) ? $_SESSION['envelope_id'] : false;
        $args = [
            'envelope_id' => $envelope_id,
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'starting_view' => $starting_view,
            'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return'
        ];

        return $args;
    }
}

