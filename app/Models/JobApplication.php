<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job',
        'name',
        'email',
        'phone',
        'profile',
        'resume',
        'cover_letter',
        'dob',
        'gender',
        'country',
        'state',
        'city',
        'stage',
        'order',
        'skill',
        'rating',
        'is_archive',
        'custom_question',
        'kk',
        'ktp',
        'transkrip_nilai',
        'ipk',
        'ijazah',
        'year_graduated',
        'latest_education',
        'major',
        'university',
        'latest_work_experience',
        'length_of_last_job',
        'certificate',
        'created_by',
    ];

    public function jobs()
    {
        return $this->hasOne('App\Models\Job', 'id', 'job');
    }

    public function stage_status()
    {
        return $this->hasOne('App\Models\JobStage', 'id', 'stage');
    }

    public static $ipk = [
        '0.0' => '0.0',
        '0.1' => '0.1',
        '0.2' => '0.2',
        '0.3' => '0.3',
        '0.4' => '0.4',
        '0.5' => '0.5',
        '0.6' => '0.6',
        '0.7' => '0.7',
        '0.8' => '0.8',
        '0.9' => '0.9',
        '1.0' => '1.0',
        '1.1' => '1.1',
        '1.2' => '1.2',
        '1.3' => '1.3',
        '1.4' => '1.4',
        '1.5' => '1.5',
        '1.6' => '1.6',
        '1.7' => '1.7',
        '1.8' => '1.8',
        '1.9' => '1.9',
        '2.0' => '1.0',
        '2.1' => '2.1',
        '2.2' => '2.2',
        '2.3' => '2.3',
        '2.4' => '2.4',
        '2.5' => '2.5',
        '2.6' => '2.6',
        '2.7' => '2.7',
        '2.8' => '2.8',
        '2.9' => '2.9',
        '3.0' => '3.0',
        '3.1' => '3.1',
        '3.2' => '3.2',
        '3.3' => '3.3',
        '3.4' => '3.4',
        '3.5' => '3.5',
        '3.6' => '3.6',
        '3.7' => '3.7',
        '3.8' => '3.8',
        '3.9' => '3.9',
        '4.0' => '4.0'
    ];

}
