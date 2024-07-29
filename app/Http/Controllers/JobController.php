<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CustomQuestion;
use App\Models\Job;
use App\Models\Utility;
use App\Models\JobApplication;
use App\Models\JobApplicationNote;
use App\Models\JobCategory;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\University;
use Illuminate\Http\Request;
use App\Mail\JobApplyNotification;
use Illuminate\Support\Facades\Mail;

class JobController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('manage job'))
        {
            if(\Auth::user()->type = 'admin')
        {
            $jobs = Job::all();

            $data['total']     = Job::all()->count();
            $data['active']    = Job::where('status', 'active')->count();
            $data['in_active'] = Job::where('status', 'in_active')->count();

        }elseif(\Auth::user()->type = 'company')
        {
            $jobs = Job::all();

            $data['total']     = Job::all()->count();
            $data['active']    = Job::where('status', 'active')->count();
            $data['in_active'] = Job::where('status', 'in_active')->count();
        }
        else
        {
            $jobs = Job::where('created_by', '=', \Auth::user()->creatorId())->get();

            $data['total']     = Job::where('created_by', '=', \Auth::user()->creatorId())->count();
            $data['active']    = Job::where('status', 'active')->where('created_by', '=', \Auth::user()->creatorId())->count();
            $data['in_active'] = Job::where('status', 'in_active')->where('created_by', '=', \Auth::user()->creatorId())->count();

        }

        return view('job.index', compact('jobs', 'data'));

        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {

        if(\Auth::user()->type = 'admin')
        {
            $categories = JobCategory::all()->pluck('title', 'id');
            $categories->prepend('--', '');
    
            $branches = Branch::all()->pluck('name', 'id');
            $branches->prepend('All', 0);
    
            $status = Job::$status;
    
            $customQuestion = CustomQuestion::all();
    
        }elseif(\Auth::user()->type = 'company')
        {
            $categories = JobCategory::all()->pluck('title', 'id');
            $categories->prepend('--', '');
    
            $branches = Branch::all()->pluck('name', 'id');
            $branches->prepend('All', 0);
    
            $status = Job::$status;
    
            $customQuestion = CustomQuestion::all();
        }
        else
        {
            $categories = JobCategory::where('created_by', \Auth::user()->creatorId())->get()->pluck('title', 'id');
            $categories->prepend('--', '');
    
            $branches = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend('All', 0);
    
            $status = Job::$status;
    
            $customQuestion = CustomQuestion::where('created_by', \Auth::user()->creatorId())->get();
    
        }

        return view('job.create', compact('categories', 'status', 'branches', 'customQuestion'));
    }

    public function store(Request $request)
    {

        if(\Auth::user()->can('create job'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'title' => 'required',
                                   'branch' => 'required',
                                   'category' => 'required',
                                   'skill' => 'required',
                                   'position' => 'required|integer',
                                   'start_date' => 'required',
                                   'end_date' => 'required',
                                   'description' => 'required',
                                   'requirement' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $job                  = new Job();
            $job->title           = $request->title;
            $job->branch          = $request->branch;
            $job->category        = $request->category;
            $job->skill           = $request->skill;
            $job->position        = $request->position;
            $job->status          = $request->status;
            $job->start_date      = $request->start_date;
            $job->end_date        = $request->end_date;
            $job->description     = $request->description;
            $job->requirement     = $request->requirement;
            $job->code            = uniqid();
            $job->applicant       = !empty($request->applicant) ? implode(',', $request->applicant) : '';
            $job->visibility      = !empty($request->visibility) ? implode(',', $request->visibility) : '';
            $job->custom_question = !empty($request->custom_question) ? implode(',', $request->custom_question) : '';
            $job->created_by      = \Auth::user()->creatorId();
            $job->save();

            return redirect()->route('job.index')->with('success', __('Job  successfully created.'));
        }
        else
        {
            return redirect()->route('job.index')->with('error', __('Permission denied.'));
        }
    }

    public function show(Job $job)
    {
        $status          = Job::$status;
        $job->applicant  = !empty($job->applicant) ? explode(',', $job->applicant) : '';
        $job->visibility = !empty($job->visibility) ? explode(',', $job->visibility) : '';
        $job->skill      = !empty($job->skill) ? explode(',', $job->skill) : '';

        return view('job.show', compact('status', 'job'));
    }

    public function edit(Job $job)
    {

        if(\Auth::user()->type = 'admin')
        {
            $categories = JobCategory::get()->pluck('title', 'id');
            $categories->prepend('--', '');
    
            $branches = Branch::get()->pluck('name', 'id');
            $branches->prepend('All', 0);
    
            $status = Job::$status;
    
            $job->applicant       = explode(',', $job->applicant);
            $job->visibility      = explode(',', $job->visibility);
            $job->custom_question = explode(',', $job->custom_question);
    
            $customQuestion = CustomQuestion::get();
        }
        elseif(\Auth::user()->type = 'company')
        {
            $categories = JobCategory::get()->pluck('title', 'id');
            $categories->prepend('--', '');
    
            $branches = Branch::get()->pluck('name', 'id');
            $branches->prepend('All', 0);
    
            $status = Job::$status;
    
            $job->applicant       = explode(',', $job->applicant);
            $job->visibility      = explode(',', $job->visibility);
            $job->custom_question = explode(',', $job->custom_question);
    
            $customQuestion = CustomQuestion::get();
        }
        else
        {
            $categories = JobCategory::where('created_by', \Auth::user()->creatorId())->get()->pluck('title', 'id');
            $categories->prepend('--', '');
    
            $branches = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend('All', 0);
    
            $status = Job::$status;
    
            $job->applicant       = explode(',', $job->applicant);
            $job->visibility      = explode(',', $job->visibility);
            $job->custom_question = explode(',', $job->custom_question);
    
            $customQuestion = CustomQuestion::where('created_by', \Auth::user()->creatorId())->get();
        }

        return view('job.edit', compact('categories', 'status', 'branches', 'job', 'customQuestion'));
    }

    public function update(Request $request, Job $job)
    {
        if(\Auth::user()->can('edit job'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'title' => 'required',
                                   'branch' => 'required',
                                   'category' => 'required',
                                   'skill' => 'required',
                                   'position' => 'required|integer',
                                   'start_date' => 'required',
                                   'end_date' => 'required',
                                   'description' => 'required',
                                   'requirement' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $job->title           = $request->title;
            $job->branch          = $request->branch;
            $job->category        = $request->category;
            $job->skill           = $request->skill;
            $job->position        = $request->position;
            $job->status          = $request->status;
            $job->start_date      = $request->start_date;
            $job->end_date        = $request->end_date;
            $job->description     = $request->description;
            $job->requirement     = $request->requirement;
            $job->applicant       = !empty($request->applicant) ? implode(',', $request->applicant) : '';
            $job->visibility      = !empty($request->visibility) ? implode(',', $request->visibility) : '';
            $job->custom_question = !empty($request->custom_question) ? implode(',', $request->custom_question) : '';
            $job->save();

            return redirect()->route('job.index')->with('success', __('Job  successfully updated.'));
        }
        else
        {
            return redirect()->route('job.index')->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Job $job)
    {
        $application = JobApplication::where('job', $job->id)->get()->pluck('id');
        JobApplicationNote::whereIn('application_id', $application)->delete();
        JobApplication::where('job', $job->id)->delete();
        $job->delete();

        return redirect()->route('job.index')->with('success', __('Job  successfully deleted.'));
    }

    public function career($id, $lang)
    {
        $jobs= Job::where('status', 'active')->where('created_by', $id)->get();

        \Session::put('lang', $lang);

        \App::setLocale($lang);

        $companySettings['title_text']      = \DB::table('settings')->where('created_by', $id)->where('name', 'title_text')->first();
        $companySettings['footer_text']     = \DB::table('settings')->where('created_by', $id)->where('name', 'footer_text')->first();
        $companySettings['company_favicon'] = \DB::table('settings')->where('created_by', $id)->where('name', 'company_favicon')->first();
        $companySettings['company_logo']    = \DB::table('settings')->where('created_by', $id)->where('name', 'company_logo')->first();
        $languages                          = Utility::languages();

        $currantLang = \Session::get('lang');
        if(empty($currantLang))
        {
            $user        = User::find($id);
            $currantLang = !empty($user) && !empty($user->lang) ? $user->lang : 'en';
        }


        return view('job.career', compact('companySettings', 'jobs', 'languages', 'currantLang','id'));
    }

    public function jobRequirement($code, $lang)
    {
        $job = Job::where('code', $code)->first();
        if($job->status == 'in_active')
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        \Session::put('lang', $lang);

        \App::setLocale($lang);

        $companySettings['title_text']      = \DB::table('settings')->where('created_by', $job->created_by)->where('name', 'title_text')->first();
        $companySettings['footer_text']     = \DB::table('settings')->where('created_by', $job->created_by)->where('name', 'footer_text')->first();
        $companySettings['company_favicon'] = \DB::table('settings')->where('created_by', $job->created_by)->where('name', 'company_favicon')->first();
        $companySettings['company_logo']    = \DB::table('settings')->where('created_by', $job->created_by)->where('name', 'company_logo')->first();
        $languages                          = Utility::languages();

        $currantLang = \Session::get('lang');
        if(empty($currantLang))
        {
            $currantLang = !empty($job->createdBy) ? $job->createdBy->lang : 'en';
        }


        return view('job.requirement', compact('companySettings', 'job', 'languages', 'currantLang'));
    }

    public function jobApply($code, $lang)
    {
        \Session::put('lang', $lang);

        \App::setLocale($lang);

        $job                                = Job::where('code', $code)->first();
        $companySettings['title_text']      = \DB::table('settings')->where('created_by', $job->created_by)->where('name', 'title_text')->first();
        $companySettings['footer_text']     = \DB::table('settings')->where('created_by', $job->created_by)->where('name', 'footer_text')->first();
        $companySettings['company_favicon'] = \DB::table('settings')->where('created_by', $job->created_by)->where('name', 'company_favicon')->first();
        $companySettings['company_logo']    = \DB::table('settings')->where('created_by', $job->created_by)->where('name', 'company_logo')->first();

        $questions = CustomQuestion::where('created_by', $job->created_by)->get();

        $countries = Country::all();
        $univercity = University::orderBy('name', 'asc')->get();

        $languages = Utility::languages();

        $currantLang = \Session::get('lang');
        if(empty($currantLang))
        {
            $currantLang = !empty($job->createdBy) ? $job->createdBy->lang : 'en';
        }


        return view('job.apply', compact('companySettings', 'job', 'questions', 'languages', 'currantLang', 'countries', 'univercity'));
    }

    public function jobApplyData(Request $request, $code)
    {

            $currantLang = \Session::get('lang');
            if(empty($currantLang))
            {
                $currantLang = !empty($job->createdBy) ? $job->createdBy->lang : 'en';
            }

            $validator = \Validator::make(
                $request->all(), [
                                    'name' => 'required',
                                    'email' => 'required',
                                    'phone' => 'required',
                                    'profile' => 'mimes:jpeg,png,jpg|max:20480',
                                    'resume' => 'mimes:jpeg,png,jpg,pdf|max:20480',
                                    'kk' => 'mimes:jpeg,png,jpg,pdf,|max:20480',
                                    'ktp' => 'mimes:jpeg,png,jpg,pdf,|max:20480',
                                    'transkrip_nilai' => 'mimes:jpeg,png,jpg,pdf,|max:20480',
                                    'ijazah' => 'mimes:jpeg,png,jpg,pdf,|max:20480',
                                    'certificate' => 'mimes:jpeg,png,jpg,pdf,|max:20480',
                            ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $job = Job::where('code', $code)->first();

            if(!empty($request->profile))
            {
                $filenameWithExt = $request->file('profile')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('profile')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $dir        = storage_path('uploads/job/profile');
                $image_path = $dir . $filenameWithExt;

                if(\File::exists($image_path))
                {
                    \File::delete($image_path);
                }
                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }
                $path = $request->file('profile')->storeAs('uploads/job/profile/', $fileNameToStore);
            }

            if(!empty($request->resume))
            {
                $filenameWithExt1 = $request->file('resume')->getClientOriginalName();
                $filename1        = pathinfo($filenameWithExt1, PATHINFO_FILENAME);
                $extension1       = $request->file('resume')->getClientOriginalExtension();
                $fileNameToStore1 = $filename1 . '_' . time() . '.' . $extension1;

                $dir        = storage_path('uploads/job/resume');
                $image_path = $dir . $filenameWithExt1;

                if(\File::exists($image_path))
                {
                    \File::delete($image_path);
                }
                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }
                $path = $request->file('resume')->storeAs('uploads/job/resume/', $fileNameToStore1);
            }

            if(!empty($request->kk))
            {
                $filenameWithExt1   = $request->file('kk')->getClientOriginalName();
                $filename1          = pathinfo($filenameWithExt1, PATHINFO_FILENAME);
                $extension1         = $request->file('kk')->getClientOriginalExtension();
                $fileNameToStoreKK  = $filename1 . '_' . time() . '.' . $extension1;

                $dir        = storage_path('uploads/job/kk');
                $image_path = $dir . $filenameWithExt1;

                if(\File::exists($image_path))
                {
                    \File::delete($image_path);
                }
                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }
                $path = $request->file('kk')->storeAs('uploads/job/kk/', $fileNameToStoreKK);
            }

            if(!empty($request->ktp))
            {
                $filenameWithExt1   = $request->file('ktp')->getClientOriginalName();
                $filename1          = pathinfo($filenameWithExt1, PATHINFO_FILENAME);
                $extension1         = $request->file('ktp')->getClientOriginalExtension();
                $fileNameToStoreKTP = $filename1 . '_' . time() . '.' . $extension1;

                $dir        = storage_path('uploads/job/ktp');
                $image_path = $dir . $filenameWithExt1;

                if(\File::exists($image_path))
                {
                    \File::delete($image_path);
                }
                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }
                $path = $request->file('ktp')->storeAs('uploads/job/ktp/', $fileNameToStoreKTP);
            }

            if(!empty($request->transkrip_nilai))
            {
                $filenameWithExt1               = $request->file('transkrip_nilai')->getClientOriginalName();
                $filename1                      = pathinfo($filenameWithExt1, PATHINFO_FILENAME);
                $extension1                     = $request->file('transkrip_nilai')->getClientOriginalExtension();
                $fileNameToStoreTranskripNilai  = $filename1 . '_' . time() . '.' . $extension1;

                $dir        = storage_path('uploads/job/transkrip_nilai');
                $image_path = $dir . $filenameWithExt1;

                if(\File::exists($image_path))
                {
                    \File::delete($image_path);
                }
                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }
                $path = $request->file('transkrip_nilai')->storeAs('uploads/job/transkrip_nilai/', $fileNameToStoreTranskripNilai);
            }

            if(!empty($request->ijazah))
            {
                $filenameWithExt1       = $request->file('ijazah')->getClientOriginalName();
                $filename1              = pathinfo($filenameWithExt1, PATHINFO_FILENAME);
                $extension1             = $request->file('ijazah')->getClientOriginalExtension();
                $fileNameToStoreIjazah  = $filename1 . '_' . time() . '.' . $extension1;

                $dir        = storage_path('uploads/job/ijazah');
                $image_path = $dir . $filenameWithExt1;

                if(\File::exists($image_path))
                {
                    \File::delete($image_path);
                }
                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }
                $path = $request->file('ijazah')->storeAs('uploads/job/ijazah/', $fileNameToStoreIjazah);
            }

            if(!empty($request->certificate))
            {
                $filenameWithExt1           = $request->file('certificate')->getClientOriginalName();
                $filename1                  = pathinfo($filenameWithExt1, PATHINFO_FILENAME);
                $extension1                 = $request->file('certificate')->getClientOriginalExtension();
                $fileNameToStoreCertificate = $filename1 . '_' . time() . '.' . $extension1;

                $dir        = storage_path('uploads/job/certificate');
                $image_path = $dir . $filenameWithExt1;

                if(\File::exists($image_path))
                {
                    \File::delete($image_path);
                }
                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }
                $path = $request->file('certificate')->storeAs('uploads/job/certificate/', $fileNameToStoreCertificate);
            }

            $jobApplication                  = new JobApplication();
            $jobApplication->job             = $job->id;
            $jobApplication->name            = $request->name;
            $jobApplication->email           = $request->email;
            $jobApplication->phone           = $request->phone;
            $jobApplication->profile         = !empty($request->profile) ? $fileNameToStore : '';
            $jobApplication->resume          = !empty($request->resume) ? $fileNameToStore1 : '';
            $jobApplication->kk                         = !empty($request->kk) ? $fileNameToStoreKK : '';
            $jobApplication->ktp                        = !empty($request->ktp) ? $fileNameToStoreKTP : '';
            $jobApplication->transkrip_nilai            = !empty($request->transkrip_nilai) ? $fileNameToStoreTranskripNilai : '';
            $jobApplication->ijazah                     = !empty($request->ijazah) ? $fileNameToStoreIjazah : '';
            $jobApplication->certificate                = !empty($request->certificate) ? $fileNameToStoreCertificate : '';
            $jobApplication->cover_letter    = $request->cover_letter;
            $jobApplication->dob             = $request->dob;
            $jobApplication->gender          = $request->gender;
            $selectedCountry                 = Country::where('code', $request->selected_country)->first();
            $jobApplication->country         = $selectedCountry->name;
            $selectedState                   = State::where('district', $request->selected_state)->first();
            $jobApplication->state           = $selectedState->name;
            $selectedCity                    = City::find($request->selected_city);
            $jobApplication->city            = $selectedCity->name;
            $jobApplication->year_graduated            = $request->year_graduated;
            $jobApplication->last_education            = $request->last_education;
            $jobApplication->major                     = $request->major;
            $jobApplication->university                = $request->university;
            $jobApplication->latest_work_experience    = $request->latest_work_experience;
            $jobApplication->length_of_last_job        = $request->length_of_last_job;
            $jobApplication->ipk                       = $request->ipk;
            $jobApplication->custom_question           = json_encode($request->question);
            $jobApplication->created_by                = $job->created_by;
            $jobApplication->save();

            $user = User::where('type', 'admin')->first();
            $email = $user->email;
            Mail::to($email)->send(new JobApplyNotification($jobApplication));

            return redirect()->route('job.apply', [$code, $currantLang])->with('success', __('Job application successfully send.'));
    }

    public function getStatesByCountry(Request $request)
    {
        $countryCode = $request->input('country_code');
        $states = State::where('countrycode', $countryCode)->get();
        return response()->json($states);
    }

    public function getCitiesByState(Request $request)
    {
        $stateDistrict = $request->input('state_district');
        $cities = City::where('district', $stateDistrict)->get();
        return response()->json($cities);
    }


}
