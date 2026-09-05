# Laravel Product Catalog

Ein domänenorientiertes E-Commerce-Produktkatalog-System,
entwickelt mit Zenv, Laravel und Filament.

Das Projekt demonstriert die Umsetzung eines realistischen
E-Commerce-Domänenmodells mit Domain-Driven Design (DDD).

Der Schwerpunkt liegt nicht auf einem einfachen CRUD-Backend,
sondern auf der Modellierung von Geschäftsregeln, Invarianten,
Value Objects, Domain Events und klar definierten Domänengrenzen.

## Architektur

Die Catalog-Domäne ist als eigenständiger Bounded Context strukturiert:

    app/Domain/Catalog/

    ├── Model/
    │   ├── Product.php
    │   ├── ProductVariant.php
    │   ├── Category.php
    │   └── ValueObjects/
    │       ├── ProductId.php
    │       ├── VariantId.php
    │       ├── Sku.php
    │       ├── Money.php
    │       ├── Size.php
    │       ├── Color.php
    │       ├── ProductMedia.php
    │       └── ProductStatus.php
    │
    ├── Specifications/
    │   └── ProductCanBeActivatedSpecification.php
    │
    ├── Events/
    │   ├── ProductCreated.php
    │   ├── ProductActivated.php
    │   └── VariantAddedToProduct.php
    │
    └── Repositories/
        ├── ProductRepositoryInterface.php
        └── CategoryRepositoryInterface.php

## Domain Model

`Product` bildet den Aggregate Root des Product-Aggregates.

Der Aggregate Root kapselt die relevanten Invarianten und
kontrolliert Änderungen am Aggregate.

    Product
       │
       ├── ProductVariant
       │      ├── VariantId
       │      ├── Sku
       │      ├── Size
       │      └── Color
       │
       ├── Money
       │
       ├── ProductMedia
       │
       └── ProductStatus

## Value Objects

Domänenrelevante Werte werden nicht als primitive Datentypen
durch die Anwendung transportiert.

Beispiele:

- `ProductId` – typisierte UUIDv7
- `VariantId` – typisierte UUIDv7
- `Sku` – validierte Artikelnummer
- `Money` – unveränderlicher Geldbetrag in Cents
- `Size` – Variantenmerkmal
- `Color` – Variantenmerkmal
- `ProductMedia` – Medieninformationen
- `ProductStatus` – typisierter Produktstatus

Dadurch werden Validierung und Domänenregeln möglichst nah
an den jeweiligen Werten und Konzepten modelliert.

## Geschäftsregeln

Die Aktivierung eines Produkts ist beispielsweise keine einfache
Statusänderung.

Ein Produkt kann nur aktiviert werden, wenn:

- mindestens eine Kategorie vorhanden ist
- mindestens eine Variante vorhanden ist
- ein gültiger Preis größer als 0 vorhanden ist

Diese Regel wird durch eine Specification ausgedrückt:

    ProductCanBeActivatedSpecification

Dadurch bleibt die Geschäftsregel unabhängig von Filament
und der administrativen Benutzeroberfläche.

## Domain Events

Domänenrelevante Änderungen erzeugen Events.

Beispiele:

    ProductCreated
    ProductActivated
    VariantAddedToProduct

Diese Events ermöglichen es, weitere Prozesse anzubinden,
ohne die Catalog-Domäne direkt mit diesen Prozessen zu koppeln.

## Repository Ports

Die Domäne definiert ihre benötigten Repository-Schnittstellen:

    ProductRepositoryInterface
    CategoryRepositoryInterface

Die konkrete Persistenz wird dadurch von der Domäne entkoppelt.

Die Repository Interfaces bilden dabei die Output Ports
der Domäne.

## Admin Backend

Das administrative Backend wird mit Filament umgesetzt.

Filament übernimmt die Präsentations- und Administrationsschicht,
während die eigentlichen Geschäftsregeln innerhalb der Domäne
liegen.

    Filament
        │
        ▼
    Application
        │
        ▼
    Domain
        │
        ├── Aggregate
        ├── Value Objects
        ├── Specifications
        ├── Domain Events
        └── Repository Ports

## Technischer Stack

- Zenv
- PHP
- Laravel
- Filament
- MySQL / PostgreSQL
- Pest
- PHPStan
- Laravel Pint
- Git

## Ziel des Projekts

Das Projekt zeigt, wie ein typisches E-Commerce-Problem mit
Laravel strukturiert werden kann, wenn die Geschäftsdomäne
und nicht das Framework den Mittelpunkt der Architektur bildet.

Im Fokus stehen:

- Domain-Driven Design
- Aggregate und Invarianten
- Value Objects
- Specifications
- Domain Events
- Repository Ports
- klare Verantwortlichkeiten
- testbare Geschäftslogik
- Filament als Admin UI
- Laravel als Application-/Infrastructure-Framework

Das Projekt ist bewusst klein gehalten.

Die Komplexität liegt nicht in der Anzahl der Features,
sondern in der Qualität des Domänenmodells.