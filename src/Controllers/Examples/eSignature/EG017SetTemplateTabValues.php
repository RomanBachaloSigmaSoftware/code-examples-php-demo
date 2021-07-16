<?php
/**
 * Example 017: Set template field (tab) values and an envelope custom field value
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Checkbox;
use DocuSign\eSign\Model\CustomFields;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Number;
use DocuSign\eSign\Model\Radio;
use DocuSign\eSign\Model\RadioGroup;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\TemplateRole;
use DocuSign\eSign\Model\Text;
use DocuSign\eSign\Model\TextCustomField;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Services\Examples\eSignature\SetTemplateTabValuesService;

class EG017SetTemplateTabValues extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg017";  # reference (and url) for this example
    private $signer_client_id = 1000; # Used to indicate that the signer will use embedded
                                      # signing. Represents the signer's userId within your application.

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
        $template_id = $this->args['envelope_args']['template_id'];
        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);
        if ($token_ok && $template_id) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = SetTemplateTabValuesService::worker($this->args, $this->clientService);

            if ($results) {
                $_SESSION["envelope_id"] = $results["envelope_id"]; # Save for use by other examples
                                                                    # which need an envelope_id

                # Redirect the user to the embedded signing
                # Don't use an iFrame!
                # State can be stored/recovered using the framework's session or a
                # query parameter on the returnUrl (see the makerecipient_view_request method)
                header('Location: ' . $results["redirect_url"]);
                exit;
            }
        } elseif (! $token_ok) {
            $this->clientService->needToReAuth($this->eg);
        } elseif (! $template_id) {
            $this->clientService->envelopeNotCreated(
                basename(__FILE__),
                $this->routerService->getTemplate($this->eg),
                $this->routerService->getTitle($this->eg),
                $this->eg,
                ['template_ok' => false]
            );
        }
    }


    

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $template_id = isset($_SESSION['template_id']) ? $_SESSION['template_id'] : false;
        $signer_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name']);
        $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
        $cc_name      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name'     ]);
        $cc_email     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email'    ]);
        $envelope_args = [
            'signer_email' => $signer_email,
            'signer_name' => $signer_name,
            'signer_client_id' => $this->signer_client_id,
            'cc_email' => $cc_email,
            'cc_name' => $cc_name,
            'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return',
            'template_id' => $template_id
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