# Armin

**Reusable API-First Template for SaaS & Digital Products**

Armin is a reusable, API-first application foundation for building SaaS and digital-product applications.

It is designed to be cloned from GitHub and adapted into individual product instances, allowing future projects to start from a stable technical foundation instead of rebuilding common application infrastructure from scratch.

> **Build the reusable foundation once, then build products on top of it.**

---

## What Armin Provides

Armin consists of three independent applications:

| Application | Technology                   | Responsibility                                    |
| ----------- | ---------------------------- | ------------------------------------------------- |
| **API**     | Laravel / PHP / MySQL        | Backend, business rules, authentication, commerce |
| **Admin**   | React / Vite / TypeScript    | Administrative interface                          |
| **Web**     | Next.js / React / TypeScript | Public-facing application                         |

The applications communicate through a versioned REST API.

```text
                         Armin
                           │
          ┌────────────────┼────────────────┐
          │                │                │
          ▼                ▼                ▼
        API              Admin             Web
      Laravel          React/Vite        Next.js
          │                │                │
          └────────────────┼────────────────┘
                           │
                     REST API Contract
```

The Laravel API is the backend **source of truth**. Admin and Web are independent API consumers.

---

## Core Capabilities

The current Template provides a reusable foundation for:

* Authentication and session management
* User and role management
* Admin authorization
* Digital item management
* Categories and tags
* Media and file management
* Free and paid access
* Orders and order items
* Payment processing
* Payment gateway abstraction
* ZarinPal integration
* Subscription plans
* Subscriptions
* Admin dashboard and management workflows
* Public catalog and item discovery
* Search, filtering, and sorting
* Responsive interfaces
* RTL / LTR support
* Persian / English foundations
* Reusable frontend UI components

For the complete implementation inventory, see [`FEATURES.md`](FEATURES.md).

---

## Architecture

Armin follows a deliberately simple architecture:

* API First
* Backend / Frontend Separation
* Thin Controllers
* Explicit Business Rules
* Meaningful Services
* Reusable Components
* Testable Code
* Docker First
* Stable API Contracts
* Convention Over Configuration
* Avoid Premature Abstraction

The backend intentionally avoids unnecessary infrastructure such as generic repository layers, universal service abstractions, entitlement engines, event-driven architecture, and microservices until real product requirements justify them.

For the complete architectural decisions and boundaries, see [`ARCHITECTURE.md`](ARCHITECTURE.md).

For the complete ecosystem definition and Template → Product Instance lifecycle, see [`ARMIN.md`](ARMIN.md).

---

## Repository Structure

The Armin ecosystem consists of three independent repositories/applications:

```text
Armin Ecosystem
│
├── Laravel API
│   └── Backend / Business Logic / REST API
│
├── React + Vite Admin
│   └── Administrative Application
│
└── Next.js Web
    └── Public Application
```

The applications are intentionally separated so each can evolve independently while sharing the API contract.

---

## API

The API is built with:

* Laravel 13
* PHP 8.4
* MySQL
* Laravel Sanctum
* REST API
* Docker
* Nginx
* PHP-FPM

The API is versioned under:

```text
/api/v1
```

The backend owns business-critical decisions such as:

* Authorization
* Access control
* Pricing
* Orders
* Payments
* Subscription state
* Content protection

Frontend applications must consume these rules rather than reimplement them.

See [`API Contract.md`](API%20Contract.md) for the HTTP API reference.

---

## Admin

The Admin application is built with:

* React
* Vite
* TypeScript
* Tailwind CSS
* React Router
* Axios
* i18n/localization infrastructure

Current administrative areas include:

* Dashboard
* Items
* Categories
* Orders
* Users
* Subscription Plans

The Admin application is fully API-driven and includes reusable UI infrastructure for forms, tables, filters, pagination, media management, feedback states, localization, RTL/LTR, and responsive layouts.

---

## Web

The public Web application is built with:

* Next.js
* React
* TypeScript
* Tailwind CSS

The current Template Web foundation includes:

* Home
* Public item catalog
* Item details
* Search
* Category filtering
* Sorting
* Responsive presentation
* RTL / LTR
* Persian / English foundations
* Dark-mode-oriented visual foundation
* Reusable UI components

The Web application is independent from the backend implementation and communicates through the API.

---

## Current Status

**Armin Template: Complete / Stable**

| Component          | Status                |
| ------------------ | --------------------- |
| Laravel API        | Complete / Stable     |
| React + Vite Admin | Complete / Stable     |
| Next.js Web        | Complete / Stable     |
| Armin Template     | **Complete / Stable** |

The Template development phase is complete.

Future improvements may still be introduced when real product requirements justify them, but there are currently no known blockers in the Template baseline.

---

## From Template to Product

Armin is intended to be used as a starting point for future products.

```text
GitHub
   │
   ▼
Armin Template
   │
   ▼
Clone
   │
   ▼
Product Instance
   │
   ├── Branding
   ├── Product content
   ├── Product-specific copy
   ├── Real catalog data
   ├── Domain
   ├── Production configuration
   └── Production credentials
```

The first real product instance built on Armin is **Prompt Box**.

Prompt Box-specific work belongs to the product instance, not to the generic Armin Template.

---

## Payment Configuration

Armin includes the ZarinPal integration required by supported payment workflows.

The Template does **not** contain real production payment credentials.

Production credentials are configured for each individual product instance during deployment.

Therefore:

```text
Armin Template
    └── ZarinPal integration

Prompt Box
    └── Production ZarinPal credentials
```

This separation keeps the public/reusable Template safe and product-independent.

---

## Documentation

| Document                               | Purpose                                                                      |
| -------------------------------------- | ---------------------------------------------------------------------------- |
| [`ARMIN.md`](ARMIN.md)                 | Ecosystem definition, vision, lifecycle, and Template → Product relationship |
| [`ARCHITECTURE.md`](ARCHITECTURE.md)   | Technical architecture and architectural decisions                           |
| [`FEATURES.md`](FEATURES.md)           | Implemented capabilities and feature boundaries                              |
| [`API Contract.md`](API%20Contract.md) | API endpoints, contracts, and business semantics                             |
| [`SESSION.md`](SESSION.md)             | Current project checkpoint                                                   |

---

## Development Philosophy

Armin is intentionally pragmatic.

The goal is not to create the most abstract or sophisticated framework possible.

The goal is to create a foundation that is:

**Stable · Understandable · Reusable · Testable · Maintainable**

New abstractions should be introduced because a real product requirement needs them, not because the architecture might need them someday.

---

## License

License information should be defined according to the intended GitHub distribution model before the Template is published for broader reuse.
