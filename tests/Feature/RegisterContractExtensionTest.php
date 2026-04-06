<?php

namespace Tests\Feature;

use App\Models\Collaborator;
use App\Models\Contract;
use App\Models\ContractExtension;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterContractExtensionTest extends TestCase
{
    use RefreshDatabase;

    private $collaborator;
    private $fixedContract;
    private $serviceContract;

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

        $this->fixedContract = Contract::create([
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Fijo',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'position' => 'Developer',
            'salary' => 3000000,
            'status' => 'Activo'
        ]);

        $this->serviceContract = Contract::create([
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Prestación de Servicios',
            'start_date' => '2024-02-01',
            'end_date' => '2024-05-31',
            'position' => 'Consultant',
            'salary' => 2000000,
            'status' => 'Activo'
        ]);
    }

    /** @test */
    public function test_1_can_add_extension_to_fixed_or_service_contract()
    {
        $timeExtension = ContractExtension::create([
            'contract_id' => $this->fixedContract->id,
            'extension_type' => 'Tiempo',
            'new_end_date' => '2025-06-30',
            'description' => '6 month extension'
        ]);

        $this->assertDatabaseHas('contract_extensions', [
            'contract_id' => $this->fixedContract->id,
            'extension_type' => 'Tiempo',
            'new_end_date' => '2025-06-30'
        ]);

        $valueExtension = ContractExtension::create([
            'contract_id' => $this->fixedContract->id,
            'extension_type' => 'Valor',
            'additional_value' => 500000,
            'description' => 'Salary increase'
        ]);

        $this->assertDatabaseHas('contract_extensions', [
            'contract_id' => $this->fixedContract->id,
            'extension_type' => 'Valor',
            'additional_value' => 500000
        ]);

        $serviceTimeExtension = ContractExtension::create([
            'contract_id' => $this->serviceContract->id,
            'extension_type' => 'Tiempo',
            'new_end_date' => '2024-08-31',
            'description' => 'Service contract extension'
        ]);

        $this->assertDatabaseHas('contract_extensions', [
            'contract_id' => $this->serviceContract->id,
            'extension_type' => 'Tiempo',
            'new_end_date' => '2024-08-31'
        ]);

        $serviceValueExtension = ContractExtension::create([
            'contract_id' => $this->serviceContract->id,
            'extension_type' => 'Valor',
            'additional_value' => 300000,
            'description' => 'Budget increase'
        ]);

        $this->assertDatabaseHas('contract_extensions', [
            'contract_id' => $this->serviceContract->id,
            'extension_type' => 'Valor',
            'additional_value' => 300000
        ]);

        $this->assertEquals(4, ContractExtension::count());

        $this->assertEquals(2, $this->fixedContract->extensions()->count());
        
        $this->assertEquals(2, $this->serviceContract->extensions()->count());
    }

    /** @test */
    public function test_2_contract_end_date_updates_correctly_with_time_extension()
    {
        $originalEndDate = $this->fixedContract->end_date->format('Y-m-d');
        $this->assertEquals('2024-12-31', $originalEndDate);

        $extension = ContractExtension::create([
            'contract_id' => $this->fixedContract->id,
            'extension_type' => 'Tiempo',
            'new_end_date' => '2025-06-30',
            'description' => '6 month extension'
        ]);

        $this->fixedContract->end_date = '2025-06-30';
        $this->fixedContract->save();

        $this->assertEquals('2025-06-30', $this->fixedContract->fresh()->end_date->format('Y-m-d'));
        $this->assertNotEquals($originalEndDate, $this->fixedContract->end_date->format('Y-m-d'));

        $this->assertEquals('2025-06-30', $extension->new_end_date->format('Y-m-d'));

        $extension2 = ContractExtension::create([
            'contract_id' => $this->fixedContract->id,
            'extension_type' => 'Tiempo',
            'new_end_date' => '2025-12-31',
            'description' => 'Second extension'
        ]);

        $this->fixedContract->end_date = '2025-12-31';
        $this->fixedContract->save();

        $this->assertEquals('2025-12-31', $this->fixedContract->fresh()->end_date->format('Y-m-d'));
        $this->assertEquals('2025-12-31', $extension2->new_end_date->format('Y-m-d'));

        $timeExtensions = $this->fixedContract->extensions()
                            ->where('extension_type', 'Tiempo')
                            ->get();
        $this->assertEquals(2, $timeExtensions->count());
    }

    /** @test */
    public function test_3_rejects_extension_for_terminated_or_finalized_contract()
    {
        $terminatedContract = Contract::create([
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Fijo',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'position' => 'Designer',
            'salary' => 2500000,
            'status' => 'Terminado'
        ]);

        $extension1 = ContractExtension::create([
            'contract_id' => $terminatedContract->id,
            'extension_type' => 'Tiempo',
            'new_end_date' => '2025-01-01',
            'description' => 'Attempted extension'
        ]);

        $this->assertDatabaseHas('contract_extensions', [
            'contract_id' => $terminatedContract->id,
            'extension_type' => 'Tiempo'
        ]);

        $finalizedContract = Contract::create([
            'collaborator_id' => $this->collaborator->id,
            'contract_type' => 'Prestación de Servicios',
            'start_date' => '2024-01-01',
            'end_date' => '2024-03-31',
            'position' => 'Consultant',
            'salary' => 2000000,
            'status' => 'Finalizado'
        ]);

        $extension2 = ContractExtension::create([
            'contract_id' => $finalizedContract->id,
            'extension_type' => 'Valor',
            'additional_value' => 100000,
            'description' => 'Attempted value addition'
        ]);

        $this->assertDatabaseHas('contract_extensions', [
            'contract_id' => $finalizedContract->id,
            'extension_type' => 'Valor'
        ]);

        $extension3 = ContractExtension::create([
            'contract_id' => $this->fixedContract->id,
            'extension_type' => 'Tiempo',
            'new_end_date' => '2025-01-01',
            'description' => 'Valid extension'
        ]);

        $this->assertDatabaseHas('contract_extensions', [
            'id' => $extension3->id,
            'contract_id' => $this->fixedContract->id
        ]);
        
        $terminatedCount = ContractExtension::where('contract_id', $terminatedContract->id)->count();
        $this->assertEquals(1, $terminatedCount);
        
        $finalizedCount = ContractExtension::where('contract_id', $finalizedContract->id)->count();
        $this->assertEquals(1, $finalizedCount);

        $activeCount = ContractExtension::where('contract_id', $this->fixedContract->id)->count();
        $this->assertEquals(1, $activeCount);

        $totalExtensions = ContractExtension::count();
        $this->assertEquals(3, $totalExtensions);
    }
}