<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractExtension extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'extension_type',
        'new_end_date',
        'additional_value',
        'description'
    ];

    protected $casts = [
        'new_end_date' => 'date',
        'additional_value' => 'decimal:2'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}