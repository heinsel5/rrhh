<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->constrained()->onDelete('restrict');
            $table->enum('contract_type', ['Fijo', 'Indefinido', 'Prestación de Servicios']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('position');
            $table->decimal('salary', 10, 2);
            $table->enum('status', ['Activo', 'Terminado', 'Finalizado'])->default('Activo');
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index('status');
            $table->index(['collaborator_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};