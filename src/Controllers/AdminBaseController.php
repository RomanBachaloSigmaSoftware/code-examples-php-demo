<?php

namespace Example\Controllers;

use Example\Services\RouterService;
abstract class AdminBaseController extends BaseController
{
    /**
     * Admin base controller
     * @param $eg string
     * @param $routerService RouterService
     * @param $basename string|null
     * @param $args array|null
     * @return void
     */
    public function controller(
        string $eg,
        RouterService $routerService,
        $basename = null,
        $args = null
    ): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController($eg, $routerService, $basename, $args);
        };
        if ($method == 'POST') {
            $routerService->check_csrf();
            $this->createController();
        };
    }

    /**
     * Show the example's form page
     * @param $eg string
     * @param $routerService RouterService
     * @param $basename string|null
     * @param $args array|null
     * @return void
     */
    private function getController(
        string $eg,
        RouterService $routerService,
        ?string $basename,
        ?array $args
    ): void
    {
        $exportId = isset($_SESSION['export_id']);
        $importId = isset($_SESSION['import_id']);
        if ($this->isHomePage($eg)){
            $GLOBALS['twig']->display($eg . '.html', [
                'title' => $this->homePageTitle($eg),
                'show_doc' => false
            ]);
        } else {
            if ($routerService->ds_token_ok()) {
                $GLOBALS['twig']->display($routerService->getTemplate($eg), [
                    'title' => $routerService->getTitle($eg),
                    'source_file' => $basename,
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $eg,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                    'args' => $args,
                    'export_id' => $exportId,
                    'import_id' => $importId
                ]);
            }
            else {
                # Save the current operation so it will be resumed after authentication
                $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $eg;
                header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
                exit;
            }
        }
    }

    /**
     * Declaration for the base controller creator. Each creator should be described in specific Controller
     */
    abstract function createController();
}
