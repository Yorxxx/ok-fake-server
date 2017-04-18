<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Transformers\AgentsTranformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JWTAuth;
use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        try {
            $user = $this->getUserFromToken();

            $rules = [
                'account'   => 'required|max:255',
                'name'      => 'required',
                'phone'     => 'required'
            ];

            $v = Validator::make($request->all(), $rules);
            if ($v->fails()) {
                throw new BadRequestHttpException($v->getMessageBag()->first());
            }

            $values = $request->all();

            $values['user_id'] = $user->id;

            $transformer = new AgentsTranformer;

            return $this->response->item(Agent::create($transformer->mapFromRequest($values)), new AgentsTranformer);
        } catch (BadRequestHttpException $e) {
            return $this->response->errorBadRequest($e->getMessage());
        } catch(Exception $e) {
            return $this->response()->errorInternal($e->getMessage());
        }
    }

    /**
     * Shows an account by its number
     * @param Request $request
     * @POST('/api/accounts/by_number')
     * @Request({account="foo"})
     */
    public function show(Request $request) {

        try {
            $user = $this->getUserFromToken();

            $rules = [
                'account'   => 'required|max:255',
            ];

            $v = Validator::make($request->all(), $rules);
            if ($v->fails()) {
                throw new BadRequestHttpException($v->getMessageBag()->first());
            }
            $agent = Agent::where('account', $request->get('account'))
                ->where('user_id', $user->id)
                ->first();
            if (!$agent) {
                throw new ModelNotFoundException();
            }
            return $this->item($agent, new AgentsTranformer);

        } catch(BadRequestHttpException $e) {
            return $this->response()->errorBadRequest($e->getMessage());
        } catch (ModelNotFoundException $e) {
            return $this->response()->errorNotFound($e->getMessage());
        }
        // @codeCoverageIgnoreStart
        catch(Exception $e) {
            return $this->response()->errorInternal();
        }
        // @codeCoverageIgnoreEnd
    }
}
