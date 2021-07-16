<?php


namespace Example\Controllers\Examples\Rooms;


use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Model\ExternalFormFillSessionForCreate;
use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;
use Example\Services\Examples\Rooms\CreateExternalFormFillSessionService;

class EG006CreateExternalFormFillSession extends \Example\Controllers\RoomsApiBaseController
{

    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "reg006";  # reference (and url) for this example
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
        $rooms = CreateExternalFormFillSessionService::getRooms($this->clientService, $this->routerService, $this->args, $this->eg);
        parent::controller($this->eg, $this->routerService, basename(__FILE__), null, $rooms);
    }
    /**
     * 1. Check the token
     * 2. Render new form if room were selected
     * 3.
     * 4. Return RoomSummaryList for specified time period
     *
     * @return void
     */
    function createController()
    {
        $minimum_buffer_min = 3;
        $room_id = $this->args['room_id'];
        $form_id = $this->args['form_id'];
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            if ($room_id && !$form_id) {
                $room = CreateExternalFormFillSessionService::getRoom(
                    $room_id,
                    $this->routerService,
                    $this->clientService,
                    $this->args,
                    $this->eg);
                $room_documents = CreateExternalFormFillSessionService::getDocuments(
                    $room_id,
                    $this->routerService,
                    $this->clientService,
                    $this->args,
                    $this->eg);
                $room_name = $room['name'];
                $room_forms = array_values(
                    array_filter($room_documents, function($f) { return $f['docu_sign_form_id']; })
                );

                $GLOBALS['twig']->display($this->routerService->getTemplate($this->eg), [
                    'title' => $this->routerService->getTitle($this->eg),
                    'forms' => $room_forms,
                    'room_id' => $room_id,
                    'room_name' => $room_name,
                    'source_file' => basename(__FILE__),
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . basename(__FILE__),
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $this->eg,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                ]);
            }
            else {
                $results = CreateExternalFormFillSessionService::worker($this->args, $this->clientService);

                if ($results) {
                    $results = json_decode((string)$results, true);
                    $this->clientService->showDoneTemplate(
                        "Create an external form fill session",
                        "Create an external form fill session",
                        "Results of Rooms::createExternalFormFillSession",
                        json_encode(json_encode($results))
                    );
                }
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
        $form_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['form_id']);
        $room_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['room_name']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'room_id' => $room_id,
            'form_id' => $form_id,
            'room_name' => $room_name,
        ];
    }
}
