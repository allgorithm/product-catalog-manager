<div align="center">

# ⚡️ Enterprise Product Catalog Manager (PCM)

### **Laravel 13** · **Filament 5** · **PHP 8.4** · **Pure Domain-Driven Design (DDD)**

[![Tests](https://img.shields.io/badge/Tests-100%25%20Passing%20(Pest%2FPHPUnit)-brightgreen?style=for-the-badge&logo=pest)](tests/)
[![Architecture](https://img.shields.io/badge/Domain%20Core-0%20Framework%20Dependencies-blueviolet?style=for-the-badge)](app/Domain/)
[![Code Style](https://img.shields.io/badge/Code%20Style-Laravel%20Pint-orange?style=for-the-badge&logo=laravel)](https://laravel.com/docs/pint)
[![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)](LICENSE)

<p align="center">
  <b>Tired of anemic models, untestable controllers, and spaghetti CRUD?</b><br>
  This project is a clean, production-grade reference architecture showing how <b>Pure DDD, Hexagonal Architecture, CQRS</b>, and <b>Filament 5</b> come together in a high-performance eCommerce core.
</p>

⭐ **If you find this architecture inspiring or useful for your projects, please give it a Star!** ⭐

[📖 Management Manual](docs/user-guides/product-catalog-manual.md) · [📐 Tactical Blueprint](.ai/specs/product-catalog/02-tactical-blueprint.md) · [📝 Changelog](changelog.md)

</div>

---

## 🌟 Why Star This Repository?

Most Laravel tutorials stop at primitive MVC with fat models and database-coupled controllers. **This repository demonstrates real-world software craftsmanship:**

* 💎 **100% Framework-Free Domain Core (`app/Domain/`)**: Zero `Illuminate\*` or `Filament\*` imports. The business logic lives independently and can run in any PHP runtime.
* 🛡️ **Specification Pattern & Invariants**: Products cannot be published with missing variants, zero pricing, or unassigned categories (`ProductCanBeActivatedSpecification`). The Aggregate protects its own consistency boundary.
* ⚡️ **Modern Filament 5 Backoffice**: Full-featured admin panel utilizing Filament 5 Schemas, inline table editing, dynamic status tabs, batch bulk actions, and nested repeaters.
* 🔄 **Explicit CQRS & Event Sourcing Ready**: Write operations run through discrete Command Handlers (`CreateProduct`, `ChangeProductStatus`, `BulkChangeProductStatus`) emitting strongly-typed Domain Events (`ProductCreated`, `ProductStatusChanged`).
* 🗄️ **Data Mapper Pattern**: Clean separation between in-memory Domain Entities and Eloquent Persistence Models via `ProductDataMapper`.
* 🧪 **Multi-Level Test Suite**: Instant pure domain unit tests (<10ms), architecture constraint tests, and database integration tests with 100% green status.
* 🛡️ **Zero Data Loss Policy**: Pre-configured with `DB::prohibitDestructiveCommands()` to protect against accidental table drops.

---

## 🏛️ Hexagonal Architecture & Bounded Context

```
                         ┌─────────────────────────────────┐
                         │      Filament 5 Backoffice      │ (UI / Presentation)
                         └────────────────┬────────────────┘
                                          │
                                          ▼
                         ┌─────────────────────────────────┐
                         │   Application Layer (CQRS)      │ (Commands & Handlers)
                         └────────────────┬────────────────┘
                                          │
                    ┌─────────────────────┴─────────────────────┐
                    ▼                                           ▼
      ┌───────────────────────────┐               ┌───────────────────────────┐
      │  Domain Layer (100% Pure) │               │   Infrastructure Layer    │
      │  - Aggregate Root         │ ◄──[ Implements ]  - Eloquent Repositories │
      │  - Entities & ValueObjects│               │  - Data Mappers           │
      │  - Specifications         │               │  - MariaDB / Redis        │
      │  - Domain Events & Ports  │               └───────────────────────────┘
      └───────────────────────────┘
```

### Domain Core Overview (`app/Domain/Catalog/`)

```
app/Domain/Catalog/
├── Model/
│   ├── Product.php                     # Aggregate Root (Invariant Enforcement)
│   ├── ProductVariant.php              # Child Entity (Matrix validation)
│   ├── Category.php                    # Entity
│   └── ValueObjects/
│       ├── ProductId.php               # Typed UUIDv7
│       ├── VariantId.php               # Typed UUIDv7
│       ├── Sku.php                     # Self-validating Value Object (Regex)
│       ├── Money.php                   # Immutable Cents & Currency Value Object
│       ├── Size.php & Color.php        # Variant Attributes
│       ├── ProductMedia.php            # Media Value Object
│       └── ProductStatus.php           # Backed Enum (DRAFT, ACTIVE, ARCHIVED)
├── Specifications/
│   └── ProductCanBeActivatedSpecification.php # Business Rule Specification
├── Events/
│   ├── ProductCreated.php
│   ├── ProductStatusChanged.php
│   └── VariantAddedToProduct.php
└── Repositories/
    ├── ProductRepositoryInterface.php  # Output Port
    └── CategoryRepositoryInterface.php # Output Port
```

---

## 🚀 Quick Start (Docker / Zenv)

```bash
# 1. Clone the repository
git clone https://github.com/allgorithm/product-catalog-manager.git
cd product-catalog-manager

# 2. Start Docker Environment
./zenv up -d

# 3. Setup Application (Migrations & Seeders)
./zenv composer setup

# 4. Run Tests & Code Quality Suite
./zenv composer test
./zenv composer csfix
```

Access the Filament 5 Admin Panel at **`http://localhost/admin`**.

---

## 🧪 Architecture & Quality Verification

This repository enforces architecture boundary tests to guarantee zero framework leakage:

```bash
# Verify that Domain layer has 0 framework dependencies:
./zenv artisan test tests/Architecture/DomainArchitectureTest.php

# Run full test suite:
./zenv artisan test
```

---

## 💫 Give it a Star!

If this project helped you understand how to implement **Clean Architecture & DDD in modern Laravel 13**, please consider giving this repo a **Star (⭐️)**! It helps more developers discover decoupled software design in the PHP ecosystem.

---

### 📄 License

Open-sourced software licensed under the [MIT license](LICENSE).