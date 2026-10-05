# Prompt Box API

Prompt Box API is the Laravel backend for **Prompt Box**, a digital-product application built on the Armin API foundation.

It provides the API, authentication, authorization, catalog management, access control, orders, payments, subscriptions, and other backend services required by the Prompt Box product.

## Role in Prompt Box

Prompt Box consists of three independent applications:

* **Prompt Box API** — Laravel backend and source of truth for business rules and data.
* **Prompt Box Admin** — React-based administrative interface.
* **Prompt Box Web** — Next.js public-facing application.

```text
                         Prompt Box
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

The API is the authoritative backend. Admin and Web consume the API and do not own authoritative business rules.

## Technology

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

## Core Responsibilities

The Prompt Box API provides backend capabilities for:

* Authentication and user management
* User and admin authorization
* Prompt Box item catalog
* Categories and tags
* Media and files
* Free and paid access
* Orders and order items
* Payments and payment gateways
* ZarinPal integration
* Subscription plans and subscriptions
* Protected content access
* Stable API resources and HTTP contracts

Business-critical decisions remain on the backend, including:

* Authorization
* Access control
* Pricing
* Order state
* Payment state
* Subscription state
* User entitlements
* Protected content access

Frontend applications consume these rules through the API.

## Architecture

Prompt Box API is based on the reusable Armin API architecture.

The backend follows:

* API First
* Backend / Frontend Separation
* Thin Controllers
* Explicit Business Rules
* Meaningful Services
* Form Requests for meaningful validation
* Reusable Components
* Testable Code
* Docker First
* Stable API Contracts
* Convention Over Configuration
* Avoid Premature Abstraction

Product-specific requirements belong in Prompt Box. Reusable architectural improvements should only be introduced into the Armin Template when they represent a genuine reusable requirement.

## Development

Start the Docker environment with:

```bash
docker compose up -d
```

Run the test suite with:

```bash
docker compose exec app php artisan test
```

The API is intended to be developed and tested inside the Docker environment.

## Armin Foundation

Prompt Box API was created from the stable **Armin API Template**.

The reusable Armin foundation provides the technical architecture and common backend capabilities, while this repository represents the independent Prompt Box product instance.

```text
Armin API Template
        ↓
Prompt Box API
        ↓
Prompt Box production
```

The Prompt Box instance owns product-specific configuration, content, catalog data, domain configuration, and production credentials.

## Payment Configuration

Prompt Box uses the payment gateway infrastructure provided by the Armin API foundation, including ZarinPal integration.

Real production payment credentials belong exclusively to the Prompt Box environment.

Production secrets must never be committed to the repository.

```text
Armin Foundation
    └── Payment gateway integration

Prompt Box
    └── Production payment credentials
```

## Documentation

The repository also contains the Armin foundation documentation under:

```text
Armin Documentation/
```

These documents describe the reusable Armin architecture, capabilities, and boundaries inherited by Prompt Box.

Prompt Box-specific product decisions, configuration, content, and deployment details belong to this product repository and its product-specific documentation.

## Status

**Productionization in Progress**

The Prompt Box API is the backend instance being prepared for the Prompt Box product.

The underlying Armin API foundation is complete and stable. Product-specific configuration, content, production settings, and deployment are handled separately in this repository.

## Repository Structure

```text
prompt-box-api/
│
├── app/
├── bootstrap/
├── config/
├── database/
├── routes/
├── tests/
├── Armin Documentation/
├── README.md
└── ...
```

The API repository is the source of truth for Prompt Box backend implementation and product-specific backend configuration.
