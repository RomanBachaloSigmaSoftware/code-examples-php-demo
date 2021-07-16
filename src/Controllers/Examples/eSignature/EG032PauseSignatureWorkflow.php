<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Workflow;
use DocuSign\eSign\Model\WorkflowStep;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Services\Examples\eSignature\PauseSignatureWorkflowService;

class EG032PauseSignatureWorkflow extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg032"; # Reference (and URL) for this example

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
            $envelope_id = PauseSignatureWorkflowService::worker($this->args, $this->clientService, $this::DEMO_DOCS_PATH);

            if ($envelope_id) {
                $_SESSION["pause_envelope_id"] = $envelope_id;
                $nextExampleUrl = "/public/index.php?page=eg033";
                $this->clientService->showDoneTemplate(
                    "Envelope sent",
                    "Envelope sent",
                    "The envelope has been created and sent!
                             <br/>Envelope ID {$envelope_id}.<br/>
                             <p>To resume a workflow after the first recipient signs
                             the envelope use <a href={$nextExampleUrl}>example 33.</a><br/>"
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
        $signer2_name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer2_name']);
        $signer2_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer2_email']);
        $envelope_args = [
            'signer1_email' => $signer1_email,
            'signer1_name' =>  $signer1_name,
            'signer2_email' => $signer2_email,
            'signer2_name' =>  $signer2_name,
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
