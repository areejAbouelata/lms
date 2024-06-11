<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\User;

class SendFCMNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user_list;
    public $data;
    public $timeout = 600;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $user_list)
    {
        $this->user_list = $user_list;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (count($this->user_list) > 1000) {
            foreach (array_chunk($this->user_list, 1000, true) as $chunk) {
                pushFcmNotes($this->data, $chunk);
            }
        } else {
            pushFcmNotes($this->data, $this->user_list);
        }
    }
}
