<?php

namespace Example\Services\Examples\eSignature;

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

class BulkSendEnvelopesService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @param $clientService
     * @param $demoDocsPath
     * @return string
     */
    # ***DS.snippet.0.start
    public static function worker(array $args, $clientService, $demoDocsPath): string
    {

        $bulk_envelopes_api = $clientService->getBulkEnvelopesApi();
        $envelope_api = $clientService->getEnvelopeApi();

        # Step 3. Submit a bulk list
        $bulk_sending_list = BulkSendEnvelopesService::createBulkSendingList($args["signers"]);
        $bulk_list = $bulk_envelopes_api->createBulkSendList($args["account_id"], $bulk_sending_list);
        $bulk_list_id = $bulk_list["list_id"];

        # Step 4. Create an envelope
        $envelope_definition = BulkSendEnvelopesService::make_envelope($args, $demoDocsPath);
        $envelope = $envelope_api->createEnvelope($args["account_id"], $envelope_definition);
        $envelope_id = $envelope["envelope_id"];

        # Step 5. Attach your bulk list ID to the envelope
        $text_custom_fields = new TextCustomField([
            "name" => "mailingListId",
            "required" => "false",
            "show" => "false",
            "value" => $bulk_list_id
        ]);

        $custom_fields = new CustomFields([
            "list_custom_fields" => [],
            "text_custom_fields" => [$text_custom_fields]
        ]);

        $envelope_api->createCustomFields($args["account_id"], $envelope_id, $custom_fields);

        # Step 6. Add placeholder recipients
        $signer = new Signer([
            'name' => 'Multi Bulk Recipient::signer',
            'email' => 'multiBulkRecipients-signer@docusign.com',
            'role_name' => "signer",
            'note' => "",
            'routing_order' => '1',
            'status' => 'created',
            'delivery_method' => 'Email',
            'recipient_id' => '12', # Represents your {RECIPIENT_ID}
            'recipient_type' => "signer"
        ]);

        $cc = new Signer([
            'name' => 'Multi Bulk Recipient::cc',
            'email' => 'multiBulkRecipients-cc@docusign.com',
            'role_name' => "cc",
            'note' => "",
            'routing_order' => '1',
            'status' => 'created',
            'delivery_method' => 'Email',
            'recipient_id' => '13', # Represents your {RECIPIENT_ID}
            'recipient_type' => "signer"
        ]);

        $recipients = new Recipients(['signers' => [$signer, $cc]]);
        $envelope_api->createRecipient($args['account_id'], $envelope_id, $recipients);

        # Step 7. Initiate bulk send
        $bulk_send_request = new BulkSendRequest(['envelope_or_template_id' => $envelope_id]);

        $batch = $bulk_envelopes_api->createBulkSendRequest(
            $args["account_id"],
            $bulk_list_id,
            $bulk_send_request
        );

        # Step 8. Confirm successful batch send
        # Exceptions will be caught by the calling function
        try {
            $results = $bulk_envelopes_api->getBulkSendBatchStatus(
                $args['account_id'],
                $batch['batch_id']
            );
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $results;
    }

    /**
     *  Create bulk sending list
     *
     * @param  $signers array
     * @return BulkSendingList -- returns a bulk sending list
     */
    public static function createBulkSendingList(array $signers): BulkSendingList
    {
        # 1. Create recipient objects with signers
        # 2. Create recipient objects with ccs
        # 3. Create bulk copies objects
        # 4. Create the bulk sending list object

        $bulk_copies = [];
        foreach ($signers as $signer) {
            $recipient_1 = new BulkSendingCopyRecipient([
                "role_name" => "signer",
                "tabs" => [],
                "name" => $signer["signer_name"],
                "email" => $signer["signer_email"]
            ]);

            $recipient_2 = new BulkSendingCopyRecipient([
                "role_name" => "cc",
                "tabs" => [],
                "name" => $signer["cc_name"],
                "email" => $signer["cc_email"]
            ]);

            $bulk_copy = new BulkSendingCopy([
                "recipients" => [$recipient_1, $recipient_2],
                "custom_fields" => []
            ]);

            array_push($bulk_copies, $bulk_copy);
        }

        $bulk_sending_list = new BulkSendingList(["name" => "sample"]);
        $bulk_sending_list->setBulkCopies($bulk_copies);

        return $bulk_sending_list;
    }

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return EnvelopeDefinition -- returns an envelope definition
     */
    public static function make_envelope(array $args, $demoDocsPath): EnvelopeDefinition
    {
        # Document 1 (PDF) has tag /sn1/
        #
        # The envelope has one recipient.
        # recipient 1 - signer
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
            'email' => $args['signers'][0]['signer_email'], 'name' => $args['signers'][0]['signer_name'],
            'recipient_id' => "1", 'routing_order' => "1",
        ]);

        # Create a SignHere tab (field on the document)
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
            'status' => "sent" # Requests that the envelope be created and sent
        ]);
    }

    # ***DS.snippet.0.end
}
