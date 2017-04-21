<?php
/**
 * Created by PhpStorm.
 * User: jorge
 * Date: 20/04/17
 * Time: 14:35
 */

namespace App;


use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\BrowserKitTestCase;

class UpdateTransactionsTaskTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * If there are no transactions, returns 0
     */
    public function given_noTransactionsToUpdate_When_Update_Then_ReturnsZero() {

        $task = new UpdateTransactionsTask;

        // Act
        $result = $task->update();

        // Assert
        self::assertEquals(0, $result);
    }

    /**
     * @test
     * Should not update state for those in_process transactions that have been created recently
     */
    public function given_inprocessTransactionJustCreated_When_Update_Then_DoesNothing() {

        $user = factory(User::class)->create();
        factory(Agent::class)->create();
        factory(Account::class)->create([
            'user_id'       => $user->id
        ]);

        // Create a ton of inprocess transactions, but none of them should be updated
        for ($i = 0; $i<100; $i++) {
            factory(Transaction::class)->create([
                'state'         => 5,
                'date_creation'  => Carbon::now()
            ]);
        }

        $task = new UpdateTransactionsTask;

        // Act
        $result = $task->update();

        // Assert
        self::assertEquals(0, $result);
        // Check that no item has been updated

        $all_items = Transaction::all();
        foreach ($all_items as $item) {
            self::assertNotEquals(3, $item->state);
        }
    }

    /**
     * @test
     * Should update the in_process transactions that were not created recently
     */
    public function given_inprocessTransactionCreatedTwoHoursAgo_When_Update_Then_UpdatesData() {

        $user = factory(User::class)->create();
        factory(Agent::class)->create();
        factory(Account::class)->create([
            'user_id'       => $user->id
        ]);

        factory(Transaction::class)->create([
            'state'     => 0
        ]);
        factory(Transaction::class)->create([
            'state'     => 1
        ]);
        factory(Transaction::class)->create([
            'state'     => 2
        ]);
//        $completed = factory(Transaction::class)->create([
//            'state'     => 3
//        ]);
        factory(Transaction::class)->create([
            'state'     => 4
        ]);
        $updated_at = Carbon::create("2000", "06", "20");
        $in_process = factory(Transaction::class)->create([
            'state'         => 5,
            'date_creation'  => Carbon::now()->subHour(2),
            'updated_at'     => $updated_at
        ]);
        factory(Transaction::class)->create([
            'state'     => 6
        ]);
        factory(Transaction::class)->create([
            'state'     => 7
        ]);
        factory(Transaction::class)->create([
            'state'     => 9
        ]);
        factory(Transaction::class)->create([
            'state'     => 11
        ]);

        $task = new UpdateTransactionsTask;

        // Act
        $result = $task->update();

        // Assert
        self::assertEquals(1, $result);
        // Check that there is now no in_process item
        $inprocess_items = Transaction::where('state', 5)->get();
        self::assertEquals(0, count($inprocess_items));
        // Check there is a completed item
        $completed_items = Transaction::where('state', 3)->get();
        self::assertEquals(1, count($completed_items));
        self::assertEquals($in_process->id, $completed_items[0]->id);
        self::assertTrue($completed_items[0]->updated_at > $updated_at);
        self::assertTrue($completed_items[0]->updated_at->eq($completed_items[0]->date_end));
    }

}
