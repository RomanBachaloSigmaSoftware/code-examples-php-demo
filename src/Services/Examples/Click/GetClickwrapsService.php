<?php

namespace Example\Services\Examples\Click;

use Example\Services\ClickApiClientService;
use DocuSign\Click\Api\AccountsApi\GetClickwrapsOptions;
use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapVersionsResponse;

class GetClickwrapsService
{
    /**
     * Get account clickwraps
     * @param  $args array
     * @param ClickApiClientService $clientService
     * @return ClickwrapVersionsResponse
     */
    public static function getClickwraps(array $args, ClickApiClientService $clientService): ClickwrapVersionsResponse
    {
        try {
            # Step 3 Start
            $accountsApi = $clientService->accountsApi();
            $options = new GetClickwrapsOptions();
            $results = $accountsApi->getClickwraps($args['account_id'], $options);
            # Step 3 End
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        return $results;
    }
}
