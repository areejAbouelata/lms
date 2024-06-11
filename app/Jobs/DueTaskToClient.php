<?php

namespace App\Jobs;

use App\Mail\DueTaskToAdmin;
use App\Models\ActivityUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DueTaskToClient implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $data;
    public $admin_data;

    public function __construct($data, $admin_data)
    {
        $this->data = $data;
        $this->admin_data = $admin_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $if_true_send = ActivityUser::where([
            'user_id' => $this->data['user_id'],
            'activity_id' => $this->data['activity_id'],
            'status' => 'pending'
        ])->exists();
        if ($if_true_send) {
            Mail::to($this->data['email'])->send(new \App\Mail\DueTaskToClient($this->data));
            foreach (User::whereIn('user_type', ['admin', 'super_admin'])->get() as $user) {
                $admin_data = $this->admin_data;
                $admin_data['name'] = $user->full_name;
                $admin_data['admin_name'] = $user->full_name;
                Mail::to($user->email)->send(new DueTaskToAdmin($admin_data));
            }
        }

    }
}
