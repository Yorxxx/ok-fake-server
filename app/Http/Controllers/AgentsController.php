<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Transformers\AgentsTranformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use JWTAuth;
use Exception;

class AgentsController extends AuthController
{

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

    /**
     * Shows an account by its number
     * @param Request $request
     * @POST('/api/accounts/by_number')
     * @Request({account="foo"})
     */
    public function show(Request $request) {

        $user = $this->getUserFromToken();

        try {
            $this->validate($request, [
                'account' => 'required|max:255'
            ]);
            $agent = Agent::where('account', $request->get('account'))
                ->where('user_id', $user->id)
                ->first();
            if (!$agent) {
                throw new ModelNotFoundException();
            }
            return $this->item($agent, new AgentsTranformer);

        } catch(ValidationException $e) {
            return $this->response()->errorBadRequest("Missing or invalid param: account");
        } catch (ModelNotFoundException $e) {
            return $this->response()->errorNotFound();
        } catch(Exception $e) {
            return $this->response()->errorInternal();
        }
    }
}
