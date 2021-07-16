<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\BulkSendingCopy;
use DocuSign\eSign\Model\BulkSendingCopyRecipient;
use DocuSign\eSign\Model\BulkSendingList;
use DocuSign\eSign\Model\BulkSendRequest;
use DocuSign\eSign\Model\CustomFields;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\TextCustomField;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Services\Examples\eSignature\BulkSendEnvelopesService;

class EG031BulkSendEnvelopes extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg031"; # Reference (and URL) for this example

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
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 1. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = json_decode(BulkSendEnvelopesService::worker($this->args, $this->clientService, $this::DEMO_DOCS_PATH), true);

            if ($results) {
                # That need an envelope_id
                $this->clientService->showDoneTemplate(
                    "Bulk sending envelopes to multiple recipients",
                    "Bulk sending envelopes to multiple recipients",
                    "The envelope has been sent to recipients!<br/> Batch id: {$results['batchId']}"
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
        $signer_name_1  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name_1']);
        $signer_email_1 = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email_1']);
        $cc_name_1      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name_1']);
        $cc_email_1     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email_1']);
        $signer_name_2  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name_2']);
        $signer_email_2 = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email_2']);
        $cc_name_2      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name_2']);
        $cc_email_2     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email_2']);
        $signers = [
            [
                'signer_email' => $signer_email_1,
                'signer_name' => $signer_name_1,
                'cc_email' => $cc_email_1,
                'cc_name' => $cc_name_1
            ],
            [
                'signer_email' => $signer_email_2,
                'signer_name' => $signer_name_2,
                'cc_email' => $cc_email_2,
                'cc_name' => $cc_name_2
            ]
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'signers' => $signers
        ];

        return $args;
    }
}
