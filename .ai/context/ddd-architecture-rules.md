# DDD Architecture & Enterprise Rules (Laravel 13 + Filament 5 Stack)

Dieses Dokument definiert die verbindlichen architektonischen Richtlinien für Domain-Driven Design (DDD) und Enterprise Design Patterns ([DesignPatternsPHP](https://designpatternsphp.readthedocs.io/en/latest/)) abgestimmt auf den **konkreten Projekt-Stack**.

---

## 🛠️ Der Projekt-Stack

* **Backend Framework**: Laravel 13.x (PHP 8.4 / 8.2+)
* **Admin Panel / UI**: Filament 5.x (Livewire / Alpine / Tailwind v4)
* **Storage**: MinIO (S3-kompatibler Objektspeicher via `zenv-minio`)
* **Caching & Queues**: Redis (`zenv-redis`) & Queue Worker (`zenv-queue`)
* **Environment / CLI**: `zenv` Docker Compose Wrapper (`./zenv artisan`, `./zenv composer`, `./zenv test`)
* **Testing & Quality**: PHPUnit 11 / Pest, Laravel Pint (`./zenv php vendor/bin/pint`)

---

## 1. Hexagonale Schichtenarchitektur im Laravel 13 + Filament Stack

```
┌────────────────────────────────────────────────────────────────────────┐
│                        INFRASTRUCTURE LAYER                            │
│                                                                        │
│  ┌──────────────────────┐  ┌─────────────────────┐  ┌───────────────┐  │
│  │ Filament 5 Resources │  │ REST / API / Web    │  │ Redis / Queue │  │
│  │ (Primary Adapters)   │  │ Controllers         │  │ Event Workers │  │
│  └──────────┬───────────┘  └──────────┬──────────┘  └───────┬───────┘  │
│             │                         │                     │          │
│             ▼                         ▼                     ▼          │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │ Eloquent Repositories | Data Mappers | MinIO S3 Storage Adapters │  │
│  │ (Secondary / Driven Adapters)                                    │  │
│  └──────────────────────────────────┬───────────────────────────────┘  │
└─────────────────────────────────────┼──────────────────────────────────┘
                                      │
                                      ▼
┌────────────────────────────────────────────────────────────────────────┐
│                         APPLICATION LAYER                              │
│                                                                        │
│   • CQRS Commands & Command Handlers (Write-Side)                      │
│   • CQRS Queries & Read Model Handlers (Read-Side / Filament DTOs)     │
│   • Ports / Interfaces (Secondary Adapters: StoragePort, EventBusPort) │
└─────────────────────────────────────┬──────────────────────────────────┘
                                      │
                                      ▼
┌────────────────────────────────────────────────────────────────────────┐
│                          DOMAIN LAYER                                  │
│                                                                        │
│   • 100% PHP Pure (Strikt KEIN Illuminate\* / Eloquent / Filament)     │
│   • Aggregates & Invarianten (mit Optimistic Locking / Versioning)     │
│   • Value Objects (final readonly, self-validating)                    │
│   • Domain Events & Domain Exceptions                                  │
│   • Repository Ports (Interfaces)                                      │
│   • Enterprise Design Patterns (State, Strategy, Specification, etc.)   │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Zusammenspiel mit Filament 5 & Laravel 13

### 2.1 Filament 5 als Primary Adapter (Driving Adapter)
* **Kein direkter Eloquent-Schreibzugriff auf Domain-Logik**: 
  - Filament Forms/Pages agieren als UI-Adapter.
  - Wenn ein Filament Formular abgesendet wird, instanziiert die Page/Action ein **Application Command** (z. B. `PublishProductCommand`) und übergibt es an den `CommandHandler`.
  - Dadurch bleibt die Geschäftslogik unabhängig von Filament im Domain-Layer gekapselt.
* **Filament Tables & Queries**:
  - Können für optimale Lese-Performance dedizierte Read-Queries/DTOs aus dem Application Layer oder optimierte Eloquent Read-Modelle nutzen.

### 2.2 MinIO / S3 Storage Integration
* Der Domain-Layer definiert einen Port: `ProductImageStoragePortInterface`.
* Der Infrastructure-Layer implementiert ihn via Laravel Storage (`Storage::disk('minio')` bzw. S3 Adapter).

### 2.3 Async Queues & Event-Outbox via Redis
* Domain Events werden während der DB-Transaktion in einer `outbox`-Tabelle persistiert (**Transactional Outbox Pattern**).
* Ein Laravel Queue Job (`zenv-queue` mit Redis) liest die Outbox und dispatcht Events an externe Systeme oder Mailpit.

---

## 3. Design Patterns Katalog (DesignPatternsPHP) im Stack

1. **Creational**:
   - `Factory Method` / `Static Factory`: Erzeugung von Value Objects (`Sku::fromString()`, `Price::fromFloat()`).
   - `Builder`: Aggregation komplexer Produkte mit mehreren Varianten, Attributen und Medien.
2. **Behavioral**:
   - `State Pattern`: Für Produkt-Zustände (`DraftState`, `ActiveState`, `ArchivedState`).
   - `Strategy Pattern`: Für dynamische Preisberechnungen, Rabatte und Versandkosten.
   - `Specification Pattern`: Wiederverwendbare Geschäftsregeln (z. B. `ProductCanBePublishedSpecification`), die sowohl in der Domain als auch in Filament-Aktionen prüfbar sind.
   - `Command Pattern`: Für alle verändernden Use-Cases (CQRS).
3. **Structural**:
   - `Data Mapper`: Mapping zwischen Eloquent DB Model (`App\Models\Product`) und Domain Aggregate (`App\Domain\Catalog\Model\Product`).
   - `Adapter`: Anbindung von MinIO, Mailpit und externen Payment/ERP-Gateways.
   - `Decorator`: Caching von Lese-Queries via Redis (`CachedProductQueryHandler`).

---

## 4. Test-Matrix (Stack-Spezifisch)

| Ebene | Test-Art | Ausführung im Stack |
| :--- | :--- | :--- |
| **Architektur** | Prüft Schichten-Isolation (`Domain/` frei von `Illuminate` / `Filament`) | `./zenv php vendor/bin/phpunit --testsuite=Architecture` |
| **Domain** | Pure Unit Tests (100% Invarianten, States, Specs, Null DB) | `./zenv php vendor/bin/phpunit --testsuite=Unit` (<50ms) |
| **Application** | CQRS Handlers mit InMemory Repositories | `./zenv php vendor/bin/phpunit --testsuite=Unit` |
| **Infrastruktur** | Eloquent Data Mapper, Redis, MinIO S3 | `./zenv php vendor/bin/phpunit --testsuite=Integration` |
| **Filament / UI** | Filament Page & Resource Tests (`Livewire::test()`) | `./zenv php vendor/bin/phpunit --testsuite=Feature` |
