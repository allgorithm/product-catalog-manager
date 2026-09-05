# Strategic Domain Analysis: Product Catalog Management

## 1. Ubiquitous Language (Glossar)

| Fachbegriff | Definition | Gültig in Context | Verbotene / Veraltete Begriffe |
| :--- | :--- | :--- | :--- |
| **Product** | Das zentrale Angebot im Katalog. Besitzt Stammdaten, Status, Kategorie und Medien. | CatalogContext | Item, Article, Entry |
| **ProductVariant** | Eine spezifische physische Ausprägung eines Produkts (definiert durch Größe und Farbe). | CatalogContext | Subproduct, ChildItem |
| **SKU** | Eindeutiger Stock Keeping Unit Identifier (Produkt- & Variantenebene). | CatalogContext | Artikelnummer, Barcode |
| **Price / Money** | Geldbetrag in kleinster Währungseinheit (Cents) mit Währungscode (z. B. EUR). | CatalogContext | Decimal, Cost |
| **ProductStatus** | Lebenszyklus eines Produkts: `DRAFT`, `ACTIVE`, `ARCHIVED`. | CatalogContext | StateFlag, IsActive |
| **Category** | Hierarchische Klassifikation für Produkte. | CatalogContext | Tag, Folder |
| **Size** | Attribut einer Variante (z. B. XS, S, M, L, XL, 42). | CatalogContext | Dimension |
| **Color** | Attribut einer Variante mit Name und optionalem Hex-Code. | CatalogContext | Shade |

---

## 2. Event Storming Stream

```
[Merchant] ──► ⚡ CreateProduct ──► 📦 Product [Draft] ──► 📢 ProductCreated
     │
     ├──► ⚡ AssignCategory ──► 📦 Product ──► 📢 CategoryAssignedToProduct
     │
     ├──► ⚡ AddVariant (Size, Color, Sku, Price) ──► 📦 Product ──► 📢 VariantAddedToProduct
     │
     ├──► ⚡ AttachMedia ──► 📦 Product ──► 📢 MediaAttachedToProduct
     │
     ├──► ⚡ ActivateProduct ──► 📦 Product [Active] ──► 📢 ProductActivated
     │                           (Invariant: min. 1 Category, min. 1 Variant, min. 1 Price)
     │
     └──► ⚡ ArchiveProduct ──► 📦 Product [Archived] ──► 📢 ProductArchived
```

---

## 3. Bounded Contexts & Subdomain-Typisierung

1. **`CatalogContext` (Core Domain)**:
   - Verwaltung des Produktstamms, Variantenmatrix, Kategorisierung und Medienzuordnung.
2. **`PricingContext` (Supporting Domain / Embedded)**:
   - Preisgestaltung, Währungsumrechnung und Preisregeln.
3. **`MediaStorageContext` (Generic Domain)**:
   - Verwaltung der Asset-Dateien auf MinIO/S3.

---

## 4. Context Map & Integrationsmuster

* **CatalogContext -> MediaStoragePort (S3/MinIO)**: *Customer/Supplier* über ein entkoppeltes Interface (`StoragePortInterface`).
* **CatalogContext -> EventBus / Redis Outbox**: *Publisher/Subscriber* zur asynchronen Benachrichtigung externer Systeme.
