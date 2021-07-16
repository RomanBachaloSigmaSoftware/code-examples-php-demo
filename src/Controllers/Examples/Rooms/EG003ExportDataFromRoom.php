<?php


namespace Example\Controllers\Examples\Rooms;


use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;
use DocuSign\Rooms\Client\ApiException;
use Example\Services\Examples\Rooms\ExportDataFromRoomService;

class EG003ExportDataFromRoom extends \Example\Controllers\RoomsApiBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "reg003";  # reference (and url) for this example
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
        $rooms = ExportDataFromRoomService::getRooms(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this->eg);
        parent::controller($this->eg, $this->routerService, basename(__FILE__), null, $rooms);
    }
    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return RoomFormFieldData for selected room
     *
     * @return void
     */
    function createController()
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = ExportDataFromRoomService::worker($this->args, $this->clientService);

            if ($results) {
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Field data associated with a room",
                    "Field data associated with a room",
                    "Results from the Rooms::GetRoomFieldData method",
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
        $room_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['room_id']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'room_id' => $room_id
        ];
    }
}
