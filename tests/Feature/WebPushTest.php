<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Notification;
use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class WebPushTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles for testing
        Role::firstOrCreate(['name' => 'warga', 'display_name' => 'Warga']);
    }

    public function test_user_can_subscribe_to_push_notifications(): void
    {
        $role = Role::where('name', 'warga')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->postJson('/push-subscribe', [
            'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/gAAAAAB',
            'keys' => [
                'p256dh' => 'BPCScXnIQN23C5hQ8U...',
                'auth' => 'a5_6as8dasd...',
            ],
            'content_encoding' => 'aes128gcm',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/gAAAAAB',
            'public_key' => 'BPCScXnIQN23C5hQ8U...',
            'auth_token' => 'a5_6as8dasd...',
            'content_encoding' => 'aes128gcm',
        ]);
    }

    public function test_user_can_unsubscribe_from_push_notifications(): void
    {
        $role = Role::where('name', 'warga')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $subscription = PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/gAAAAAB',
            'public_key' => 'BPCScXnIQN23C5hQ8U...',
            'auth_token' => 'a5_6as8dasd...',
            'content_encoding' => 'aes128gcm',
        ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'id' => $subscription->id,
        ]);

        $response = $this->actingAs($user)->postJson('/push-unsubscribe', [
            'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/gAAAAAB',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'id' => $subscription->id,
        ]);
    }

    public function test_notification_creation_triggers_push_observer(): void
    {
        $role = Role::where('name', 'warga')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        // Mock the WebPushService to assert sendPush is called
        $mockService = Mockery::mock(WebPushService::class);
        $mockService->shouldReceive('sendPush')
            ->once()
            ->with(Mockery::on(function ($notification) use ($user) {
                return $notification->user_id === $user->id && $notification->title === 'Alert Uji';
            }));

        $this->app->instance(WebPushService::class, $mockService);

        // Create notification which should fire the NotificationObserver
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Alert Uji',
            'message' => 'Ini adalah pesan uji untuk observer.',
            'link' => '/warga/dashboard',
        ]);
    }

    public function test_guest_cannot_subscribe_or_unsubscribe(): void
    {
        $response = $this->postJson('/push-subscribe', [
            'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/gAAAAAB',
            'keys' => [
                'p256dh' => 'BPCScXnIQN23C5hQ8U...',
                'auth' => 'a5_6as8dasd...',
            ],
            'content_encoding' => 'aes128gcm',
        ]);

        $response->assertStatus(401); // Unauthorized for JSON requests

        $responseUnsub = $this->postJson('/push-unsubscribe', [
            'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/gAAAAAB',
        ]);

        $responseUnsub->assertStatus(401);
    }

    public function test_subscribe_validates_required_fields(): void
    {
        $role = Role::where('name', 'warga')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->postJson('/push-subscribe', [
            // Missing all required parameters
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['endpoint', 'keys.p256dh', 'keys.auth']);
    }
}
