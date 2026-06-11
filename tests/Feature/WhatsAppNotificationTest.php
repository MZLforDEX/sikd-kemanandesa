<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Notification;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles for testing
        Role::firstOrCreate(['name' => 'warga', 'display_name' => 'Warga']);
    }

    public function test_whatsapp_phone_number_formatting_logic(): void
    {
        $service = new WhatsAppService();

        $this->assertEquals('6281234567890', $service->formatPhoneNumber('081234567890'));
        $this->assertEquals('6281234567890', $service->formatPhoneNumber('+6281234567890'));
        $this->assertEquals('6281234567890', $service->formatPhoneNumber('81234567890'));
        $this->assertEquals('6281234567890', $service->formatPhoneNumber('0812-3456-7890'));
        $this->assertEquals('6281234567890', $service->formatPhoneNumber('(0812) 3456 7890'));
        $this->assertNull($service->formatPhoneNumber(''));
        $this->assertNull($service->formatPhoneNumber('abc'));
    }

    public function test_whatsapp_notification_is_not_sent_when_token_is_missing(): void
    {
        Http::fake();
        config(['services.whatsapp.token' => null]);

        $role = Role::where('name', 'warga')->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'phone' => '081234567890',
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Judul Uji',
            'message' => 'Pesan Uji',
            'link' => '/warga/dashboard',
        ]);

        Http::assertNothingSent();
    }

    public function test_whatsapp_payload_and_authorization_headers_are_correct(): void
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => true], 200),
        ]);

        config([
            'services.whatsapp.token' => 'TestSecretToken123',
            'services.whatsapp.endpoint' => 'https://api.fonnte.com/send',
        ]);

        $role = Role::where('name', 'warga')->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'phone' => '081234567890',
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Darurat Kebakaran',
            'message' => 'Kebakaran terdeteksi di RT 02 Dusun Krajan.',
            'link' => '/warga/laporan/1',
        ]);

        Http::assertSent(function ($request) {
            $this->assertEquals('https://api.fonnte.com/send', $request->url());
            $this->assertEquals('TestSecretToken123', $request->header('Authorization')[0]);
            
            $data = $request->data();
            $this->assertEquals('6281234567890', $data['target']);
            $this->assertStringContainsString('Darurat Kebakaran', $data['message']);
            $this->assertStringContainsString('Kebakaran terdeteksi di RT 02 Dusun Krajan.', $data['message']);
            $this->assertStringContainsString('/warga/laporan/1', $data['message']);
            
            return true;
        });
    }

    public function test_whatsapp_api_failures_and_exceptions_do_not_halt_flow(): void
    {
        // Mock a 500 error from the WhatsApp gateway
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'server error'], 500),
        ]);

        config([
            'services.whatsapp.token' => 'TestSecretToken123',
            'services.whatsapp.endpoint' => 'https://api.fonnte.com/send',
        ]);

        $role = Role::where('name', 'warga')->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'phone' => '081234567890',
        ]);

        // This should run without throwing any exceptions or halting flow
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Gangguan Keamanan',
            'message' => 'Laporan mencurigakan di pos ronda.',
            'link' => '/warga/dashboard',
        ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);
    }
}
