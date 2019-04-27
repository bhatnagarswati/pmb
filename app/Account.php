<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    //
    const TYPE_DD = 1;
    const TYPE_RD = 2;
    const TYPE_FD = 3;
    const TYPE_MONTHLY_INCOME = 4;
    const TYPE_LOAN = 5;

    const STATUS_ACTIVE = 1;
    const STATUS_CLOSED = 2;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $appends = [
        'ori_account_number',
        'payable_amount',
        'term',
        'installment_number',
        'paid_installment',
        'unpaid_installment',
        'paid_amount'    
    ];

    public function getInstallmentNumberAttribute()
    {
        $no_of_inst = $this->getDays($this->policy_date, date("Y-m-d"));
        return $no_of_inst > 0 ? $no_of_inst : 0;
    }

    public function getPaidInstallmentAttribute()
    {
        return $this->transactions->sum('amount')/$this->denomination_amount;
    }

    public function getPaidAmountAttribute()
    {
        return $this->transactions->sum('amount');
    }

    public function getUnpaidInstallmentAttribute()
    {
        return $this->installment_number - $this->paid_installment;
    }

    public function getOriAccountNumberAttribute()
    {
        return Admin::getTygetPrefixpeOptions($this->account_type).$this->prefix.$this->account_number;
    }

    public function getTermAttribute()
    {
        if ($this->account_type == Account::TYPE_DD){
            return $this->getDays($this->policy_date, $this->maturity_date).' Days';
        }
        return $this->duration.' months';
    }

    public function transactions()
    {
        return $this->hasMany(AccountTransaction::class, 'account_id')->where('method', AccountTransaction::MEDTHOD_CREDIT);
    }

    public function createUser()
    {
        return $this->belongsTo(Admin::class, 'create_user_id');
    }

    public function setData($request)
    {
        $this->nominee_name = $request->nominee_name;
        $this->nominee_relation = $request->nominee_relation;
        $this->nominee_share = $request->nominee_share;
        $this->second_nominee_name = (!empty($request->second_nominee_name)) ? $request->second_nominee_name : 'NULL';
        $this->second_nominee_relation = (!empty($request->second_nominee_relation)) ? $request->second_nominee_relation : 'NULL';
        $this->second_nominee_share = (!empty($request->second_nominee_share)) ? $request->second_nominee_share : 'NULL';
        $this->account_type = $request->account_type;
        $this->interest_rate = $request->interest_rate;
        $this->denomination_amount = $request->denomination_amount;
        $this->duration = $request->duration;
        $this->prefix = $this->getMemberPrefix();
        $this->account_number = $this->getAccountNumber();
        $this->create_user_id = \Auth::guard('admins')->user()->id;
        $this->user_id = $request->user_id;
        $this->policy_date = $request->policy_date;
        $this->maturity_date = date("Y-m-d", strtotime($this->policy_date.' + '.$this->duration.' months'));        
        $this->maturity_amount = $this->payable_amount + $this->payable_amount * $this->interest_rate/100;
        $this->status = Account::STATUS_ACTIVE;
    }

    public function getPayableAmountAttribute()
    {
        if($this->account_type == self::TYPE_DD) {
            $no_of_days = $this->getDays($this->policy_date, $this->maturity_date);
            return $this->denomination_amount * $no_of_days;
        }elseif($this->account_type == self::TYPE_RD) {
            return  $this->denomination_amount * $this->duration;
        }

        return $this->denomination_amount;
    }

    public function getMemberPrefix()
    {
        $last_account_no = Account::where('account_type', $this->account_type)->orderBy('id', 'DESC')->first();
        $member_prefix = str_pad(1, 3, '0', STR_PAD_LEFT);

        if($last_account_no) {
            $member_id = (int)$last_account_no->account_number;
            if ($member_id == 9999999) {
                $member_prefix = $member_prefix + 1;
            }
        }       

        return $member_prefix;
    }
    
    public function getAccountNumber()
    {
        $prefix = Admin::getTygetPrefixpeOptions($this->account_type);
        $last_account_no = Account::where('account_type', $this->account_type)->orderBy('id', 'DESC')->first();
        $account_no = str_pad(1, 7, '0', STR_PAD_LEFT); 
        if($last_account_no) {
            $account_no = (int)$last_account_no->account_number + 1;
            $account_no = str_pad($account_no, 7, '0', STR_PAD_LEFT); 
        }

        if ($account_no == 9999999) {
            $account_no = str_pad(1, 7, '0', STR_PAD_LEFT); 
        }

        return $account_no;
    }

    public static function getTypeOptions($id = null)
    {
        $list = [
            self::TYPE_DD => 'Daily Deposit',
            self::TYPE_RD => 'Reccurring Deposit',
            self::TYPE_FD => 'Fixed Deposit',
            //self::TYPE_MONTHLY_INCOME => 'Monthly Income Scheme',
           // self::TYPE_LOAN => 'Loan Scheme',
        ];

        if ($id === null) {
            return $list;        
        }

        if(isset($list[$id])) {
            return $list[$id];
        }

        return $id;
    }

    public static function getDurationOptions($id = null)
    {
        $list = [
            self::TYPE_DD => DDSetting::class,
            self::TYPE_RD => RDSetting::class,
            self::TYPE_FD => FDSetting::class,
            self::TYPE_MONTHLY_INCOME => MonthlyScheme::class,
            self::TYPE_LOAN => LoanScheme::class,
        ];

        if ($id === null) {
            return $list;        
        }

        if(isset($list[$id])) {
            return $list[$id]::getDurationArray();
        }

        return $id;
    }

    public static function getClassOptions($id = null)
    {
        $list = [
            self::TYPE_DD => DDSetting::class,
            self::TYPE_RD => RDSetting::class,
            self::TYPE_FD => FDSetting::class,
            self::TYPE_MONTHLY_INCOME => MonthlyScheme::class,
            self::TYPE_LOAN => LoanScheme::class,
        ];

        if ($id === null) {
            return $list;        
        }

        if(isset($list[$id])) {
            return $list[$id];
        }

        return $id;
    }

    public static function getDenominationArray($id)
    {
        $list = [
            self::TYPE_DD => DDSetting::class,
            self::TYPE_RD => RDSetting::class,
            self::TYPE_FD => FDSetting::class,
            self::TYPE_MONTHLY_INCOME => MonthlyScheme::class,
            self::TYPE_LOAN => LoanScheme::class,
        ];

        if ($id === null) {
            return $list;        
        }

        if(isset($list[$id])) {
            return $list[$id]::getDenominationArray();            
        }
        return $id;
    }

    public function rules()
    {
        $array = [

        ];

        return $array;
    }

    public static function getDays($from, $to)
    {
        $to = \Carbon\Carbon::createFromFormat('Y-m-d', $to);

        $from = \Carbon\Carbon::createFromFormat('Y-m-d', $from);

        $diff_in_days = $to->diffInDays($from);

        return $diff_in_days - 1;
    }

}
