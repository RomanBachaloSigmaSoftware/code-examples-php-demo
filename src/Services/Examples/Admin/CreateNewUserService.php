<?php

namespace Example\Services\Examples\Admin;

use DocuSign\OrgAdmin\Api\UsersApi;
use DocuSign\OrgAdmin\Client\ApiException;
use DocuSign\OrgAdmin\Model\NewUserRequestAccountProperties;
use DocuSign\OrgAdmin\Model\NewUserResponse;
use DocuSign\OrgAdmin\Model\PermissionProfileRequest;
use NewUserRequest as GlobalNewUserRequest;
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Client\ApiClient.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Client\ApiException.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Configuration.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Api\UsersApi.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Model\NewUserRequest.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Model\NewUserRequestAccountProperties.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Model\PermissionProfileRequest.php";

class CreateNewUserService
{
    /**
     * Method to add a new user to your organization.
     * @param $organizationId
     * @param $userData
     * @param $clientService
     * @return NewUserResponse
     * @throws ApiException
     */
    public static function addActiveUser($organizationId, $userData, $clientService): NewUserResponse
    {
        $apiClient = $clientService->getApiClient();

        $usersApi = new UsersApi($apiClient);
        $accountId = $GLOBALS['DS_CONFIG']['account_id'];

        $premissionProfile = new PermissionProfileRequest([
            'id' => $GLOBALS['DS_CONFIG']['premissionProfile_id'],
            'name' => $GLOBALS['DS_CONFIG']['premissionProfile_name']
        ]);

        $nacountInfo = new NewUserRequestAccountProperties([
            'id' => $accountId,
            'permission_profile' => $premissionProfile
        ]);

        $request = new GlobalNewUserRequest([
            'user_name' => $userData['Name'],
            'first_name' => $userData['FirstName'],
            'last_name' => $userData['LastName'],
            'email' => $userData['Email'],
            'default_account_id' => $accountId,
            'accounts' => array($nacountInfo),
            'auto_activate_memberships' => false
        ]);

        return $usersApi->createUser($organizationId, $request);
    }
}
