<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'deal_id',
        'log_type',
        'remark',
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function userdetail()
    {
        return $this->hasOne('App\Models\UserDetail', 'user_id', 'user_id');
    }

    public function getRemark()
    {
        $remark = json_decode($this->remark, true);

        if($remark)
        {
            $user_name = $this->user ? $this->user->name : '';

            if($this->log_type == 'Invite User')
            {
                return $user_name . ' ' . __('has invited') . ' <b>' . $remark['title'][0] . '</b>';
            }
            elseif($this->log_type == 'User Assigned to the Task')
            {
                return $user_name . ' ' . __('has assigned task ') . ' <b>' . $remark['task_name'] . '</b> ' . __(' to') . ' <b>' . $remark['member_name'] . '</b>';
            }
            elseif($this->log_type == 'User Removed from the Task')
            {
                return $user_name . ' ' . __('has removed ') . ' <b>' . $remark['member_name'] . '</b>' . __(' from task') . ' <b>' . $remark['task_name'] . '</b>';
            }
            elseif($this->log_type == 'Upload File')
            {
                return $user_name . ' ' . __('Upload new file') . ' <b>' . $remark['file_name'] . '</b>';
            }
            elseif($this->log_type == 'Create Project')
            {
                return $user_name . ' ' . __('Create new Project') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Update Project')
            {
                return $user_name . ' ' . __('Update Project') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Delete Project')
            {
                return $user_name . ' ' . __('Delete Project') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Bug')
            {
                return $user_name . ' ' . __('Created new bug') . ' <b>' . $remark['title'] . '</b>';
            }
            elseif($this->log_type == 'Create Milestone')
            {
                return $user_name . ' ' . __('Create new milestone') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Task')
            {
                return $user_name . ' ' . __('Create new Task') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Update Task')
            {
                return $user_name . ' ' . __('Update Task') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Update Priority')
            {
                return $user_name . ' ' . __('Update Priority') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Update Status')
            {
                return $user_name . ' ' . __('Update Status') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Move Task')
            {
                return $user_name . ' ' . __('Moved the Task') . " <b>" . $remark['title'] . "</b> " . __('from') . " " . __(ucwords($remark['old_stage'])) . " " . __('to') . " " . __(ucwords($remark['new_stage']));
            }
            elseif($this->log_type == 'Create Expense')
            {
                return $user_name . ' ' . __('Create new Expense') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Sub Task')
            {
                return $user_name . ' ' . __('Create new Sub Task') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Update Sub Task')
            {
                return $user_name . ' ' . __('Update Sub Task') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Attachment')
            {
                return $user_name . ' ' . __('Create new Attachment') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Delete Attachment')
            {
                return $user_name . ' ' . __('Delete Attachment') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Link')
            {
                return $user_name . ' ' . __('Create Link') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Delete Link')
            {
                return $user_name . ' ' . __('Delete Link') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Comment')
            {
                return $user_name . ' ' . __('Create new Comment') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Add Product')
            {
                return $user_name . ' ' . __('Add new Products') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Update Sources')
            {
                return $user_name . ' ' . __('Update Sources');
            }
            elseif($this->log_type == 'Create Deal Call')
            {
                return $user_name . ' ' . __('Create new Deal Call');
            }
            elseif($this->log_type == 'Create Deal Email')
            {
                return $user_name . ' ' . __('Create new Deal Email');
            }
            elseif($this->log_type == 'Move')
            {
                return $user_name . " " . __('Moved the deal') . " <b>" . $remark['title'] . "</b> " . __('from') . " " . __(ucwords($remark['old_status'])) . " " . __('to') . " " . __(ucwords($remark['new_status']));
            }
            elseif($this->log_type == 'Delete Team')
            {
                return $user_name . ' ' . __('Delete') . " <b>" . $remark['title'][0] . "</b>";
            }
            elseif($this->log_type == 'Delete Sub Task')
            {
                return $user_name . ' ' . __('Delete Sub Task') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Delete Comment')
            {
                return $user_name . ' ' . __('Delete Comment') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Audit Memorandum')
            {
                return $user_name . ' ' . __('Create Audit Memorandum') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Journal Data')
            {
                return $user_name . ' ' . __('Create Journal Data') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Financial Statment')
            {
                return $user_name . ' ' . __('Create Financial Statment') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Summary Materiality')
            {
                return $user_name . ' ' . __('Create Summary Materiality') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Notes Analysis Perbandingan Data Antar Periode')
            {
                return $user_name . ' ' . __('Create Notes Analysis Perbandingan Data Antar Periode') . " <b>" . $remark['title'] . "</b>";
            }
        }
        else
        {
            return $this->remark;
        }
    }

    public function logIcon($type = '')
    {
        $icon = '';

        if(!empty($type))
        {
            if($type == 'Invite User')
            {
                $icon = 'user';
            }
            else if($type == 'User Assigned to the Task')
            {
                $icon = 'user-check';
            }
            else if($type == 'User Removed from the Task')
            {
                $icon = 'user-x';
            }
            else if($type == 'Upload File')
            {
                $icon = 'upload-cloud';
            }
            else if($type == 'Create Milestone')
            {
                $icon = 'crop';
            }
            else if($type == 'Create Bug')
            {
                $icon = 'alert-triangle';
            }
            else if($type == 'Create Task')
            {
                $icon = 'list';
            }
            else if($type == 'Update Task')
            {
                $icon = 'edit';
            }
            else if($type == 'Update Priority')
            {
                $icon = 'edit';
            }
            else if($type == 'Update Status')
            {
                $icon = 'edit';
            }
            else if($type == 'Create Project')
            {
                $icon = 'file-text';
            }
            else if($type == 'Update Project')
            {
                $icon = 'edit';
            }
            else if($type == 'Delete Project')
            {
                $icon = 'x';
            }
            else if($type == 'Move Task')
            {
                $icon = 'command';
            }
            else if($type == 'Create Expense')
            {
                $icon = 'clipboard';
            }
            else if($type == 'Move')
            {
                $icon = 'move';
            }
            elseif($type == 'Add Product')
            {
                $icon = 'shopping-cart';
            }
            elseif($type == 'Upload File')
            {
                $icon = 'file';
            }
            elseif($type == 'Update Sources')
            {
                $icon = 'airplay';
            }
            elseif($type == 'Create Deal Call')
            {
                $icon = 'phone-call';
            }
            elseif($type == 'Create Deal Email')
            {
                $icon = 'voicemail';
            }
            elseif($type == 'Create Invoice')
            {
                $icon = 'file-plus';
            }
            elseif($type == 'Add Contact')
            {
                $icon = 'book';
            }
            else if($type == 'Create Comment')
            {
                $icon = 'message-circle';
            }
            else if($type == 'Delete Team')
            {
                $icon = 'user-x';
            }
            else if($type == 'Delete Sub Task')
            {
                $icon = 'x';
            }
            else if($type == 'Create Sub Task')
            {
                $icon = 'folder-plus';
            }
            else if($type == 'Update Sub Task')
            {
                $icon = 'edit';
            }
            else if($type == 'Create Link')
            {
                $icon = 'link';
            }
            else if($type == 'Delete Link')
            {
                $icon = 'x';
            }
            else if($type == 'Delete Comment')
            {
                $icon = 'x-circle';
            }
            else if($type == 'Create Audit Memorandum')
            {
                $icon = 'file-text';
            }
            else if($type == 'Create Journal Data')
            {
                $icon = 'pencil';
            }
            else if($type == 'Create Financial Statment')
            {
                $icon = 'pencil';
            }
            else if($type == 'Create Summary Materiality')
            {
                $icon = 'pencil';
            }
            else if($type == 'Create Notes Analysis Perbandingan Data Antar Periode')
            {
                $icon = 'pencil';
            }
            else if($type == 'Create Attachment')
            {
                $icon = 'file';
            }
            else if($type == 'Delete Attachment')
            {
                $icon = 'file-minus';
            }
        }

        return $icon;
    }
}
