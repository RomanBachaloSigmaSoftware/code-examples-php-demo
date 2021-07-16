<?php
/**
 * Example 007: Get an envelope's document
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Services\Examples\eSignature\EnvelopeGetDocService;

class EG007EnvelopeGetDoc extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg007";  # reference (and url) for this example

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
     * Check the token and check we have an envelope_id
     * Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        $envelope_id= $this->args['envelope_id'];
        $envelope_documents = isset($_SESSION['envelope_documents']) ? $_SESSION['envelope_documents'] : false;
        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);
        if ($token_ok && $envelope_id&& $envelope_documents) {
            # Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = EnvelopeGetDocService::worker($this->args, $this->clientService);

            if ($results) {
                # See https://stackoverflow.com/a/27805443/64904
                header("Content-Type: {$results['mimetype']}");
                header("Content-Disposition: attachment; filename=\"{$results['doc_name']}\"");
                ob_clean();
                flush();
                $file_path = $results['data']->getPathname();
                readfile($file_path);
                exit();
            }
        } elseif (! $token_ok) {
            $this->clientService->needToReAuth($this->eg);
        } elseif (! $envelope_id|| ! $envelope_documents) {
            $this->clientService->envelopeNotCreated(
                basename(__FILE__),
                $this->routerService->getTemplate($this->eg),
                $this->routerService->getTitle($this->eg),
                $this->eg,
                ['envelope_ok' => false, 'documents_ok' => false]
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
        $envelope_documents = isset($_SESSION['envelope_documents']) ? $_SESSION['envelope_documents'] : false;
        $document_id  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['document_id' ]);
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_id' => $envelope_id,
            'document_id' => $document_id,
            'envelope_documents' => $envelope_documents
        ];

        return $args;
    }
}
