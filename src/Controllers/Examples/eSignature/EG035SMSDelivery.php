<?php
/**
 * Example 035: SMS Delivery with remote signer and carbon copy.
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\RecipientPhoneNumber;
use DocuSign\eSign\Model\RecipientAdditionalNotification;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Services\Examples\eSignature\SMSDeliveryService;

class EG035SMSDelivery extends eSignBaseController
{
    /** signatureClientService */
    private SignatureClientService $clientService;

    /** RouterService */
    private RouterService $routerService;

    /** Specific template arguments */
    private array $args;

    private string $eg = "eg035";  # reference (and URL) for this example

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
     * 3. Redirect the user to the signing
     *
     * @return void
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = SMSDeliveryService::worker($this->args, $this->clientService, $this::DEMO_DOCS_PATH);

            if ($results) {
                $_SESSION["envelope_id"] = $results["envelope_id"]; # Save for use by other examples
                                                                    # which need an envelope_id
                $this->clientService->showDoneTemplate(
                    "Envelope sent",
                    "Envelope sent",
                    "The envelope has been created and sent!<br/> Envelope ID {$results["envelope_id"]}."
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
        $signer_name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signerName' ]);
        $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signerEmail']);
        $cc_name      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['ccName'     ]);
        $cc_email     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['ccEmail'    ]);
        $cc_country_code = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['ccCountryCode']);
        $cc_phone_number = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['ccPhoneNumber']);
        $signer_country_code = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['countryCode']);
        $signer_phone_number = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['phoneNumber']);

        $envelope_args = [
            'signer_email' => $signer_email,
            'signer_name' => $signer_name,
            'signer_country_code' => $signer_country_code,
            'signer_phone_number' => $signer_phone_number,
            'cc_email' => $cc_email,
            'cc_name' => $cc_name,
            'cc_country_code' => $cc_country_code,
            'cc_phone_number' => $cc_phone_number,
            'status' => 'sent'
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


