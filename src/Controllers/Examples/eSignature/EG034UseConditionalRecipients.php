<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Checkbox;
use DocuSign\eSign\Model\ConditionalRecipientRule;
use DocuSign\eSign\Model\ConditionalRecipientRuleCondition;
use DocuSign\eSign\Model\ConditionalRecipientRuleFilter;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\RecipientGroup;
use DocuSign\eSign\Model\RecipientOption;
use DocuSign\eSign\Model\RecipientRouting;
use DocuSign\eSign\Model\RecipientRules;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Workflow;
use DocuSign\eSign\Model\WorkflowStep;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Services\Examples\eSignature\UseConditionalRecipientsService;

class EG034UseConditionalRecipients extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg034"; # Reference (and URL) for this example

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
            $envelope_id = UseConditionalRecipientsService::worker($this->args, $this->clientService, $this::DEMO_DOCS_PATH);

            if ($envelope_id) {
                # That need an envelope_id
                $this->clientService->showDoneTemplate(
                    "Use conditional recipients",
                    "Use conditional recipients",
                    "Envelope ID {$envelope_id} with the conditional
                            routing criteria has been created and sent to the first recipient!"
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
        $signer1_name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer1_name']);
        $signer1_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer1_email']);
        $signer_2a_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_2a_name']);
        $signer_2a_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_2a_email']);
        $signer_2b_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_2b_name']);
        $signer_2b_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_2b_email']);
        $envelope_args = [
            'signer1_email' => $signer1_email,
            'signer1_name' => $signer1_name,
            'signer_2a_email' => $signer_2a_email,
            'signer_2a_name' => $signer_2a_name,
            'signer_2b_email' => $signer_2b_email,
            'signer_2b_name' => $signer_2b_name,
            'status' => "Sent",
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
