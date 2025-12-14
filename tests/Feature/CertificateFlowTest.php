<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_issuer_can_create_certificate_and_it_is_verifiable_by_hash(): void
    {
        Storage::fake('local');

        $issuer = User::factory()->create();

        $this->actingAs($issuer);

        $response = $this->post('/issuer/upload', [
            'student_name'   => 'Test Student',
            'student_wallet' => null,
            'course'         => 'Blockchain 101',
            'grade'          => 'A',
            'completed_date' => now()->toDateString(),
            'issue_date'     => now()->toDateString(),
        ]);

        $response->assertRedirect('/issuer/dashboard');

        $this->assertDatabaseHas('certificates', [
            'student_name' => 'Test Student',
            'course'       => 'Blockchain 101',
        ]);

        $certificate = Certificate::first();

        $this->assertNotNull($certificate->sha256_hash);

        $verifyResponse = $this->get('/verify/' . $certificate->sha256_hash);

        $verifyResponse->assertStatus(200);
        $verifyResponse->assertSee('Test Student');
        $verifyResponse->assertSee('Blockchain 101');
    }
}
