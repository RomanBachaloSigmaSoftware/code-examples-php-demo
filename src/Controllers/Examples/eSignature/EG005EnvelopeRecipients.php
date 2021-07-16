<?php
/**
 * Example 005: List an envelope's recipients and status
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Recipients;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Services\Examples\eSignature\EnvelopeRecipientsService;

class EG005EnvelopeRecipients extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg005";  # reference (and url) for this example

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
     * 1. Check the token and check we have an envelope_id
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        $envelope_id= $this->args['envelope_id'];
        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);
        if ($token_ok && $envelope_id) {
            # 2. Call the worker method
            $results = EnvelopeRecipientsService::worker($this->args, $this->clientService);
            if ($results) {
                # results is an object that implements ArrayAccess. Convert to a regular array:
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Envelope recipients results",
                    "List the envelope's recipients and their status",
                    "Results from the EnvelopesRecipients::list method:",
                    json_encode(json_encode($results))
                );
            }
        } elseif (! $token_ok) {
            $this->clientService->needToReAuth($this->eg);
        } elseif (! $envelope_id) {
            $this->clientService->envelopeNotCreated(
                basename(__FILE__),
                $this->routerService->getTemplate($this->eg),
                $this->routerService->getTitle($this->eg),
                $this->eg,
                ['envelope_ok' => false]
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
        $envelope_id= isset($_SESSION['envelope_id']) ? $_SESSION['envelope_id'] : false;
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_id' => $envelope_id
        ];

        return $args;
    }
}
