<?php
/**
 * Created by PhpStorm.
 * User: jorge
 * Date: 11/04/17
 * Time: 14:39
 */

namespace App\Http\Controllers;


use App\User;
use Dingo\Api\Routing\Helpers;
use Exception;
use JWTAuth;

/**
 * Controller that implements methods for retrieving user from token
 * Class AuthController
 * @package App\Http\Controllers
 */
class AuthController extends Controller {

    use Helpers;

    /**
     * Returns the current user or null
     * @codeCoverageIgnore
     * @return User the user or null if no current user (or invalid token)
     * @throws Exception if failed retrieving user
     */
    public function getUserFromToken() {
        try {
            $token = JWTAuth::getToken();
            if ($user = JWTAuth::toUser($token)) {
                return $user;
            }
        } catch (Exception $e) {
            throw $e;
        }
        throw new Exception("User not found");
    }
}