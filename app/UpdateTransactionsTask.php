<?php

namespace App;

use Carbon\Carbon;

class UpdateTransactionsTask {

    public function update() {

        $filter_date = Carbon::now()->subHour();

        $updatable_data = Transaction::where('state', 5)
            //->where('date_creation', '<', $filter_date->getTimestamp()*1000)
            ->get();

        $items = [];
        $current_time = Carbon::now();
        foreach ($updatable_data as $data) {
            if ($current_time->diffInHours($data->date_creation) > 1){
                array_push($items, $data);
                $data->update([
                    'state'         => 3,
                    'updated_at'    => $current_time,
                    'date_end'      => $current_time
                ]);
            }
        }

        return count($items);
    }
}