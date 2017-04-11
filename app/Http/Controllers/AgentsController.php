<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Transformers\AgentsTranformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use JWTAuth;
use App\User;
use Exception;

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

    /**
     * Stores a new agent for the current user
     * @POST('/api/agents')
     * @Response(200, "accepted")
     * @return \Dingo\Api\Http\Response
     */
    public function store(Request $request) {
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
        try {
            $this->validate($request, [
                'account'   => 'required|max:255',
                'name'      => 'required',
                'phone'     => 'required'
            ]);
            $values = $request->all();
            $values['user_id'] = $user->id;

            $transformer = new AgentsTranformer;

            if ($agent = Agent::Create($transformer->mapFromRequest($values))) {
                return $this->response->item($agent, new AgentsTranformer);
            }
        } catch (Exception $e) {
            return $this->response->errorBadRequest();
        }
        return $this->response->errorBadRequest();

    }
}
