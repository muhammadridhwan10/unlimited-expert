<?php
// app/Mail/PsychotestScheduled.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\PsychotestSchedule;

class PsychotestScheduled extends Mailable
{
    use Queueable, SerializesModels;

    public $schedule;
    public $plainPassword;

    public function __construct(PsychotestSchedule $schedule, $plainPassword)
    {
        $this->schedule = $schedule;
        $this->plainPassword = $plainPassword;
    }

    public function build()
    {
        return $this->subject('Psychotest/Assessment Test - ' . $this->schedule->candidates->name)
                    ->view('email.psychotest-scheduled')
                    ->with([
                        'candidateName' => $this->schedule->candidates->name,
                        'jobTitle' => $this->schedule->candidates->jobs->title ?? 'N/A',
                        'username' => $this->schedule->username,
                        'password' => $this->plainPassword,
                        'startTime' => $this->schedule->start_time->format('d M Y H:i'),
                        'endTime' => $this->schedule->end_time->format('d M Y H:i'),
                        'duration' => $this->schedule->duration_minutes,
                        'instructions' => $this->schedule->instructions,
                        'testUrl' => route('psychotest.test.login'),
                    ]);
    }
}