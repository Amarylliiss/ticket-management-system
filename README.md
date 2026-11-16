# Helpdesk

System zgłoszeń dla klientów i zespołu wsparcia. Klient opisuje problem, pracownik przejmuje sprawę, a pulpit pokazuje, czy pierwsza odpowiedź zmieściła się w progu priorytetu.

## Co potrafi

- rejestracja klienta i logowanie tokenem Laravel Sanctum
- zgłoszenia z kategorią, priorytetem i statusem
- przypisywanie spraw pracownikom
- komentarze publiczne i wewnętrzne
- załączniki pobierane tylko przez osoby, które widzą zgłoszenie
- historia zmian
- statystyki czasu pierwszej odpowiedzi i czasu rozwiązania
- zdarzenia domenowe, listenery i powiadomienia na kolejce Redis
- polityki dostępu dla klienta, pracownika i administratora

## Układ kodu

Zapis idzie przez model domenowy. Odczyt idzie krótszą drogą, zapytaniem do bazy.

```
app/Domain            reguły zgłoszenia, statusy, priorytety, zdarzenia
app/Application       komendy, handlery i raport czasu odpowiedzi
app/Infrastructure    Eloquent, mapowanie i repozytorium
app/Http              API
app/Policies          kto co może zobaczyć i zmienić
app/Listeners         historia oraz powiadomienia
resources/js          panel Vue
```

Agregat `Ticket` pilnuje przejść statusu, przypisania i jednorazowego zapisu pierwszej odpowiedzi. Po zapisie publikowane są zdarzenia Laravela. Historia powstaje od razu. Powiadomienia (baza i mail) realizują listenery z `ShouldQueue`, więc w środowisku z Redisem obsługuje je worker.

Progi pierwszej odpowiedzi:

| Priorytet | Czas |
| --- | --- |
| Niski | 8 godzin |
| Średni | 4 godziny |
| Wysoki | 1 godzina |
| Pilny | 15 minut |

Czas liczy się do pierwszej publicznej odpowiedzi pracownika. Notatka wewnętrzna nie zatrzymuje licznika. Zgłoszenie zamknięte przez klienta przed progiem nie jest spóźnione.

## Uruchomienie

Potrzebny jest Docker.

```bash
cp .env.example .env
docker compose --profile assets run --rm assets
docker compose up --build
```

Panel jest pod adresem [http://localhost:8080](http://localhost:8080).

Przy pierwszym starcie migracje wykonują się same. Dane przykładowe:

```bash
docker compose exec app php artisan db:seed --force
```

Testy:

```bash
docker compose exec app php artisan test
```

Kolejka działa w usłudze `queue` (`php artisan queue:work redis`). Mail w przykładzie trafia do logu (`storage/logs/laravel.log`).

## Konta przykładowe

Hasło każdego konta to `password`.

| Rola | E-mail |
| --- | --- |
| Administrator | admin@helpdesk.test |
| Pracownik | anna.wisniewska@helpdesk.test |
| Pracownik | marek.zielinski@helpdesk.test |
| Klient | ola.kaminska@helpdesk.test |
| Klient | jan.lewandowski@helpdesk.test |

Nowe konto założone w panelu zawsze dostaje rolę klienta. Hasło bazy w `docker-compose.yml` (`secret` / `root`) jest tylko do lokalnego uruchomienia.
