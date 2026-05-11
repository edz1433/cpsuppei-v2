<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnSlipItem extends Model
{
    protected $table = 'return_slip_items';

    protected $fillable = [
        'return_slip_id',
        'enduser_property_id',
        'property_no_generated',
        'item_name',
        'serial_number',
        'current_office_id',
        'current_location_id',
        'current_status',
        'status',
        'confirmed_by',
        'confirmed_at',
        'cancelled_by',
        'cancelled_at',
        'action_remarks',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function slip()
    {
        return $this->belongsTo(ReturnSlip::class, 'return_slip_id');
    }

    public function property()
    {
        return $this->belongsTo(EnduserProperty::class, 'enduser_property_id');
    }

    public function currentOffice()
    {
        return $this->belongsTo(Office::class, 'current_office_id');
    }

    public function currentLocation()
    {
        return $this->belongsTo(Office::class, 'current_location_id');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
