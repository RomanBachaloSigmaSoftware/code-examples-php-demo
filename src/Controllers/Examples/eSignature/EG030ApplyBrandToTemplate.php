<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Services\Examples\eSignature\ApplyBrandToEnvelopeService;

class EG030ApplyBrandToTemplate extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg030"; # Reference (and URL) for this example

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
        $brands = $this->clientService->getBrands($this->args);
        parent::controller($this->eg, $this->routerService, basename(__FILE__), null, $brands);
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
        $template_id = $this->args['template_id'];
        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);
        if ($token_ok) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = ApplyBrandToEnvelopeService::worker($this->args, $this::DEMO_DOCS_PATH, $this->clientService);

            if ($results) {
                # That need an envelope_id
                $this->clientService->showDoneTemplate(
                    "Brand applying to template",
                    "Brand applying to template",
                    "The brand has been applied to the template!<br/> Envelope ID {$results["envelope_id"]}."
                );
            }
        } elseif (!$token_ok) {
            $this->clientService->needToReAuth($this->eg);
        } elseif (!$template_id) {
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
        $signer_name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name']);
        $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
        $brand_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['brand_id']);
        $envelope_args = [
            'signer_email' => $signer_email,
            'signer_name' => $signer_name,
            'brand_id' => $brand_id
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