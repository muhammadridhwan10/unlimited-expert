<?php

namespace App\Http\Controllers;

use App\Models\Mail\TicketSend;
use App\Models\Employee;
use App\Models\Mail\UserCreate;
use App\Models\Support;
use App\Models\SupportReply;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
        {
            $supports = Support::all();
            $countTicket      = Support::all()->count();
            $countOpenTicket  = Support::where('status', '=', 'open')->count();
            $countonholdTicket  = Support::where('status', '=', 'on hold')->count();
            $countCloseTicket = Support::where('status', '=', 'close')->count();
            return view('support.index', compact('supports','countTicket','countOpenTicket','countonholdTicket','countCloseTicket'));
        }
        elseif(\Auth::user()->type == 'staff IT')
        {
            $model            = Support::where('user', \Auth::user()->id);

            $supports         = $model->get();
            $countTicket      = $model->count();
            $countOpenTicket  = $model->where('status', '=', 'open')->count();
            $countonholdTicket = $model->where('status', '=', 'on hold')->count();
            $countCloseTicket = $model->where('status', '=', 'close')->count();
            return view('support.index', compact('supports','countTicket','countOpenTicket','countonholdTicket','countCloseTicket'));
        }
        else
        {
            $model            = Support::where('ticket_created', \Auth::user()->id);

            $supports         = $model->get();
            $countTicket      = $model->count();
            $countOpenTicket  = $model->where('status', '=', 'open')->count();
            $countonholdTicket = $model->where('status', '=', 'on hold')->count();
            $countCloseTicket = $model->where('status', '=', 'close')->count();
            return view('support.index', compact('supports','countTicket','countOpenTicket','countonholdTicket','countCloseTicket'));
        }

    }


    public function create()
    {
        $priority = [
            __('Low'),
            __('Medium'),
            __('High'),
            __('Critical'),
        ];
        $status = Support::$status;
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
        {
            $users  = User::where('type', 'staff IT')->get()->pluck('name', 'id');
            $users->prepend('Select Support', '');
        }
        else
        {
            $users  = User::where('type', 'staff IT')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $users->prepend('Select Support', '');
        }
        return view('support.create', compact('priority', 'users', 'status'));
    }


    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                            'subject' => 'required',
                            'priority' => 'required',
                            'attachment' => 'mimes:png,jpg,jpeg|max:20480'
                        ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $support              = new Support();
        $support->subject     = $request->subject;
        $support->user        = $request->user;
        $support->priority    = $request->priority;
        $support->ticket_code = date('hms');
        $support->status      = 'Open';

        if(!empty($request->attachment))
        {
            $fileName = time() . "_" . $request->attachment->getClientOriginalName();
            $request->attachment->storeAs('uploads/supports', $fileName, 's3');
            $support->attachment = $fileName;
        }
        $support->description    = $request->description;
        $support->created_by     = \Auth::user()->creatorId();
        $support->ticket_created = \Auth::user()->id;
        $support->save();

        $ticket_code = $support->ticket_code;
        $subject = $support->subject;
        $user = $support->user;
        $priority = $support->priority;
        $description = $support->description;
        $status = $support->status;

        $employee = Employee::where('user_id', $support->user)->first();

        $message = "Hi,\n";
        $message .= "*New Support Ticket Created*\n";
        $message .= "--------------------------------\n";
        $message .= "Ticket No.: *{$ticket_code}*\n";
        $message .= "Subject: *{$subject}*\n";
        $message .= "Priority: *{$priority}*\n";
        $message .= "Status: *{$status}*\n";
        $message .= "Description: {$description}\n";
        $message .= "~~~~~~~~~~~~~~~~~~~~\n";
        $message .= "Please follow up on this ticket as soon as possible. Thank you.";

        $whatsappNumber = '62' . $employee->phone;

        $whatsappUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . urlencode($message);

        return view('support.redirect_to_whatsapp', compact('whatsappUrl'));
    }



    public function show(Support $support)
    {
        //
    }


    public function edit(Support $support)
    {
        $priority = [
            __('Low'),
            __('Medium'),
            __('High'),
            __('Critical'),
        ];
        $status = Support::$status;
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
        {
            $users  = User::where('type', 'staff IT')->get()->pluck('name', 'id');
            $users->prepend('Select Support', '');
        }
        else
        {
            $users = User::where('type', 'staff IT')->get()->pluck('name', 'id');
            $users->prepend('Select Support', '');
        }
        

        return view('support.edit', compact('priority', 'users', 'support','status'));
    }


    public function update(Request $request, Support $support)
    {

        $validator = \Validator::make(
            $request->all(), [
                               'subject' => 'required',
                               'priority' => 'required',
                               'attachment' => 'mimes:png,jpg,jpeg|max:20480'
                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $support->subject  = $request->subject;
        $support->user     = $request->user;
        $support->priority = $request->priority;
        $support->status  = $request->status;
        // $support->end_date = $request->end_date;
        if(!empty($request->attachment))
        {
            $fileName = time() . "_" . $request->attachment->getClientOriginalName();
            $request->attachment->storeAs('uploads/supports', $fileName);
            $support->attachment = $fileName;
        }
        $support->description = $request->description;

        $support->save();

        return redirect()->route('support.index')->with('success', __('Support successfully updated.'));

    }


    public function destroy(Support $support)
    {
        $support->delete();
        if($support->attachment)
        {
            \File::delete(storage_path('uploads/supports/' . $support->attachment));
        }

        return redirect()->route('support.index')->with('success', __('Support successfully deleted.'));

    }

    public function reply($ids)
    {
        $id      = \Crypt::decrypt($ids);
        $replyes = SupportReply::where('support_id', $id)->get();
        $support = Support::find($id);

        foreach($replyes as $reply)
        {
            $supportReply          = SupportReply::find($reply->id);
            $supportReply->is_read = 1;
            $supportReply->save();
        }

        return view('support.reply', compact('support', 'replyes'));
    }

    public function replyAnswer(Request $request, $id)
    {
        $supportReply              = new SupportReply();
        $supportReply->support_id  = $id;
        $supportReply->user        = \Auth::user()->id;
        $supportReply->description = $request->description;
        $supportReply->created_by  = \Auth::user()->creatorId();
        $supportReply->save();
    
        $support = Support::find($id);
    
        $ticketCreated = $support->ticket_created;
        $ticketUser = $support->user;
    
        $notificationUserId = (\Auth::user()->id == $ticketCreated) ? $ticketUser : $ticketCreated;
    
        $notificationData = [
            'user_id' => $notificationUserId,
            'type' => 'comment_ticketing',
            'data' => json_encode([
                'id' => $id,
                'updated_by' => \Auth::user()->id,
                'name' => $supportReply->users->name,
            ]),
            'is_read' => false,
        ];
    
        Notification::create($notificationData);
    
        return redirect()->back()->with('success', __('Support reply successfully send.'));
    }

    public function grid()
    {

        // if(\Auth::user()->type == 'company')
        // {
        //     $supports = Support::where('created_by', \Auth::user()->creatorId())->get();

        //     return view('support.grid', compact('supports'));
        // }
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
        {
            $supports = Support::all();

            return view('support.grid', compact('supports'));
        }
        elseif(\Auth::user()->type == 'client')
        {
            $supports = Support::where('user', \Auth::user()->id)->orWhere('ticket_created', \Auth::user()->id)->get();

            return view('support.grid', compact('supports'));
        }
        elseif(\Auth::user()->type == 'employee')
        {

            $supports = Support::where('user', \Auth::user()->id)->orWhere('ticket_created', \Auth::user()->id)->get();

            return view('support.grid', compact('supports'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
