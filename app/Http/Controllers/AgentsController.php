<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Transformers\AgentsTranformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use JWTAuth;
use App\User;

class AgentsController extends Controller
{
    use Helpers;

    /**
     * Returns the agents for the current user
     * @GET('/api/agents')
     * @Response(200, $agents)
     */
    public function getAgents() {

        try {
            $token = JWTAuth::getToken();
            if (!$user = JWTAuth::toUser($token)) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        $data = $user->agents;
        //Agent::where('user_id', $user->id)->get();
        return $this->collection($data, new AgentsTranformer, ['key' => 'results']);
    }
}
