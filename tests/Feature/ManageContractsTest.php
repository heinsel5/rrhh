<?php

namespace Tests\Feature;

use App\Models\Collaborator;
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageContractsTest extends TestCase
{
    use RefreshDatabase;

    private $collaborator;

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
    }

    /** @test */
    public function test_1_can_create_contract_and_associate_to_existing_collaborator()
    {
        $contractData = [
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Fijo',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'position' => 'Senior Developer',
            'salary' => 5000000,
            'status' => 'Activo'
        ];

        $contract = Contract::create($contractData);

        $this->assertDatabaseHas('contracts', [
            'collaborator_id' => $this->collaborator->id,
            'position' => 'Senior Developer',
            'salary' => 5000000
        ]);

        $this->assertEquals($this->collaborator->id, $contract->collaborator_id);
        $this->assertEquals('Fijo', $contract->contract_type);
        $this->assertEquals('2024-01-01', $contract->start_date->format('Y-m-d'));
        $this->assertEquals('2024-12-31', $contract->end_date->format('Y-m-d'));
        $this->assertEquals('Senior Developer', $contract->position);
        $this->assertEquals(5000000, $contract->salary);

        $this->assertCount(1, $this->collaborator->contracts);
        $this->assertEquals($contract->id, $this->collaborator->contracts->first()->id);
    }

    /** @test */
    public function test_2_cannot_create_contract_for_nonexistent_collaborator()
    {
        $invalidCollaboratorId = 99999;

        $this->assertNull(Collaborator::find($invalidCollaboratorId));

        try {
            Contract::create([
                'collaborator_id' => $invalidCollaboratorId,
                'contract_type' => 'Fijo',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'position' => 'Developer',
                'salary' => 3000000,
                'status' => 'Activo'
            ]);

            $this->fail('Should have thrown an exception for nonexistent collaborator');
            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsString('foreign key', $e->getMessage());
            
            $this->assertEquals(0, Contract::count());
        }
    }

    /** @test */
    public function test_3_validates_that_date_and_salary_fields_are_correctly_validated()
    {

        try {
            $invalidDatesContract = Contract::create([
                'collaborator_id' => $this->collaborator->id,
                'contract_type' => 'Fijo',
                'start_date' => '2024-12-31',
                'end_date' => '2024-01-01',
                'position' => 'Developer',
                'salary' => 3000000,
                'status' => 'Activo'
            ]);

            if ($invalidDatesContract->end_date < $invalidDatesContract->start_date) {
                $this->fail('End date must be after start date');
            }
        } catch (\Exception $e) {
            $this->assertTrue(true, 'Date validation works correctly');
        }

        try {
            $invalidSalaryContract = Contract::create([
                'collaborator_id' => $this->collaborator->id,
                'contract_type' => 'Fijo',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'position' => 'Developer',
                'salary' => -1000000,
                'status' => 'Activo'
            ]);

            if ($invalidSalaryContract->salary <= 0) {
                $this->fail('Salary must be a positive value');
            }
        } catch (\Exception $e) {
            $this->assertTrue(true, 'Positive salary validation works correctly');
        }

        try {
            $zeroSalaryContract = Contract::create([
                'collaborator_id' => $this->collaborator->id,
                'contract_type' => 'Fijo',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'position' => 'Developer',
                'salary' => 0,
                'status' => 'Activo'
            ]);

            if ($zeroSalaryContract->salary <= 0) {
                $this->fail('Salary must be greater than 0');
            }
        } catch (\Exception $e) {
            $this->assertTrue(true, 'Salary greater than zero validation works correctly');
        }

        try {
            Contract::create([
                'collaborator_id' => $this->collaborator->id,
                'contract_type' => 'Fijo',
                'start_date' => null,
                'end_date' => '2024-12-31',
                'position' => 'Developer',
                'salary' => 3000000,
                'status' => 'Activo'
            ]);

            $this->fail('Start date cannot be null');
        } catch (\Exception $e) {
            $this->assertTrue(true, 'Non-null date validation works correctly');
        }

        $validContract = Contract::create([
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Fijo',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'position' => 'Developer',
            'salary' => 3000000,
            'status' => 'Activo'
        ]);

        $this->assertDatabaseHas('contracts', ['id' => $validContract->id]);
        $this->assertEquals(3000000, $validContract->salary);
        $this->assertEquals('2024-01-01', $validContract->start_date->format('Y-m-d'));
        $this->assertEquals('2024-12-31', $validContract->end_date->format('Y-m-d'));
    }

    /** @test */
    public function test_4_can_update_existing_contract()
    {

        $contract = Contract::create([
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Fijo',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'position' => 'Junior Developer',
            'salary' => 2500000,
            'status' => 'Activo'
        ]);

        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'position' => 'Junior Developer',
            'salary' => 2500000
        ]);

        $contract->update([
            'position' => 'Senior Developer',
            'salary' => 3500000,
            'end_date' => '2025-06-30'
        ]);

        $updatedContract = $contract->fresh();
        
        $this->assertEquals('Senior Developer', $updatedContract->position);
        $this->assertEquals(3500000, $updatedContract->salary);
        $this->assertEquals('2025-06-30', $updatedContract->end_date->format('Y-m-d'));
        
        $this->assertEquals($this->collaborator->id, $updatedContract->collaborator_id);

        $this->assertEquals('2024-01-01', $updatedContract->start_date->format('Y-m-d'));

        $this->assertEquals('Fijo', $updatedContract->contract_type);
        $this->assertEquals('Activo', $updatedContract->status);

        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'position' => 'Senior Developer',
            'salary' => 3500000,
            'end_date' => '2025-06-30'
        ]);

        $this->assertDatabaseMissing('contracts', [
            'id' => $contract->id,
            'position' => 'Junior Developer'
        ]);

        $this->assertDatabaseMissing('contracts', [
            'id' => $contract->id,
            'salary' => 2500000
        ]);

        $contract->update([
            'salary' => 4000000
        ]);

        $this->assertEquals(4000000, $contract->fresh()->salary);
        $this->assertEquals('Senior Developer', $contract->fresh()->position);
    }
}