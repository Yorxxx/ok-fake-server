<?php
namespace App\Transformers;

use App\Account;
use App\Agent;
use App\Setting;
use League\Fractal\TransformerAbstract;

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
            'phone'             => $phone
        ];
    }
}