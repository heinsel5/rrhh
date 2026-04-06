<?php

namespace Tests\Feature;

use App\Models\Collaborator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollaboratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_a_collaborator_with_valid_data()
    {
        // Arrange
        $data = [
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'document_type' => 'CC',
            'document_number' => '123456789',
            'birth_date' => '1990-01-01',
            'email' => 'juan.perez@example.com',
            'phone_number' => '3001234567',
            'address' => 'Calle 123 #45-67'
        ];

        // Act
        $collaborator = Collaborator::create($data);

        // Assert
        $this->assertDatabaseHas('collaborators', [
            'email' => 'juan.perez@example.com',
            'document_number' => '123456789'
        ]);
        $this->assertEquals('Juan', $collaborator->first_name);
    }

    /** @test */
    public function cannot_create_collaborator_with_duplicate_document_number()
    {
        // Arrange
        Collaborator::create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'document_type' => 'CC',
            'document_number' => '123456789',
            'birth_date' => '1990-01-01',
            'email' => 'juan.perez@example.com',
        ]);

        // Expect
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Act
        Collaborator::create([
            'first_name' => 'Maria',
            'last_name' => 'Gómez',
            'document_type' => 'CC',
            'document_number' => '123456789', // Duplicado
            'birth_date' => '1992-05-15',
            'email' => 'maria.gomez@example.com',
        ]);
    }

    /** @test */
    public function can_update_collaborator_information()
    {
        // Arrange
        $collaborator = Collaborator::create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'document_type' => 'CC',
            'document_number' => '123456789',
            'birth_date' => '1990-01-01',
            'email' => 'juan.perez@example.com',
        ]);

        // Act
        $collaborator->update([
            'first_name' => 'Juan Carlos',
            'phone_number' => '3009876543'
        ]);

        // Assert
        $this->assertEquals('Juan Carlos', $collaborator->fresh()->first_name);
        $this->assertEquals('3009876543', $collaborator->fresh()->phone_number);
    }

    /** @test */
    public function can_get_list_of_all_collaborators()
    {
        // Arrange
        Collaborator::create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'document_type' => 'CC',
            'document_number' => '123456789',
            'birth_date' => '1990-01-01',
            'email' => 'juan.perez@example.com',
        ]);

        Collaborator::create([
            'first_name' => 'Maria',
            'last_name' => 'Gómez',
            'document_type' => 'CC',
            'document_number' => '987654321',
            'birth_date' => '1992-05-15',
            'email' => 'maria.gomez@example.com',
        ]);

        // Act
        $collaborators = Collaborator::all();

        // Assert
        $this->assertCount(2, $collaborators);
    }

    /** @test */
    public function can_soft_delete_a_collaborator()
    {
        // Arrange
        $collaborator = Collaborator::create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'document_type' => 'CC',
            'document_number' => '123456789',
            'birth_date' => '1990-01-01',
            'email' => 'juan.perez@example.com',
        ]);

        // Act
        $collaborator->delete();

        // Assert
        $this->assertSoftDeleted($collaborator);
        $this->assertDatabaseHas('collaborators', ['id' => $collaborator->id]);
        $this->assertDatabaseMissing('collaborators', ['id' => $collaborator->id, 'deleted_at' => null]);
    }
}