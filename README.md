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

Backend - PHP 8.2
Baza danych - PostgreSQL 15           
Serwer HTTP - Nginx                   
Konteneryzacja - Docker, Docker Compose
Frontend - HTML5, CSS3, JavaScript

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
