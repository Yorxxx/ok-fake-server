<?php
namespace App\Transformers;

use App\Account;
use App\Agent;
use App\Setting;
use Illuminate\Validation\ValidationException;
use League\Fractal\TransformerAbstract;
use Illuminate\Http\Request;

class AgentsTranformer extends TransformerAbstract
{
    public function transform(Agent $agent)
    {

        $prefix = $phone = '';
        if ($agent->phone !== null) {
            $phone_values = explode('-', $agent->phone);
            $prefix = array_values($phone_values)[0];
            $phone = array_values($phone_values)[1];
        }

        return [
            'id'                => $agent->id,
            'account'           => $agent->account,
            'owner'             => (bool)$agent->owner,
            'name'              => $agent->name,
            'email'             => $agent->email,
            'country'           => $agent->country,
            'prefix'            => $prefix,
            'phone'             => $phone,
            'user_id'           => $agent->user != null ? $agent->user->id : 0
        ];
    }

    /**
     * Maps the expected input request values into Laravel expected models
     * Does not validate any data. You should validate the incoming data before mapping
     * @param $values array containing the request data
     * @return array with mappable data
     */
    public function mapFromRequest($values) {
        if ($values == null)
            return null;

        return [
            'account'   => array_key_exists('account', $values) ? $values['account'] : '',
            'owner'     => array_key_exists('owner', $values) ? $values['owner'] : false,
            'name'      => $values['name'],
            'phone'     => '+'.$values['prefix'].'-'.$values['phone'],
            'email'     => array_key_exists('email', $values) ? $values['email'] : '',
            'country'   => array_key_exists('country', $values) ? $values['country'] : '',
            'user_id'   => array_key_exists('user_id', $values) ? $values['user_id'] : ''
        ];
    }
}