<?php

namespace Database\Seeders;

use App\Domain\Ticket\Enums\ActivityType;
use App\Domain\Ticket\Enums\CommentVisibility;
use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Infrastructure\Persistence\Eloquent\Models\ActivityModel;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\CommentModel;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Ewa Nowak',
            'email' => 'admin@helpdesk.test',
        ]);

        $anna = User::factory()->agent()->create([
            'name' => 'Anna Wiśniewska',
            'email' => 'anna.wisniewska@helpdesk.test',
        ]);

        $marek = User::factory()->agent()->create([
            'name' => 'Marek Zieliński',
            'email' => 'marek.zielinski@helpdesk.test',
        ]);

        $ola = User::factory()->create([
            'name' => 'Aleksandra Kamińska',
            'email' => 'ola.kaminska@helpdesk.test',
        ]);

        $jan = User::factory()->create([
            'name' => 'Jan Lewandowski',
            'email' => 'jan.lewandowski@helpdesk.test',
        ]);

        $categories = collect([
            ['Sprzęt', 'Komputery, drukarki i urządzenia peryferyjne.'],
            ['Oprogramowanie', 'Błędy aplikacji i problemy z instalacją.'],
            ['Dostęp i konta', 'Hasła, uprawnienia i blokady kont.'],
            ['Sieć', 'VPN, Wi-Fi i łączność z biurem.'],
            ['Inne', 'Sprawy, które nie pasują do pozostałych kategorii.'],
        ])->mapWithKeys(function (array $item) {
            [$name, $description] = $item;

            $category = CategoryModel::query()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $description,
                'is_active' => true,
            ]);

            return [$name => $category];
        });

        $this->ticket(
            reference: 'TCK-000001',
            title: 'Drukarka na drugim piętrze stoi w błędzie',
            description: 'Od rana drukarka przy sali konferencyjnej pokazuje błąd papieru, chociaż kaseta jest pełna. Dokumenty do podpisu stoją.',
            category: $categories['Sprzęt'],
            priority: TicketPriority::High,
            status: TicketStatus::InProgress,
            customer: $ola,
            assignee: $anna,
            createdAt: now()->subHours(26),
            firstResponseAt: now()->subHours(26)->addMinutes(40),
            comments: [
                [$anna, 'Dziękuję za zgłoszenie. Kaseta była źle osadzona — sprawdzam, czy drukarka wraca do kolejki.', CommentVisibility::Public, now()->subHours(26)->addMinutes(40)],
            ],
        );

        $this->ticket(
            reference: 'TCK-000002',
            title: 'Nie mogę zalogować się do poczty',
            description: 'Po zmianie hasła klient poczty odrzuca nowe dane. Na stronie logowania dostaję komunikat o złym haśle.',
            category: $categories['Dostęp i konta'],
            priority: TicketPriority::Urgent,
            status: TicketStatus::Resolved,
            customer: $jan,
            assignee: $marek,
            createdAt: now()->subHours(6),
            firstResponseAt: now()->subHours(6)->addMinutes(8),
            resolvedAt: now()->subHours(4),
            comments: [
                [$marek, 'Zresetowałem synchronizację hasła. Spróbuj zalogować się jeszcze raz i daj znać, czy skrzynka już wstaje.', CommentVisibility::Public, now()->subHours(6)->addMinutes(8)],
                [$jan, 'Działa. Dziękuję.', CommentVisibility::Public, now()->subHours(4)->subMinutes(10)],
            ],
        );

        $this->ticket(
            reference: 'TCK-000003',
            title: 'Laptop wolno wstaje po aktualizacji',
            description: 'Po wczorajszej aktualizacji systemu start trwa kilka minut, a wentylator pracuje od razu po włączeniu.',
            category: $categories['Oprogramowanie'],
            priority: TicketPriority::Low,
            status: TicketStatus::Open,
            customer: $ola,
            assignee: null,
            createdAt: now()->subHours(30),
        );

        $this->ticket(
            reference: 'TCK-000004',
            title: 'VPN rozłącza się przy zmianie sieci',
            description: 'Po przejściu z biurowego Wi-Fi na hotspot tunel VPN zrywa się i nie podnosi się sam.',
            category: $categories['Sieć'],
            priority: TicketPriority::Medium,
            status: TicketStatus::WaitingForCustomer,
            customer: $jan,
            assignee: $anna,
            createdAt: now()->subHours(3),
            firstResponseAt: now()->subHours(3)->addMinutes(25),
            comments: [
                [$anna, 'Wygląda na stary profil VPN. Poproszę o numer wersji klienta, gdy będziesz przy komputerze.', CommentVisibility::Public, now()->subHours(3)->addMinutes(25)],
                [$anna, 'Sprawdzić, czy polityka split-tunnel nie nadpisuje profilu po aktualizacji.', CommentVisibility::Internal, now()->subHours(3)->addMinutes(30)],
            ],
        );

        $this->ticket(
            reference: 'TCK-000005',
            title: 'Pomyłka — zgłoszenie nieaktualne',
            description: 'Wysłałem zgłoszenie na zły adres. Problem z monitorem rozwiązał się po wymianie kabla.',
            category: $categories['Inne'],
            priority: TicketPriority::High,
            status: TicketStatus::Closed,
            customer: $jan,
            assignee: null,
            createdAt: now()->subMinutes(40),
            closedAt: now()->subMinutes(20),
            comments: [
                [$jan, 'Zamykam, kabel był uszkodzony.', CommentVisibility::Public, now()->subMinutes(25)],
            ],
        );

        DB::table('ticket_counters')->where('id', 1)->update(['current' => 5]);
    }

    /**
     * @param  list<array{0: User, 1: string, 2: CommentVisibility, 3: Carbon}>  $comments
     */
    private function ticket(
        string $reference,
        string $title,
        string $description,
        CategoryModel $category,
        TicketPriority $priority,
        TicketStatus $status,
        User $customer,
        ?User $assignee,
        $createdAt,
        $firstResponseAt = null,
        $resolvedAt = null,
        $closedAt = null,
        array $comments = [],
    ): void {
        $ticket = TicketModel::query()->create([
            'id' => (string) Str::uuid(),
            'reference' => $reference,
            'title' => $title,
            'description' => $description,
            'category_id' => $category->id,
            'priority' => $priority,
            'status' => $status,
            'customer_id' => $customer->id,
            'assignee_id' => $assignee?->id,
            'first_response_at' => $firstResponseAt,
            'resolved_at' => $resolvedAt,
            'closed_at' => $closedAt,
        ]);

        $ticket->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $resolvedAt ?? $firstResponseAt ?? $createdAt,
        ])->save();

        $submitted = ActivityModel::query()->create([
            'ticket_id' => $ticket->id,
            'actor_id' => $customer->id,
            'type' => ActivityType::Submitted,
            'properties' => ['summary' => 'Zarejestrowano zgłoszenie.'],
        ]);
        $submitted->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

        if ($assignee !== null) {
            $assignedAt = $createdAt->copy()->addMinutes(5);
            $assigned = ActivityModel::query()->create([
                'ticket_id' => $ticket->id,
                'actor_id' => $assignee->id,
                'type' => ActivityType::Assigned,
                'properties' => ['summary' => "Przypisano zgłoszenie do {$assignee->name}."],
            ]);
            $assigned->forceFill(['created_at' => $assignedAt, 'updated_at' => $assignedAt])->save();
        }

        foreach ($comments as [$author, $body, $visibility, $at]) {
            $comment = CommentModel::query()->create([
                'id' => (string) Str::uuid(),
                'ticket_id' => $ticket->id,
                'author_id' => $author->id,
                'body' => $body,
                'visibility' => $visibility,
            ]);
            $comment->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
        }
    }
}
