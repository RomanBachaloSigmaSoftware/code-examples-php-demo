<?php
/**
 * Example 008: create a template if it doesn't already exist
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Api\TemplatesApi\ListTemplatesOptions;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Checkbox;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeTemplate;
use DocuSign\eSign\Model\ModelList;
use DocuSign\eSign\Model\Number;
use DocuSign\eSign\Model\Radio;
use DocuSign\eSign\Model\RadioGroup;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Text;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Services\Examples\eSignature\CreateTemplateService;

class EG008CreateTemplate extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg008";  # reference (and url) for this example
    private $template_name = 'Example Signer and CC template';

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
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            $results = CreateTemplateService::worker($this->args, $this->template_name, $this::DEMO_DOCS_PATH, $this->clientService);
            if ($results) {
                $_SESSION["template_id"] = $results["template_id"]; # Save for use by other examples
                $msg = $results['created_new_template'] ? "The template has been created!" :
                            "Done. The template already existed in your account.";

                $this->clientService->showDoneTemplate(
                    "Template results",
                    "Template results",
                    "{$msg}<br/>Template name: {$results['template_name']}, 
                                ID {$results['template_id']}."
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
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];

        return $args;
    }
}


