# Workflow: Domain-Driven Design (DDD) Enterprise Pipeline
## Stack: Laravel 13 • Filament 5 • PHP 8.4/8.2 • Redis • MinIO • zenv Docker

Dieser Workflow transformiert eine fachliche Anforderung in ein vollständiges, getestetes und architektonisch verifiziertes Enterprise-Feature nach **Domain-Driven Design (DDD)** und den Entwurfsmustern aus **[DesignPatternsPHP](https://designpatternsphp.readthedocs.io/en/latest/)**.

---

## 🚀 Phasenübersicht & Approval-Gate

```
[Anforderung / User Story] 
     │
     ▼
[Phase 1: Strategisches DDD]      ──► Agent: domain-analyst.md
     │
     ▼
[Phase 2: Taktisches DDD &        ──► Agent: tactical-architect.md
          Design Patterns Matrix]
     │
     ▼
╔════════════════════════════════════════════════════════════════════╗
║ 🛑 PHASE 2.5: ENTWICKLER-REVIEW & FREIGABE-GATE (HUMAN-IN-THE-LOOP)║
║  • Der Agent STOPPT hier und präsentiert das Konzept dem Entwickler║
║  • Erst nach EXPLIZITER FREIGABE durch den Entwickler darf         ║
║    die Implementierung (Phase 3) starten!                          ║
╚════════════════════════════════════════════════════════════════════╝
     │  (Nach Freigabe)
     ▼
[Phase 3: Enterprise-             ──► Agent: enterprise-implementer.md
          Implementierung]
          (Domain, CQRS, Eloquent Data Mapper, Filament 5, MinIO)
     │
     ▼
[Phase 4: Multilevel Test-Suite]  ──► Agent: test-engineer.md
          (ArchUnit, Domain Unit, Integration, Filament Livewire)
     │
     ▼
[Phase 5: Quality Gate]           ──► Agent: quality-gatekeeper.md
          (./zenv artisan test, ./zenv php vendor/bin/pint)
     │
     ├── (Rot / Fehler) ──────────► Loop zurück zu Phase 3
     └── (Grün) 
            │
            ▼
[Phase 6: Management- &           ──► Agent: management-doc-writer.md
          Bedienungsanleitung]
          (docs/user-guides/[feature]-manual.md)
            │
            ▼
        Feature Done / Merge Ready
```

---

## 📖 Detaillierte Phasen

### 🔹 Phase 1: Strategisches DDD (Discovery & Ubiquitous Language)
* **Agent**: `.ai/agents/domain-analyst.md`
* **Input**: Fachliche Anforderung
* **Aktionen**:
  1. Event Storming Stream analysieren (Events, Commands, Read Models).
  2. Fachglossar definieren (Ubiquitous Language).
  3. Bounded Contexts und Subdomains (Core/Supporting/Generic) festlegen.
  4. Context Map & Integrationsmuster (ACL, Shared Kernel, etc.) bestimmen.
* **Output-Artefakt**: `.ai/specs/[feature-name]/01-strategic-analysis.md`

---

### 🔹 Phase 2: Taktisches DDD & Design Patterns Matrix
* **Agent**: `.ai/agents/tactical-architect.md`
* **Input**: `01-strategic-analysis.md` & `.ai/context/ddd-architecture-rules.md`
* **Aktionen**:
  1. **Design Patterns Auswahl (nach [DesignPatternsPHP](https://designpatternsphp.readthedocs.io/en/latest/))**:
     - *Creational*: Factory Method, Builder, Prototype
     - *Behavioral*: State Pattern, Strategy Pattern, Specification Pattern, Command Pattern
     - *Structural*: Data Mapper (Domain <-> Eloquent), Adapter (MinIO/Redis), Decorator (Caching)
  2. Aggregate Roots, Entities und Value Objects modellieren.
  3. Invarianten (Konsistenzregeln) exakt definieren.
  4. Domain Events, Exceptions und Repository/Gateway-Ports spezifizieren.
  5. Filament 5 UI-Anforderungen & CQRS Commands/Queries definieren.
* **Output-Artefakt**: `.ai/specs/[feature-name]/02-tactical-blueprint.md`

---

### 🛑 Phase 2.5: Entwickler-Review & Freigabe-Gate (Human-in-the-Loop)
* **Status**: **STOPP-PUNKT**. Die Pipeline unterbricht die automatische Ausführung.
* **Aktion des Assistenten**:
  1. Präsentiert dem Entwickler eine prägnante Zusammenfassung des Konzepts (`02-tactical-blueprint.md`), der gewählten Patterns und der Schnittstellen.
  2. Bittet den Entwickler um Feedback und explizite Freigabe.
* **Bedingung**: **Die Phase 3 (Implementierung) darf UNTER KEINEN UMSTÄNDEN vor der Bestätigung des Entwicklers gestartet werden.**
* **Optionen des Entwicklers**:
  - `Genehmigt`: Weiter zu Phase 3.
  - `Änderungswünsche`: Tactical Architect passt die Blaupause an und bittet erneut um Freigabe.

---

### 🔹 Phase 3: Enterprise-Implementierung (Hexagonal / Clean Architecture)
* **Agent**: `.ai/agents/enterprise-implementer.md`
* **Voraussetzung**: Explizite Freigabe aus Phase 2.5 erteilt.
* **Aktionen**:
  1. **Domain Layer (`app/Domain/[Context]/`)**: 
     - Value Objects, Aggregates, Domain Events, Exceptions, Repository Interfaces.
     - Umsetzung der gewählten Muster: State, Strategy, Specification, Builder, etc.
     - **100% Framework-frei** (keine Laravel/Eloquent/Filament Abhängigkeit).
  2. **Application Layer (`app/Application/[Context]/`)**:
     - CQRS Commands, CommandHandlers, Queries, DTOs, Event Bus Ports.
  3. **Infrastructure Layer (`app/Infrastructure/[Context]/`)**:
     - **Data Mapper**: Trennung zwischen Eloquent Modellen (`App\Models\...`) und Domain Entities.
     - **Repositories**: `Eloquent...Repository` implementiert Domain Ports.
     - **Filament 5 Integration**: Filament Resources / Actions agieren als Primary Adapters und dispatchen Commands.
     - **MinIO & Redis**: S3 Storage Adapter & Outbox Event Worker.

---

### 🔹 Phase 4: Multilevel Test-Generierung (PHPUnit 11 / Pest)
* **Agent**: `.ai/agents/test-engineer.md`
* **Aktionen**:
  1. **Architecture Tests**: Schichten-Isolation testen.
  2. **Pure Domain Unit Tests**: Invarianten, States, Strategies & Specifications (ohne DB, <5ms).
  3. **Application Tests**: CQRS Handlers mit In-Memory Repositories.
  4. **Integration Tests**: Data Mapper, Eloquent Repositories, MinIO S3 & Redis Outbox.
  5. **Filament 5 Tests**: Livewire Tests für Filament Formulare und Aktionen.

---

### 🔹 Phase 5: Quality Gate & Verifikation
* **Agent**: `.ai/agents/quality-gatekeeper.md`
* **Sicherheits-Vorgabe (Data Loss Prevention)**:
  - **VERBOTEN**: `artisan migrate:fresh`, `db:wipe` oder `migrate:reset`.
  - Schema-Updates dürfen ausschließlich **inkrementell** via `./zenv artisan migrate` ausgeführt werden.
* **Aktionen**:
  1. Migrationen prüfen/ausführen: `./zenv artisan migrate` (nicht-destruktiv).
  2. Tests ausführen: `./zenv artisan test` (oder `./zenv php vendor/bin/phpunit`).
  3. Code Style & Pint ausführen: `./zenv php vendor/bin/pint --test`.
  4. Bei Fehlern: Automatisierte Korrekturschleife mit `enterprise-implementer.md`.
  5. Bei Erfolg: Feature ist verifiziert und produktionsbereit.

---

### 🔹 Phase 6: Management-Handbuch & Bedienungsanleitung
* **Agent**: `.ai/agents/management-doc-writer.md`
* **Input**: 
  - `01-strategic-analysis.md` (Ubiquitous Language)
  - `02-tactical-blueprint.md` (Geschäftsregeln & Invarianten)
  - Implementierte Filament/Livewire Ressourcen
* **Ziel**: Erstellung einer leicht verständlichen, professionellen Dokumentation für Management, Product Owner und Endanwender.
* **Inhalte**:
  1. **Executive Summary & Business Value**: Welches Problem wird gelöst?
  2. **Funktionsübersicht**: Tabellarischer Überblick aller Kernfunktionen.
  3. **Klick-Pfade & Schritt-für-Schritt How-To**: Wie werden Produkte angelegt, Varianten gepflegt und freigegeben?
  4. **Geschäftsregeln & Schutzmechanismen**: Erklärung der System-Invarianten ohne technischen Jargon.
  5. **Effizienz-Tipps**: Such-, Filter-, Sortierfunktionen, Inline-Editing und Bulk Actions.
  6. **FAQ & Fehlerbehebung**: Antworten auf häufige Anwenderfragen.
* **Output-Artefakt**: `docs/user-guides/[feature-name]-manual.md`

---

## 🛠️ Schnellstart-Befehl für neue Features

Um ein neues Feature durch die DDD-Pipeline zu starten:

> *"Starte den DDD-Pipeline Workflow (`.ai/workflows/ddd-pipeline.md`) für folgende Anforderung: [Hier Anforderung einfügen]"*
