<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\ReturnUrlRequest;

class EmbeddedSendingService
{
    /**
     * Do the work of the example
     * 1. Create the envelope with "created" (draft) status
     * 2. Send the envelope
     * 3. Get the SenderView url
     *
     * @param  $args array
     * @return array ['redirect_url']
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    public static function worker(array $args, $clientService, $demoDocsPath): array
    {
        # 1. Create the envelope as a draft using eg002's worker
        # Exceptions will be caught by the calling function
        $args['envelope_args']['status'] = 'created';
        $results = SigningViaEmailService::worker($args, $clientService, $demoDocsPath);
        $envelope_id= $results['envelope_id'];

        # 2. Create sender view
        $view_request = new ReturnUrlRequest(['return_url' => $args['ds_return_url']]);
        $envelope_api = $clientService->getEnvelopeApi();
        $results = $envelope_api->createSenderView($args['account_id'], $envelope_id, $view_request);

        # Switch to the Recipients / Documents view if requested by the user in the form
        $url = $results['url'];
        if ($args['starting_view'] == "recipient") {
            $url = str_replace('send=1', 'send=0', $url);
        }

        return ['envelope_id' => $envelope_id, 'redirect_url' =>  $url];
    }
    # ***DS.snippet.0.end

}
