<?php


namespace Example\Controllers\Examples\Rooms;


use DocuSign\Rooms\Api\RoomsApi\GetRoomsOptions;
use DocuSign\Rooms\Client\ApiException;
use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;
use Example\Services\Examples\Rooms\GetRoomsWithFiltersService;

class EG005GetRoomsWithFilters extends \Example\Controllers\RoomsApiBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "reg005";  # reference (and url) for this example
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new RoomsApiClientService($this->args);
        $this->routerService = new RouterService();
        $rooms = GetRoomsWithFiltersService::getRooms($this->routerService, $this->args, $this->clientService, $this->eg);
        parent::controller($this->eg, $this->routerService, basename(__FILE__), null, $rooms);
    }
    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return RoomSummaryList for specified time period
     *
     * @return void
     */
    function createController()
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = GetRoomsWithFiltersService::worker($this->args, $this->clientService);

            if ($results) {
                $start_date = $this->args['start_date'];
                $end_date = $this->args['end_date'];
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Rooms filtered by date",
                    "Rooms that have had their field data, updated within the time period between $start_date and $end_date",
                    "Results from the Rooms::GetRooms methods",
                    json_encode(json_encode($results))
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
        $start_date = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['start_date']);
        $end_date = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['end_date']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
    }
}