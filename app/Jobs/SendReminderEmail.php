<?php

namespace App\Jobs;

use App\Mail\ReminderMail;
use App\Models\Planning;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $planning;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Planning $planning)
    {
        $this->planning = $planning;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Mail::to($this->planning->user->email)->send(new ReminderMail($this->planning));
    }
}
