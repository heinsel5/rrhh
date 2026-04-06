<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractTermination extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'termination_date',
        'reason'
    ];

    protected $casts = [
        'termination_date' => 'date'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}