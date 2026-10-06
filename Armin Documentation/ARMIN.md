# Armin

**Reusable API-First Template for SaaS & Digital Products**

Armin is a reusable, API-first application foundation for building future SaaS and digital-product applications.

It is maintained as a GitHub-based template ecosystem and is intended to be cloned and adapted for individual product instances.

Armin is **not a single end-user product**. It is the technical foundation from which multiple products can be built.

---

## 1. Purpose

The purpose of Armin is to provide a stable, reusable foundation for future products without rebuilding the same application infrastructure for every project.

A new product can start from Armin and focus primarily on its product-specific requirements instead of rebuilding:

* Authentication
* User management
* Roles and authorization
* Digital item management
* Categories and tags
* Media management
* Free and paid access
* Orders and payments
* Payment gateway integration
* Subscription plans and subscriptions
* Admin management
* Public catalog and item presentation
* Responsive and localized frontend foundations

Armin provides the common foundation.

Each product instance provides its own product identity, content, configuration, and business-specific extensions.

---

## 2. Core Concept

The intended lifecycle is:

```text
                    GitHub
                       │
                       ▼
              Armin Template
          Reusable Application Base
                       │
             Clone / Create Instance
                       │
          ┌────────────┼────────────┐
          ▼            ▼            ▼
      Prompt Box    Product B     Product C
       Instance      Instance      Instance
          │
          ▼
   Product-specific work
```

The relationship is:

> **Armin is the foundation. Products are the instances built on that foundation.**

The first real product instance is **Prompt Box**.

Prompt Box is not part of the Armin Template itself. It is the first product built from the template.

---

## 3. Application Ecosystem

Armin consists of three independent applications.

```text
┌──────────────────────────────────────────────┐
│                  Armin API                   │
│          Laravel / PHP / MySQL               │
│                                              │
│   Business Logic • Auth • Commerce • Access  │
│   Payments • Subscriptions • Persistence     │
└──────────────────────┬───────────────────────┘
                       │ REST API
          ┌────────────┴────────────┐
          │                         │
          ▼                         ▼
┌─────────────────────┐   ┌─────────────────────┐
│    Armin Admin      │   │      Armin Web       │
│ React + Vite        │   │ Next.js + React      │
│ Admin Application   │   │ Public Application   │
└─────────────────────┘   └─────────────────────┘
```

### API

The Laravel API is the backend and source of truth.

It owns:

* Authentication
* Authorization
* Users
* Domain entities
* Business rules
* Access control
* Orders
* Payments
* Subscriptions
* Persistence
* API contracts

### Admin

The React + Vite application is the administrative interface.

It consumes the API and owns:

* Administrative workflows
* Tables
* Forms
* Filters
* Pagination
* Dashboard presentation
* Media management UI
* Administrative navigation
* Localization and presentation

It does not duplicate backend business rules.

### Web

The Next.js application is the public-facing interface.

It consumes the API and owns:

* Public pages
* Catalog discovery
* Search
* Filtering
* Sorting
* Item presentation
* Responsive public UX
* Public routing
* SEO-oriented presentation
* Localization and presentation

It does not own authorization, pricing, payment, or entitlement decisions.

---

## 4. Architectural Principles

Armin follows these principles:

### API First

The backend API is the stable contract between the backend and frontend applications.

### Backend / Frontend Separation

The Laravel API has no dependency on React, Next.js, Vite, or Tailwind.

### Simple Over Complex

Architecture should remain simple until real product requirements justify additional abstraction.

### Convention Over Configuration

Existing framework conventions should be preferred over unnecessary custom infrastructure.

### Thin Controllers

Controllers coordinate requests and responses. Business rules belong in appropriate domain logic and services.

### Explicit Business Rules

Important business behavior should be understandable from the codebase rather than hidden behind generic abstractions.

### Reusable Components

Shared UI and infrastructure should be reused where a real common pattern exists.

### Testable

Business-critical backend behavior should be covered by automated tests.

### Docker First

The backend development and test environment is containerized and should be treated as the canonical project environment.

### Stable API Contracts

Frontend applications depend on explicit API behavior. Changes to the contract should be deliberate and backward-compatible where appropriate.

### Avoid Premature Abstraction

Armin intentionally avoids abstractions that are not justified by current complexity.

---

## 5. Domain Model

The core domain is intentionally product-neutral.

The central content entity is:

> **Item**

`Item` is deliberately used instead of product-specific terminology such as Prompt, Pattern, or Product.

This allows the same foundation to support different digital-product applications.

Current core entities include:

* User
* Item
* Category
* Tag
* Media
* File
* Access
* Order
* OrderItem
* Payment
* SubscriptionPlan
* Subscription

Product-specific terminology can be introduced at the product-instance level without changing the generic foundation unnecessarily.

---

## 6. Access Model

Armin uses explicit access rules.

The current model supports:

* Free item access for authenticated users
* Paid item access through direct `Access`
* Paid item access through an active global subscription
* Hidden items remaining inaccessible through protected endpoints
* Direct access remaining valid independently of later subscription expiration

Armin intentionally does not introduce a generic entitlement engine, tier engine, or plan-to-item mapping system unless future product requirements justify such complexity.

The backend remains the authority for access decisions.

---

## 7. Commerce Model

The commerce foundation supports:

* Item orders
* Subscription orders
* Order items
* Price snapshots
* Payment records
* Payment gateway integration
* Payment verification
* Transactional fulfillment

The purchase price is captured as an order-item snapshot so historical orders are not dependent on later price changes.

