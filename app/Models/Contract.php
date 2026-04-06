<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'collaborator_id',
        'contract_type',
        'start_date',
        'end_date',
        'position',
        'salary',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal:2'
    ];

    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function extensions()
    {
        return $this->hasMany(ContractExtension::class);
    }

    public function termination()
    {
        return $this->hasOne(ContractTermination::class);
    }

    public function isActive()
    {
        return $this->status === 'Activo';
    }

    public function canBeExtended()
    {
        return in_array($this->status, ['Activo']) && 
               in_array($this->contract_type, ['Fijo', 'Prestación de Servicios']);
    }
}