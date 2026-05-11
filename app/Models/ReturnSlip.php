<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnSlip extends Model
{
    protected $table = 'return_slips';

    protected $fillable = [
        'user_id',
        'requested_by',
        'campus_id',
        'request_type',
        'target_office_id',
        'reason',
        'status',
        'confirmed_by',
        'confirmed_at',
        'remarks',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ReturnSlipItem::class, 'return_slip_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function targetOffice()
    {
        return $this->belongsTo(Office::class, 'target_office_id');
    }

    public function pendingItems()
    {
        return $this->hasMany(ReturnSlipItem::class, 'return_slip_id')->where('status', 'Pending');
    }

    public function confirmedItems()
    {
        return $this->hasMany(ReturnSlipItem::class, 'return_slip_id')->where('status', 'Confirmed');
    }

    public function cancelledItems()
    {
        return $this->hasMany(ReturnSlipItem::class, 'return_slip_id')->where('status', 'Cancelled');
    }
}
