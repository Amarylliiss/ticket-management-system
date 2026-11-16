<?php

use App\Domain\Identity\Enums\UserRole;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Infrastructure\Persistence\Eloquent\Models\AttachmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use App\Listeners\NotifyAssignee;
use App\Models\User;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

function category(): CategoryModel
{
    return CategoryModel::factory()->create();
}

it('registers a customer and returns a usable token', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Ola Kamińska',
        'email' => 'ola@example.test',
        'password' => 'tajnehaslo',
        'password_confirmation' => 'tajnehaslo',
    ]);

    $response->assertCreated()
        ->assertJsonPath('user.role.value', UserRole::Customer->value);

    $this->withHeader('Authorization', 'Bearer '.$response->json('token'))
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('email', 'ola@example.test');
});

it('lets a customer submit a ticket and keeps it away from other customers', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $agent = User::factory()->agent()->create();
    $category = category();

    Sanctum::actingAs($owner);

    $created = $this->postJson('/api/tickets', [
        'title' => 'Nie działa poczta',
        'description' => 'Od godziny nie mogę wysłać wiadomości.',
        'category_id' => $category->id,
        'priority' => 'high',
    ]);

    $created->assertCreated()
        ->assertJsonPath('reference', 'TCK-000001')
        ->assertJsonPath('status.value', 'open');

    $this->assertDatabaseHas('ticket_activities', [
        'ticket_id' => $created->json('id'),
        'type' => 'submitted',
    ]);

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $agent->id,
    ]);

    Sanctum::actingAs($other);

    $this->getJson('/api/tickets/'.$created->json('id'))->assertForbidden();
    $this->getJson('/api/tickets')->assertOk()->assertJsonCount(0, 'data');
});

it('assigns a ticket, changes its status and hides internal notes from the customer', function () {
    $customer = User::factory()->create();
    $agent = User::factory()->agent()->create();
    $ticket = TicketModel::factory()->create([
        'customer_id' => $customer->id,
        'status' => TicketStatus::Open,
    ]);

    Sanctum::actingAs($agent);

    $this->patchJson('/api/tickets/'.$ticket->id.'/assign', [
        'assignee_id' => $agent->id,
    ])->assertOk()->assertJsonPath('status.value', 'in_progress');

    $this->postJson('/api/tickets/'.$ticket->id.'/comments', [
        'body' => 'Sprawdzam logi na serwerze.',
        'visibility' => 'internal',
    ])->assertCreated();

    $this->postJson('/api/tickets/'.$ticket->id.'/comments', [
        'body' => 'Dostaniesz odpowiedź jeszcze dziś.',
        'visibility' => 'public',
    ])->assertCreated();

    $ticket->refresh();
    expect($ticket->first_response_at)->not->toBeNull();

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $customer->id,
    ]);

    Sanctum::actingAs($customer);

    $this->getJson('/api/tickets/'.$ticket->id)
        ->assertOk()
        ->assertJsonCount(1, 'comments')
        ->assertJsonMissing(['body' => 'Sprawdzam logi na serwerze.']);

    $this->patchJson('/api/tickets/'.$ticket->id.'/assign', [
        'assignee_id' => $agent->id,
    ])->assertForbidden();
});

it('queues the assignee notification instead of sending it inline', function () {
    Queue::fake();

    $agent = User::factory()->agent()->create();
    $ticket = TicketModel::factory()->create(['status' => TicketStatus::Open]);

    Sanctum::actingAs($agent);

    $this->patchJson('/api/tickets/'.$ticket->id.'/assign', [
        'assignee_id' => $agent->id,
    ])->assertOk();

    Queue::assertPushed(CallQueuedListener::class, function (CallQueuedListener $job) {
        return $job->class === NotifyAssignee::class;
    });
});

it('stores an attachment and refuses to hand it to another customer', function () {
    Storage::fake('attachments');

    $customer = User::factory()->create();
    $stranger = User::factory()->create();
    $ticket = TicketModel::factory()->create(['customer_id' => $customer->id]);

    Sanctum::actingAs($customer);

    $response = $this->postJson('/api/tickets/'.$ticket->id.'/attachments', [
        'file' => UploadedFile::fake()->create('opis.txt', 12, 'text/plain'),
    ]);

    $response->assertCreated();
    Storage::disk('attachments')->assertExists(
        AttachmentModel::query()->find($response->json('id'))->path
    );

    Sanctum::actingAs($stranger);

    $this->get('/api/attachments/'.$response->json('id'))->assertForbidden();
});

it('exposes response-time statistics only to the team', function () {
    $customer = User::factory()->create();
    $agent = User::factory()->agent()->create();

    Sanctum::actingAs($customer);
    $this->getJson('/api/statistics/response-times')->assertForbidden();

    Sanctum::actingAs($agent);
    $this->getJson('/api/statistics/response-times')
        ->assertOk()
        ->assertJsonStructure([
            'totals',
            'average_first_response_seconds',
            'by_priority',
            'by_agent',
        ]);
});

it('allows only an administrator to manage categories', function () {
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();

    Sanctum::actingAs($agent);
    $this->postJson('/api/categories', ['name' => 'Sieć'])->assertForbidden();

    Sanctum::actingAs($admin);
    $this->postJson('/api/categories', [
        'name' => 'Sieć',
        'description' => 'VPN i Wi-Fi',
    ])->assertCreated()->assertJsonPath('slug', 'siec');
});

it('serves the panel shell', function () {
    $this->withoutVite();

    $this->get('/')->assertOk()->assertSee('id="app"', false);
});
