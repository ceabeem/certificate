<?php

namespace Tests\Feature;

use App\Models\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VerificationUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_page_shows_both_upload_and_hash_forms(): void
    {
        $response = $this->get('/verify');

        $response->assertStatus(200);
        $response->assertSee('Verify by PDF', false);
        $response->assertSee('Verify by SHA-256', false);
    }

    public function test_verification_by_hash_shows_invalid_for_unknown_hash(): void
    {
        $response = $this->post('/verify/hash', [
            'hash' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Certificate not found in records', false);
    }

    public function test_public_download_route_serves_existing_certificate_pdf(): void
    {
        Storage::fake('local');

        $path = 'certificates/test.pdf';
        Storage::disk('local')->put($path, 'dummy pdf content');

        $certificate = Certificate::create([
            'student_name'      => 'Dummy Student',
            'student_wallet'    => null,
            'course'            => 'Test Course',
            'grade'             => null,
            'completed_date'    => now()->toDateString(),
            'issue_date'        => now()->toDateString(),
            'certificate_code'  => 'TEST-001',
            'pdf_path'          => $path,
            'sha256_hash'       => hash('sha256', 'dummy pdf content'),
            'ipfs_cid'          => null,
            'issuer_id'         => 1,
        ]);

        $response = $this->get('/certificates/' . $certificate->id . '/download');

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }
}
