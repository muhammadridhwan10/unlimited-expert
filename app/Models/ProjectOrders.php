<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectOrders extends Model
{
    use HasFactory;

    protected $fillable=[
        'id',
        'order_number',
        'fee',
        'name',
        'email',
        'name_invoice',
        'position',
        'telp',
        'npwp',
        'address',
        'country',
        'state',
        'city',
        'total_company_income_per_year',
        'total_company_assets_value',
        'total_employeee',
        'total_branch_offices',
        'client_business_sector_id',
        'client_ownership_id',
        'accounting_standars_id',
        'total_company_profit_or_loss',
        'periode',
        'project_name',
        'start_date',
        'end_date',
        'description',
        'budget',
        'estimated_hrs',
        'tags',
        'label',
        'template_task_id',
        'public_accountant_id',
        'leader_project',
        'status',
        'ph_partners',
        'ph_manager',
        'ph_senior',
        'ph_associate',
        'ph_asssistant',
        'rate_partners',
        'rate_manager',
        'rate_senior',
        'rate_associate',
        'rate_assistant',
        'is_approve',
        'status_client',
        'created_by',
        'where_did_you_find_out_about_us'
    ];

    public static $label=[
        'Audit' => 'Audit',
        'Accounting' => 'Accounting',
        'Tax' => 'Tax',
        'Accounting&Tax' => 'Accounting&Tax',
        'KPPK' => 'KPPK',
        'Agreed Upon Procedures (AUP)' => 'Agreed Upon Procedures (AUP)',
        'Other' => 'Other',
    ];

    public function sector(){
        return $this->hasOne('App\Models\ClientBusinessSector', 'id', 'client_business_sector_id');
    }

    public function ownership(){
        return $this->hasOne('App\Models\ClientOwnershipStatus', 'id', 'client_ownership_id');
    }

    public function accountingstandard(){
        return $this->hasOne('App\Models\ClientAccountingStandard', 'id', 'accounting_standars_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, "leader_project", "id");
    }

    public function accountant()
    {
        return $this->belongsTo(PublicAccountant::class, "public_accountant_id", "id");
    }

}