Payment processing is separated from internal order and fulfillment state transitions.

---

## 8. Payment Gateway Model

Armin provides a payment gateway abstraction with the current implementation supporting:

* Fake gateway for development/testing
* ZarinPal integration for real payment processing

The integration is part of the reusable Template.

Production credentials are **not** part of the Template.

They are configured when an individual product instance is deployed.

For example:

```text
Armin Template
      │
      ├── ZarinPal integration
      │
      └── Gateway abstraction
             │
             ▼
        Prompt Box
             │
             └── Production ZarinPal credentials
```

Therefore, obtaining and configuring a real ZarinPal key is a **product-instance productionization task**, not unfinished Template development.

---

## 9. Subscription Model

The current subscription model supports:

* Subscription plans
* Active/inactive plan state
* Duration
* Pricing
* Subscription creation through orders
* Subscription start/end dates
* Active, future, and expired states
* Global subscription-based access to paid items

The current design intentionally does not include:

* Recurring billing
* Automatic renewals
* Tier/entitlement engines
* Plan-to-item mapping
* Complex subscription lifecycle infrastructure

Those may be considered later if a real product requires them.

---

## 10. Media Model

Media is intentionally modeled through:

```text
Item
  │
  └── Media
        │
        └── File
```

The system supports:

* Multiple media records per item
* Primary media
* Ordering
* Upload
* Delete
* File metadata
* Protection against deleting shared files

Media is not treated as a standalone generic content-management system.

Protected content delivery should remain authorization-aware.

---

## 11. Current Template Status

The Armin Template is currently considered:

> **Complete / Stable**

All three applications are complete and stable at the intended Template scope.

| Application        | Status            |
| ------------------ | ----------------- |
| Laravel API        | Complete / Stable |
| React + Vite Admin | Complete / Stable |
| Next.js Web        | Complete / Stable |
| Armin Template     | Complete / Stable |

There are no currently known blockers within the intended Template scope.

This status means the Template development phase is complete.

It does not mean the Template will never evolve. Future improvements can be introduced when real product requirements justify them.

---

## 12. Productionization

The next lifecycle stage is no longer Template development.

It is:

> **Productionization of a real product instance.**

The general lifecycle is:

```text
Template Complete
       │
       ▼
Create Product Instance
       │
       ▼
Production Configuration
       │
       ├── Domain
       ├── Server / Hosting
       ├── Environment configuration
       ├── Payment credentials
       ├── Production database
       └── Real product content
       │
       ▼
Production Deployment
       │
       ▼
Real Product
```

Production configuration belongs to each product instance.

It should not be embedded into the reusable Template.

---

## 13. Prompt Box

Prompt Box is the first real product instance built from Armin.

Its relationship to Armin is:

```text
Armin Template
      │
      ▼
     Clone
      │
      ▼
 Prompt Box
      │
      ├── Product branding
      ├── Product content
      ├── Product copy
      ├── Real catalog data
      ├── Domain
      ├── Production configuration
      └── Production payment credentials
```

Prompt Box-specific work is intentionally outside the scope of the generic Template.

For example:

* Prompt Box branding
* Prompt Box copy
* Prompt Box-specific content
* Product-specific catalog data
* Product-specific domain
* Production credentials

belong to the Prompt Box instance.

The Template should remain generic and reusable.

---

## 14. What Armin Is Not

Armin is intentionally not:

* A CMS
* WordPress-like infrastructure
* A single digital-product marketplace
* A Prompt marketplace specifically
* A Pattern marketplace specifically
* A generic enterprise framework
* A microservices architecture
* A universal billing platform
* A generic entitlement engine
* A repository-heavy architecture
* An event-driven platform
* A complete SaaS business for end users

Its purpose is narrower:

> Provide a solid, understandable, reusable application foundation for future SaaS and digital-product projects.

---

## 15. Future Evolution

Armin can evolve as real products reveal additional requirements.

Potential future areas include:

* Additional payment gateways
* More advanced protected-content delivery
* Refunds and cancellations
* Coupons and discounts
* Taxes and invoices
* Recurring subscriptions
* Subscription renewals
* More advanced subscription models
* Additional account capabilities
* More advanced search
* Reporting and analytics
* Audit capabilities
* Background jobs
* Object storage and CDN
* Caching
* Additional frontend automated tests

These are **future evolution opportunities**, not missing requirements for the current Template baseline.

New architecture should be introduced only when actual product requirements justify it.

---

## 16. Source of Truth

The responsibilities of the documentation set are intentionally separated.

### `ARMIN.md`

Defines:

* What Armin is
* Why it exists
* How the ecosystem is structured
* Template vs. Product Instance
* Current lifecycle
* Relationship to Prompt Box

### `ARCHITECTURE.md`

Defines:

* Technical architecture
* Boundaries
* Domain decisions
* Architectural principles
* Intentional non-architecture

### `FEATURES.md`

Defines:

* Implemented capabilities
* Current feature boundaries
* Stable Template functionality

### `API Contract.md`

Defines:

* HTTP API
* Endpoints
* Request/response behavior
* Status codes
* API business semantics

### `SESSION.md`

Defines:

* The current working checkpoint
* Immediate next lifecycle step

It should remain short and should not become a historical archive.

---

## 17. Guiding Principle

The central principle of Armin is:

> **Build the reusable foundation once, then build products on top of it.**

The Template should remain stable, understandable, and generic enough to serve different products without becoming an over-engineered framework.

When a future product introduces genuine complexity, that requirement should drive the next architectural decision.

Until then:

**Simple, explicit, reusable, and stable.**
