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

class AgentsController extends AuthController
{
    use Helpers;

    /**
     * Returns the agents for the current user
     * @GET('/api/agents')
     * @Response(200, $agents)
     */
    public function getAgents() {
        $user = $this->getUserFromToken();
        $data = $user->agents;
        return $this->collection($data, new AgentsTranformer, ['key' => 'results']);
    }

    /**
     * Stores a new agent for the current user
     * @POST('/api/agents')
     * @Response(200, "accepted")
     * @return \Dingo\Api\Http\Response
     */
    public function store(Request $request) {
        $user = $this->getUserFromToken();
        try {
            $this->validate($request, [
                'account'   => 'required|max:255',
                'name'      => 'required',
                'phone'     => 'required'
            ]);
            $values = $request->all();
            $values['user_id'] = $user->id;

            $transformer = new AgentsTranformer;

            if ($agent = Agent::create($transformer->mapFromRequest($values))) {
                return $this->response->item($agent, new AgentsTranformer);
            }
        } catch (Exception $e) {
            return $this->response->errorBadRequest();
        }
        return $this->response->errorBadRequest();
    }
}
