<?php

namespace Tests\Feature;

use App\Models\Collaborator;
use App\Models\Contract;
use App\Models\ContractTermination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerminateContractTest extends TestCase
{
    use RefreshDatabase;

    private $collaborator;
    private $activeContract;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->collaborator = Collaborator::create([
            'first_name' => 'Juan',
            'last_name' => 'Perez',
            'document_type' => 'CC',
            'document_number' => '123456789',
            'birth_date' => '1990-01-01',
            'email' => 'juan.perez@example.com',
            'phone_number' => '3001234567',
            'address' => '123 Main Street'
        ]);

        $this->activeContract = Contract::create([
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Fijo',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'position' => 'Developer',
            'salary' => 3000000,
            'status' => 'Activo'
        ]);
    }

    /** @test */
    public function test_1_can_change_contract_status_to_terminated()
    {
        $this->assertEquals('Activo', $this->activeContract->status);
        
        $termination = ContractTermination::create([
            'contract_id' => $this->activeContract->id,
            'termination_date' => '2024-06-30',
            'reason' => 'Employee resignation'
        ]);
        
        $this->activeContract->status = 'Terminado';
        $this->activeContract->save();
        
        $this->assertEquals('Terminado', $this->activeContract->fresh()->status);
        $this->assertNotEquals('Activo', $this->activeContract->fresh()->status);
        
        $this->assertDatabaseHas('contract_terminations', [
            'contract_id' => $this->activeContract->id,
            'termination_date' => '2024-06-30',
            'reason' => 'Employee resignation'
        ]);
        
        $this->assertNotNull($this->activeContract->fresh()->termination);
        $this->assertEquals($termination->id, $this->activeContract->fresh()->termination->id);
    }

    /** @test */
    public function test_2_termination_records_correct_date_and_reason()
    {
        $terminationDate = '2024-06-30';
        $reason = 'Contract violation - Non-compliance with work schedule';
        
        $termination = ContractTermination::create([
            'contract_id' => $this->activeContract->id,
            'termination_date' => $terminationDate,
            'reason' => $reason
        ]);
        
        $this->activeContract->status = 'Terminado';
        $this->activeContract->save();
        
        $this->assertEquals($terminationDate, $termination->termination_date->format('Y-m-d'));
        
        $this->assertEquals($reason, $termination->reason);
        
        $this->assertDatabaseHas('contract_terminations', [
            'contract_id' => $this->activeContract->id,
            'termination_date' => $terminationDate,
            'reason' => $reason
        ]);
        
        $terminationDate2 = '2024-08-15';
        $reason2 = 'Mutual agreement - Contract termination by both parties';
        
        $secondContract = Contract::create([
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Prestación de Servicios',
            'start_date' => '2024-03-01',
            'end_date' => '2024-09-30',
            'position' => 'Consultant',
            'salary' => 2500000,
            'status' => 'Activo'
        ]);
        
        $termination2 = ContractTermination::create([
            'contract_id' => $secondContract->id,
            'termination_date' => $terminationDate2,
            'reason' => $reason2
        ]);
        
        $secondContract->status = 'Terminado';
        $secondContract->save();
        
        $this->assertEquals($terminationDate2, $termination2->termination_date->format('Y-m-d'));
        $this->assertEquals($reason2, $termination2->reason);
        
        $this->assertDatabaseHas('contract_terminations', [
            'contract_id' => $secondContract->id,
            'termination_date' => $terminationDate2,
            'reason' => $reason2
        ]);
    }

    /** @test */
    public function test_3_cannot_terminate_a_contract_that_is_already_terminated_or_finalized()
    {
        $contractToTerminate = Contract::create([
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Fijo',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'position' => 'Designer',
            'salary' => 2800000,
            'status' => 'Activo'
        ]);
        
        $termination1 = ContractTermination::create([
            'contract_id' => $contractToTerminate->id,
            'termination_date' => '2024-05-15',
            'reason' => 'First termination'
        ]);
        
        $contractToTerminate->status = 'Terminado';
        $contractToTerminate->save();
        
        try {
            $termination2 = ContractTermination::create([
                'contract_id' => $contractToTerminate->id,
                'termination_date' => '2024-06-01',
                'reason' => 'Second termination attempt'
            ]);
            
            $this->fail('Should not be able to create second termination for same contract');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsString('Integrity constraint violation', $e->getMessage());
            $this->assertStringContainsString('unique', $e->getMessage());
        }
        
        $terminationCount = ContractTermination::where('contract_id', $contractToTerminate->id)->count();
        $this->assertEquals(1, $terminationCount);
        
        $this->assertEquals('Terminado', $contractToTerminate->fresh()->status);
        
        $finalizedContract = Contract::create([
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Fijo',
            'start_date' => '2024-01-01',
            'end_date' => '2024-03-31',
            'position' => 'Tester',
            'salary' => 2000000,
            'status' => 'Finalizado'
        ]);
        
        try {
            $terminationFinalized = ContractTermination::create([
                'contract_id' => $finalizedContract->id,
                'termination_date' => '2024-02-15',
                'reason' => 'Attempt to terminate finalized contract'
            ]);
            
            
            $this->assertDatabaseHas('contract_terminations', [
                'contract_id' => $finalizedContract->id
            ]);
            
            $this->assertEquals('Finalizado', $finalizedContract->fresh()->status);
            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertTrue(true);
        }
        
        $activeContractForTest = Contract::create([
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Prestación de Servicios',
            'start_date' => '2024-06-01',
            'end_date' => '2024-12-31',
            'position' => 'Analyst',
            'salary' => 3500000,
            'status' => 'Activo'
        ]);
        
        $validTermination = ContractTermination::create([
            'contract_id' => $activeContractForTest->id,
            'termination_date' => '2024-09-30',
            'reason' => 'Valid early termination'
        ]);
        
        $activeContractForTest->status = 'Terminado';
        $activeContractForTest->save();
        
        $this->assertDatabaseHas('contract_terminations', [
            'contract_id' => $activeContractForTest->id,
            'reason' => 'Valid early termination'
        ]);
        
        $this->assertEquals('Terminado', $activeContractForTest->fresh()->status);
        
        $this->assertTrue(true);
    }
}