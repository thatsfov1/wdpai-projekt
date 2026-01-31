# FixUp - System rezerwacji uslug fachowcow

Aplikacja webowa umozliwiajaca wyszukiwanie i rezerwacje uslug fachowcow z roznych branz. System laczy klientow poszukujacych pomocy specjalistow z wykwalifikowanymi fachowcami oferujacymi swoje uslugi.

Projekt wykonany w ramach przedmiotu **Wstep do Programowania Aplikacji Internetowych** na Politechnice Krakowskiej.

---

## Spis tresci

1. [Opis projektu](#opis-projektu)
2. [Technologie](#technologie)
3. [Struktura projektu](#struktura-projektu)
4. [Schemat bazy danych](#schemat-bazy-danych)
5. [Instalacja i uruchomienie](#instalacja-i-uruchomienie)
6. [Funkcjonalnosci](#funkcjonalnosci)

---

## Opis projektu

FixUp to platforma laczaca klientow z fachowcami. Aplikacja umozliwia:

- Rejestracje uzytkownikow jako klient lub fachowiec
- Przegladanie fachowcow wedlug kategorii uslug
- Wyszukiwanie fachowcow po miescie i kategorii
- Rezerwacje terminow wizyt
- Wystawianie recenzji po zakonczonej usludze
- Zarzadzanie profilem i oferowanymi uslugami

---

## Technologie

* Backend - PHP 8.2
* Baza danych - PostgreSQL 15           
* Serwer HTTP - Nginx                   
* Konteneryzacja - Docker, Docker Compose
* Frontend - HTML5, CSS3, JavaScript

---

## Struktura projektu

```
FixUp/
├── index.php                    # punkt wejscia aplikacji
├── Routing.php                  # system routingu
├── Database.php                 # polaczenie z baza danych
├── config.php                   # konfiguracja
├── docker-compose.yaml          # konfiguracja kontenerow
│
├── docker/
│   ├── db/
│   │   └── init.sql             # schemat bazy danych
│   ├── nginx/
│   │   └── nginx.conf           # konfiguracja serwera
│   └── php/
│       └── Dockerfile
│
├── public/
│   ├── scripts/                 # skrypty JavaScript
│   ├── styles/                  # arkusze CSS
│   └── views/                   # szablony widokow PHP
│
├── src/
│   ├── controllers/             # kontrolery MVC
│   ├── model/                   # modele danych
│   └── repository/              # warstwa dostepu do danych
│
└── uploads/                     # pliki uzytkownikow
    ├── profiles/
    └── reviews/
```


## Schemat bazy danych

### Diagram ERD

Diagram ERD znajduje sie w pliku `docs/diagramERD.png`.


### Tabele

**users** - dane uzytkownikow (klienci i fachowcy)

**categories** - kategorie uslug

**workers** - profile fachowcow

**services** - uslugi oferowane przez fachowcow

**work_images** - zdjecia prac fachowcow

**reservations** - rezerwacje terminow

**reviews** - recenzje i oceny

**review_images** - zdjecia do recenzji

**login_attempts** - logi prob logowania

**reservation_status_log** - historia zmian statusow rezerwacji

### Widoki

**v_workers_full** - pelne dane fachowcow z informacjami o uzytkowniku i kategorii

**v_reservations_details** - szczegolowe informacje o rezerwacjach

**v_workers_statistics** - statystyki fachowcow

**v_top_workers** - ranking najlepszych fachowcow

**v_recent_reviews** - ostatnie recenzje

**v_category_statistics** - statystyki kategorii uslug

**v_daily_reservations** - dzienne podsumowanie rezerwacji

### Funkcje

**calculate_worker_rating()** - oblicza srednia ocene fachowca

**count_worker_reviews()** - zlicza recenzje fachowca

**check_worker_availability()** - sprawdza dostepnosc terminu

**can_add_review()** - sprawdza mozliwosc dodania recenzji

**get_failed_login_attempts()** - liczba nieudanych prob logowania

**get_worker_summary()** - podsumowanie statystyk fachowca

### Wyzwalacze

**trg_update_rating_after_review** - aktualizuje rating fachowca po dodaniu recenzji

**trg_update_rating_after_review_delete** - przelicza rating po usunieciu recenzji

**trg_reservation_updated** - aktualizuje timestamp przy zmianie rezerwacji

**trg_validate_reservation_date** - waliduje date rezerwacji

**trg_prevent_double_booking** - zapobiega podwojnym rezerwacjom

**trg_prevent_self_booking** - blokuje rezerwacje u siebie

**trg_log_status_change** - loguje zmiany statusow rezerwacji

### Procedury

**create_reservation_safe()** - tworzenie rezerwacji z walidacja

**cancel_reservation_safe()** - anulowanie rezerwacji z walidacja

**add_review_safe()** - dodawanie recenzji z aktualizacja ratingu

**register_worker_safe()** - rejestracja fachowca

**cleanup_old_login_attempts()** - czyszczenie starych prob logowania

## Instalacja i uruchomienie

### Wymagania

- Docker
- Docker Compose

### Uruchomienie

1. Klonowanie repozytorium:

```bash
git clone <adres-repozytorium>
cd FixUp
```

2. Uruchomienie kontenerow:

```bash
docker-compose up -d
```

3. Aplikacja dostepna pod adresem:

```
http://localhost:8080
```

4. Panel pgAdmin (zarzadzanie baza danych):

```
http://localhost:5050
Login: admin@example.com
Haslo: admin
```

### Zatrzymanie

```bash
docker-compose down
```

### Reset bazy danych

```bash
docker-compose down -v
docker-compose up -d
```

---

## Funkcjonalnosci

### Dla klientow

- rejestracja i logowanie
- przegladanie kategorii uslug
- wyszukiwanie fachowcow po miescie
- przegladanie profili fachowcow
- rezerwacja terminu wizyty
- zarzadzanie rezerwacjami
- wystawianie recenzji po zakonczonej usludze

### Dla fachowcow

- rejestracja jako fachowiec z wyborem kategorii
- edycja profilu i opisu
- dodawanie oferowanych uslug
- zarzadzanie rezerwacjami (potwierdzanie, anulowanie, zakonczenie)
- przegladanie otrzymanych recenzji

### Bezpieczenstwo

- hashowanie hasel (bcrypt)
- ochrona sesji (HttpOnly, Secure, SameSite)
- prepared statements (ochrona przed SQL Injection)
- walidacja danych wejsciowych
- monitorowanie prob logowania
