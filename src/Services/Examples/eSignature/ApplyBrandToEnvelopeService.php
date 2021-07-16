<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;

class ApplyBrandToEnvelopeService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @return array ['redirect_url']
     */
    # ***DS.snippet.0.start
    public static function worker(array $args, $demoDocsPath, $clientService): array
    {
        # Step 3. Construct the request body
        $envelope_definition = ApplyBrandToEnvelopeService::make_envelope($args["envelope_args"], $demoDocsPath);

        # Step 4. Call the eSignature REST API
        $envelope_api = $clientService->getEnvelopeApi();
        $results = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);

        return ['envelope_id' => $results->getEnvelopeId()];
    }

    /**
     *  Create the envelope definition
     *  Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @param $demoDocsPath
     * @return EnvelopeDefinition -- returns an envelope definition
     */
    public static function make_envelope(array $args, $demoDocsPath): EnvelopeDefinition
    {
        # Document 1 (PDF) has tag /sn1/
        #
        # The envelope has one recipient:
        # Recipient 1 - signer
        #
        # Read the file
        $content_bytes = file_get_contents($demoDocsPath . $GLOBALS['DS_CONFIG']['doc_pdf']);
        $base64_file_content = base64_encode($content_bytes);

        # Create the document model
        $document = new Document([ # Create the DocuSign document object
            'document_base64' => $base64_file_content,
            'name' => 'Example document', # Can be different from actual file name
            'file_extension' => 'pdf', # Many different document types are accepted
            'document_id' => 1 # A label used to reference the doc
        ]);

        # Create the signer recipient model
        $signer = new Signer([ # The signer
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'recipient_id' => "1", 'routing_order' => "1",
            # Setting the client_user_id marks the signer as embedded
            'client_user_id' => $args['signer_client_id']
        ]);

        # Create a sign_here tab (field on the document)
        $sign_here = new SignHere([ # DocuSign SignHere field/tab
            'anchor_string' => '/sn1/', 'anchor_units' => 'pixels',
            'anchor_y_offset' => '10', 'anchor_x_offset' => '20'
        ]);

        # Add the tabs model (including the sign_here tab) to the signer
        # The Tabs object takes arrays of the different field/tab types
        $signer->settabs(new Tabs(['sign_here_tabs' => [$sign_here]]));

        # Next, create the top-level envelope definition and populate it
        return new EnvelopeDefinition([
            'email_subject' => "Please sign this document sent from the PHP SDK",
            'documents' => [$document],
            # The Recipients object takes arrays for each recipient type
            'recipients' => new Recipients(['signers' => [$signer]]),
            'status' => "sent", # Request that the envelope be created and sent
            'brand_id' => $args["brand_id"], # Apply selected Brand to envelope
        ]);
    }
    # ***DS.snippet.0.end
}
