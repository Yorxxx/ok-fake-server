<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Transformers\AgentsTranformer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\UnauthorizedException;
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
     * Updates an existing agent
     * @param Request $request the request
     * @param $id Integer the identifier of the agent to update
     * @return \Dingo\Api\Http\Response|void
     */
    public function update(Request $request, $id) {

        try {
            $user = $this->getUserFromToken();

            $existing_data = Agent::where('id', $id)->first();

            if ($existing_data == null) {
                throw new ModelNotFoundException("Agent not found");
            }
            if (strcmp($user->id, $existing_data->user_id) != 0) {
                throw new UnauthorizedException("User does not have to modify another user agent");
            }

            $transformer = new AgentsTranformer;
            $values = $transformer->mapFromRequest($request->all());
            $values['updated_at'] = Carbon::now();

            $existing_data->update($values);

//            $rules = [
//                'name'      => 'required',
//                'phone'     => 'required'
//            ];
//
//            $v = Validator::make($request->all(), $rules);
//            if ($v->fails()) {
//                throw new BadRequestHttpException($v->getMessageBag()->first());
//            }
//
//            $values = $request->all();
//
//            $values['user_id'] = $user->id;
//
//
             return $this->response->item($existing_data, new AgentsTranformer);
        } catch (BadRequestHttpException $e) {
            return $this->response->errorBadRequest($e->getMessage());
        } catch (ModelNotFoundException $e) {
            return $this->response->errorNotFound($e->getMessage());
        } catch (UnauthorizedException $e) {
            return $this->response->errorForbidden($e->getMessage());
        }catch(Exception $e) {
            return $this->response()->errorInternal($e->getMessage());
        }
    }
}
