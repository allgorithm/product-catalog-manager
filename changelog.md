# Changelog

Alle relevanten Änderungen, Architektur-Entscheidungen und Feature-Implementierungen in diesem Projekt.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).

---

## [1.0.0] - 2026-09-05

### 🚀 Neu hinzugefügt (Added)

#### 1. Domain-Driven Design (DDD) & Agentic Harness
* **DDD Pipeline Workflow** ([`.ai/workflows/ddd-pipeline.md`](file://.ai/workflows/ddd-pipeline.md)): 6-stufige Orchestrierung von Fachanforderung bis Merge mit verbindlichem **Entwickler-Review & Freigabe-Gate (Human-in-the-Loop)** und automatisierter Generierung von Management-Dokumentationen.
* **Architektur- & Pattern-Regeln** ([`.ai/context/ddd-architecture-rules.md`](file://.ai/context/ddd-architecture-rules.md)):
  * Verpflichtende Schichtenarchitektur (Hexagonal / Clean Architecture).
  * Systematische Einbindung der Entwurfsmuster nach [DesignPatternsPHP](https://designpatternsphp.readthedocs.io/en/latest/).
* **Spezialisierte Agenten-Suite** ([`.ai/agents/`](file://.ai/agents/)):
  * `domain-analyst.md`: Ubiquitous Language, Event Storming & Bounded Contexts.
  * `tactical-architect.md`: Aggregates, Invarianten, Value Objects, Ports & CQRS Blaupausen.
  * `enterprise-implementer.md`: Framework-freier Domain Core, Data Mapper, CQRS Handlers.
  * `test-engineer.md`: Architektur-Tests, Pure Domain Unit Tests & Integration Tests.
  * `quality-gatekeeper.md`: Statische Analyse, Pint Linter & Test-Verifikation.
  * `management-doc-writer.md`: Übersetzung technischer Invarianten & Flows in verständliche Management-Bedienungsanleitungen.
  * `laravel13-modernizer.md`: Laravel 13, PHP 8.4 Features (Property Hooks, Concurrency, Typed Casts).
  * `filament-livewire-expert.md`: Filament 5 Schemas, Tables, Actions & Livewire-Payload-Minimierung.
  * `eloquent-sql-optimizer.md`: N+1 Killer (`with`, `withCount`), Index-Strategien & Cursor-Pagination.
  * `cache-redis-architect.md`: Redis Caching, Cache-Tags, Atomic Locks & Cache Stampede Schutz.
  * `token-cost-optimizer.md`: LLM-Kontext-Kompaktierung & Semantisches Response Caching in Redis.

#### 2. Domain Layer: Catalog (`app/Domain/Catalog/`) – 100% Framework-frei
* **Aggregate Root**: `Product` mit Invarianten-Kapselung, Status-Transitions und Event Recording (`recordThat`).
* **Entity**: `ProductVariant` mit Prüfung auf Duplikate bei Größe-Farbe-Kombinationen.
* **Entity**: `Category`.
* **Value Objects (`final readonly`)**:
  * `ProductId`, `VariantId`, `CategoryId` (UUID-basiert).
  * `Sku` (Format-validiert: Alphanumerisch, Bindestriche, Punkte, Unterstriche; keine Leerzeichen).
  * `Money` (Betrag in Cents, Währung, formatiert, keine Negativbeträge).
  * `Size` & `Color` (Farbe mit Name und optionalem Hex-Code).
  * `ProductMedia` (URL, Alt-Text, Primary-Flag).
  * `ProductStatus` (Enum: `DRAFT`, `ACTIVE`, `ARCHIVED`).
* **Design Pattern (Behavioral)**:
  * `ProductCanBeActivatedSpecification` (**Specification Pattern**): Validiert, dass vor der Aktivierung eine Kategorie, mindestens eine Variante und ein Preis > 0 vorhanden sind.
* **Domain Events & Exceptions**:
  * `ProductCreated`, `ProductStatusChanged`, `VariantAddedToProduct`.
  * `DomainValidationException`.
* **Output Ports**:
  * `ProductRepositoryInterface`, `CategoryRepositoryInterface`.

#### 3. Application Layer: Use-Cases & CQRS (`app/Application/Catalog/`)
* **Commands & Handlers**:
  * `CreateProductCommand` & `CreateProductCommandHandler`.
  * `ChangeProductStatusCommand` & `ChangeProductStatusCommandHandler`.
  * `BulkChangeProductStatusCommand` & `BulkChangeProductStatusCommandHandler`.
* **Ports**:
  * `EventBusPort`.

#### 4. Infrastructure Layer & Filament 5 Backoffice (`app/Infrastructure/`, `app/Filament/`)
* **Data Mapper Pattern**:
  * `ProductDataMapper`: Saubere Konvertierung zwischen In-Memory Domain Entities und Eloquent Modellen.
* **Repositories & Adapters**:
  * `EloquentProductRepository`: Transaktionales Speichern (`DB::transaction`) mit Varianten- & Medien-Sync.
  * `EloquentCategoryRepository`.
  * `LaravelEventBusAdapter`.
* **Eloquent Models**:
  * `ProductModel`, `ProductVariantModel`, `ProductMediaModel`, `CategoryModel`.
* **Filament 5 & Livewire 3/4 Integration**:
  * `ProductResource`:
    * Tabellenansicht mit `deferLoading()`, Eager Loading und globaler Suche über Produktname und Varianten-SKUs.
    * **Inline Editing**: Status per Select und Basispreis direkt in der Zeile editierbar (bindet CQRS Handlers ein).
    * **Status-Filter-Tabs**: *Alle Produkte*, *Entwürfe*, *Aktiv*, *Archiviert*.
    * **Aktionen & Bulk Actions**: Einzel- und Mehrfach-Aktivierung/Archivierung (`BulkActionGroup`, `BulkAction`).
    * **Formular-Tabs**: *Stammdaten*, *Varianten* (Repeater) und *Medien* (Repeater).
  * `CategoryResource`:
    * Formular mit automatischer Slug-Generierung (`Str::slug`).
    * Produkt-Zähler-Badge in der Tabelle.
* **Datenbank-Migrationen**:
  * `2026_09_05_000001_create_product_catalog_tables.php`: Erstellt Tabellen `categories`, `products`, `product_variants`, `product_media` inkl. Indizes (`status`, `category_id`, `created_at`) und Unique-Constraints.
  * `2026_09_05_000002_widen_color_hex_on_product_variants_table.php`: Erweitert `color_hex` auf `VARCHAR(50)`.

#### 5. Multilevel Test-Suite (`tests/`) – 100% Grün
* **Architecture Tests** (`tests/Architecture/DomainArchitectureTest.php`):
  * Verifiziert automatisiert, dass der Domain Layer **0 Abhängigkeiten** zu Laravel (`Illuminate`), Filament oder der Infrastruktur hat.
* **Domain Unit Tests** (`tests/Unit/Domain/ProductAggregateTest.php`):
  * Testet alle Invarianten, Event Recording, Aktivierungs-Spezifikationen und Fehlerfälle isoliert (ohne Datenbank in <10ms).
* **Application Use-Case Tests** (`tests/Unit/Application/ProductCommandHandlersTest.php`):
  * Testet CQRS Command Handlers mit `InMemoryProductRepository` und `DummyEventBus`.
* **Integration Tests** (`tests/Integration/EloquentRepositoryIntegrationTest.php`):
  * Testet Re-Konstitution des Aggregats mit Relationen über `ProductDataMapper` und `EloquentProductRepository`.

#### 6. Management-Dokumentation & Bedienungsanleitung (`docs/user-guides/`)
* **Produktkatalog Management-Handbuch** ([`docs/user-guides/product-catalog-manual.md`](file://docs/user-guides/product-catalog-manual.md)):
  * Vollständig verständliche, geschäftsorientierte Dokumentation ohne IT-Kauderwelsch.
  * Executive Summary & Business Value (Qualitätssicherung, Verhinderung unvollständiger Katalogdaten im Shop).
  * Visuelle Lifecycle- und Prozessdiagramme (Mermaid) für Entwurf, Aktivierung und Archivierung.
  * Geschäftsregeln & Freigabekriterien (Aktivierungsspezifikation).
  * Schritt-für-Schritt Bedienungsanleitung für Filament 5 (Kategorien, Produkte, Varianten, Medien).
  * Troubleshooting & FAQ für Fachanwender.

---

### 🛡️ Sicherheit & Data Loss Prevention (Security & Safety)

* **Strikter Schutz gegen Datenverlust**:
  * Verbot von `artisan migrate:fresh`, `db:wipe` oder `migrate:reset` in allen Workflow- und Agenten-Vorgaben.
  * Schema-Updates werden ausschließlich **inkrementell** via `./zenv artisan migrate` durchgeführt.
  * In `AppServiceProvider::boot()` wurde `DB::prohibitDestructiveCommands($this->app->isProduction())` aktiviert.
* **Strikte Modell-Integrität**:
  * `Model::shouldBeStrict(! $this->app->isProduction())` in `AppServiceProvider`.

---

### 🔧 Behobene Fehler & Anpassungen (Bug Fixes & Improvements)

* **Filament 5 Namespace-Migration**:
  * Anpassung von `Tab` auf `Filament\Schemas\Components\Tabs\Tab`.
  * Verlagerung von `Tabs`, `Tab`, `Grid`, `Section` in `Filament\Schemas\Components`.
  * Vereinheitlichung aller Tabellen- und Bulk-Aktionen auf `Filament\Actions\*` (z. B. `Action`, `EditAction`, `DeleteAction`, `BulkAction`, `BulkActionGroup`).
  * Aktualisierung der Utility-Typen (`Forms\Set` -> `Filament\Schemas\Components\Utilities\Set`).
* **PHP 8.4 Backed Enum Handling**:
  * Behebung des `Object of class ProductStatus could not be converted to string`-Fehlers in den `visible()`-Callbacks der Aktionen durch typsicheren Zugriff über `->value`.
* **SKU & Color Validierung**:
  * Hinzufügen von client- und serverseitigen Regex-Validierungen für `sku` (`/^[A-Za-z0-9\-_.]+$/`) und `color_hex` (`/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/`), um Fehleingaben und SQL-Truncation-Fehler abzufangen.
* **Code Style & Tooling**:
  * Vollständige Einhaltung der PSR-12 / Laravel-Konventionen mittels Laravel Pint (`PASS: 75 files`).
  * Neue Composer-Skripte hinzugefügt: `composer csfix`, `composer pint` und `composer pint:test` zur bequemen Code-Formatierung.
* **Enterprise `.gitignore` Härtung**:
  * Vollständiger Zero-Leak-Schutz für Secrets/Zertifikate (`.env*`, `*.key`, `*.pem`, `*.crt`, `*.pfx`, `auth.json`, `/storage/*.key`, `/storage/oauth-*.key`).
  * Abdeckung für SQLite (`*.sqlite`, `*.sqlite-wal`, `*.sqlite-shm`), Docker Overrides, Caches (PHPStan, Pest, Mutation Infection Logs) und Dev-Tooling.
