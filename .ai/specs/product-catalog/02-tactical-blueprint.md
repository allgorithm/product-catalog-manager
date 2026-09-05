# Tactical Design Blueprint: Product Catalog Management

## 1. Design Patterns Matrix (nach [DesignPatternsPHP](https://designpatternsphp.readthedocs.io/en/latest/))

| Kategorie | Gewähltes Pattern | Konkreter Einsatz im Feature |
| :--- | :--- | :--- |
| **Creational** | **Factory Method / Static Factory** | `Sku::fromString()`, `Money::fromCents()`, `Product::create()` mit Validierung. |
| **Creational** | **Builder Pattern** | `ProductBuilder` zum flüssigen Aufbau komplexer Produkte in Tests und Seeder. |
| **Behavioral** | **State Pattern** | Kapselung des Produkt-Lebenszyklus (`DraftState`, `ActiveState`, `ArchivedState`). |
| **Behavioral** | **Specification Pattern** | `ProductCanBeActivatedSpecification` prüft Freigabekriterien unabhängig von Frameworks. |
| **Behavioral** | **Command Pattern (CQRS)** | `CreateProductCommand`, `AddVariantCommand`, `ChangeProductStatusCommand`, `BulkArchiveProductsCommand`. |
| **Structural** | **Data Mapper Pattern** | `ProductDataMapper` übersetzt zwischen reinem Domain-Aggregat und `App\Models\ProductModel` (Eloquent). |
| **Structural** | **Adapter Pattern** | `MinioProductMediaStorageAdapter` bindet MinIO S3 an das `MediaStoragePortInterface` an. |

---

## 2. Domänenmodell & Taktische Bausteine

### 2.1 Value Objects (Immutabel, `final readonly`)
* **`ProductId`**: UUID v7 Identifier.
* **`VariantId`**: UUID v7 Identifier.
* **`CategoryId`**: UUID v7 / Integer ID.
* **`Sku`**: Format-validierte Stock Keeping Unit (z. B. `PROD-100-M-RED`).
* **`Money`**: Betrag in Cents (`amount`) + Währung (`currency`, Default: EUR).
* **`Size`**: Konfektionsgröße (`value`: XS, S, M, L, XL, etc.).
* **`Color`**: Farbe mit `name` (z. B. "Deep Navy") und optionalem `hexCode` ("#000080").
* **`ProductMedia`**: `url`, `altText`, `isPrimary` Flag.

### 2.2 Aggregate Root: `Product`
```
┌────────────────────────────────────────────────────────────────────────┐
│                        AGGREGATE ROOT: Product                         │
│                                                                        │
│  - id: ProductId                                                       │
│  - name: string                                                        │
│  - sku: Sku (Base SKU)                                                 │
│  - categoryId: CategoryId                                              │
│  - status: ProductStatus (Enum / State)                                │
│  - basePrice: Money                                                    │
│  - media: Collection<ProductMedia>                                     │
│  - variants: Collection<ProductVariant>                                │
│                                                                        │
│  + create(id, name, sku, categoryId, basePrice): Product               │
│  + addVariant(sku, size, color, price): ProductVariant                 │
│  + removeVariant(variantId): void                                      │
│  + attachMedia(media): void                                            │
│  + activate(specification: ProductCanBeActivatedSpecification): void   │
│  + archive(): void                                                     │
│  + changePrice(newPrice: Money): void                                  │
└────────────────────────────────────────────────────────────────────────┘
```

### 2.3 Entity: `ProductVariant` (im Aggregate)
* **Identität**: `VariantId`
* **Attribute**: `Sku`, `Size`, `Color`, `Price` (optional überschreibend zu `basePrice`).
* **Invariante**: Innerhalb eines Produkts darf die Kombination aus `Size` + `Color` **nicht doppelt** existieren.

### 2.4 Invarianten & Geschäftsregeln
1. **Aktivierungs-Invariante**: Ein Produkt kann nur aktiviert werden, wenn:
   - Mindestens eine Kategorie zugewiesen ist.
   - Mindestens eine Variante existiert.
   - Ein gültiger Preis (> 0) hinterlegt ist.
   - Geprüft über `ProductCanBeActivatedSpecification::isSatisfiedBy($product)`.
2. **Archivierungs-Invariante**: Ein archiviertes Produkt kann nicht mehr direkt modifiziert werden (nur Re-Aktivierung oder Entwurf).

---

## 3. Ports (Schnittstellen im Domain Layer)

* **`ProductRepositoryInterface`**:
  - `save(Product $product): void`
  - `findById(ProductId $id): ?Product`
  - `delete(ProductId $id): void`
* **`CategoryRepositoryInterface`**:
  - `save(Category $category): void`
  - `findById(CategoryId $id): ?Category`

---

## 4. Application Layer (CQRS Use-Cases)

### Write Side (Commands):
1. `CreateProductCommand` & `CreateProductCommandHandler`
2. `AddVariantToProductCommand` & `AddVariantToProductCommandHandler`
3. `ChangeProductStatusCommand` & `ChangeProductStatusCommandHandler`
4. `BulkChangeProductStatusCommand` & `BulkChangeProductStatusCommandHandler` (für Filament Bulk Actions)

### Read Side (Queries & DTOs):
1. `ListProductsQuery` (Filter nach Category, Status, SKU-Suche, Sortierung, Pagination).
2. `GetProductDetailsQuery` (Vollständige Ansicht inkl. Variantenmatrix für Filament Edit Page).

---

## 5. Infrastructure & Filament 5 / Livewire Integration

* **Filament Resource**: `App\Filament\Resources\ProductResource`
  - **Table View**:
    - Spalten: SKU, Name, Kategorie-Badge, Status-Badge, Preis, Varianten-Count, Primary-Image.
    - **Livewire & Table Performance**: `deferLoading()`, Eager Loading (`category`, `media`, `variants`).
    - **Inline Editing**: Status per Toggle/Select ändern, Basispreis direkt editieren.
    - **Filter**: Filter nach `category_id`, `status` (Tabs: All, Draft, Active, Archived).
    - **Suche**: Global Search über Name und Varianten-SKUs.
    - **Bulk Actions**: `BulkActivateAction`, `BulkArchiveAction`.
  - **Form View (Tabs)**:
    - Tab 1: **Stammdaten & Kategorie** (Name, Basis-SKU, Category-Select, Basispreis).
    - Tab 2: **Varianten-Matrix** (Repeater mit Size, Color, Variant-SKU, Variant-Preis).
    - Tab 3: **Medien / Bilder** (S3/MinIO FileUpload mit Vorschau).
* **Data Mapper**:
  - `ProductDataMapper`: Konvertiert bidirektional zwischen `Product` (Domain) und `App\Models\Product` (Eloquent mit `hasMany(variants)`, `belongsTo(category)`).
