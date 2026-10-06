# Armin Features

This document is the implementation inventory for the Armin Template.

It describes the capabilities currently included in the stable Template baseline.

It focuses on the current stable baseline. Future extensions are documented as future evolution opportunities, not as current Template requirements.

---

## 1. Current Status

**Armin Template: Complete / Stable**

All three applications are currently considered complete and stable at the intended Template scope.

| Application        | Status                |
| ------------------ | --------------------- |
| Laravel API        | Complete / Stable     |
| React + Vite Admin | Complete / Stable     |
| Next.js Web        | Complete / Stable     |
| Armin Template     | **Complete / Stable** |

---

# 2. Laravel API

## 2.1 Authentication

The API provides:

* User registration
* Login
* Logout
* Current-user endpoint
* Laravel Sanctum authentication
* Token-based API authentication
* Request validation
* Authentication rate limiting
* Normalized-email-aware login throttling
* Role-based authorization

---

## 2.2 Roles and Authorization

The current role model includes:

* `user`
* `admin`

Administrative endpoints require authenticated administrative access.

Authorization is enforced by the API rather than trusted to frontend applications.

---

## 2.3 Users

The API supports administrative user management including:

* User listing
* Search
* Role filtering
* Sorting
* Pagination
* User details
* User name updates
* User role updates

---

## 2.4 Items

`Item` is the core product-neutral digital-content entity.

The API supports:

* Item creation
* Item editing
* Item deletion
* Item details
* Public item listing
* Public item detail
* Visibility management
* Free / paid access type
* Pricing
* Description
* Content
* Slugs
* Category relationships
* Tag relationships
* Search
* Filtering
* Sorting
* Pagination

Public item representations do not expose protected content through normal catalog responses.

Hidden items are excluded from public discovery.

---

## 2.5 Categories

The API supports:

* Public category listing
* Administrative listing
* Search
* Sorting
* Pagination
* Create
* Show
* Update
* Delete

Deleting a category removes its item relationships according to the current relationship rules without deleting the associated items.

---

## 2.6 Tags

Tags are managed through item relationships rather than a standalone tag-management module.

The current implementation supports:

* Attaching tags to items
* Replacing item tags
* Free-form tag input
* Case-insensitive tag reuse
* Automatic tag creation when required
* Tag-based item filtering

There is intentionally no standalone Tag administration section.

---

## 2.7 Media

The API supports item media management including:

* Image upload
* Multiple media per item
* Primary media
* Media ordering
* Media deletion
* File metadata
* Public file URLs where appropriate
* Shared-file protection
* Cleanup of unreferenced files

The current image upload limit is **5 MB**.

Media is managed in the context of Items rather than through a standalone media-management system.

---

## 2.8 Files

Files provide storage metadata for media.

The current architecture does not expose a standalone generic File API.

File lifecycle is managed through media relationships and reference integrity.

---

## 2.9 Access Control

The API supports:

* Free item access
* Paid item access
* Direct user access
* Subscription-based access
* Hidden item protection
* Authentication requirements for protected access

Access decisions are centralized through:

```text id="7w0e1f"
AccessResolver::canAccess(User $user, Item $item): bool
```

Current semantics include:

* Authenticated users can access free items.
* Paid items require direct access or an active global subscription.
* Expired subscriptions do not revoke separately granted direct access.
* Hidden items are protected from normal protected-content access.
* Guests receive authentication-required responses for protected access endpoints.

---

## 2.10 Orders

The API supports:

* Item orders
* Subscription orders
* Order items
* Order status
* Price snapshots
* Order ownership
* User order history
* Administrative order listing
* Administrative order details
* Pagination
* Filtering
* Deterministic ordering

The price stored on an `OrderItem` represents the historical purchase price.

---

## 2.11 Payments

The API supports:

* Payment creation
* Payment initiation
* Gateway interaction
* Payment callbacks
* Payment verification
* Payment status
* Gateway reference tracking
* Payment retry workflows
* Ownership validation
* Transactional fulfillment

Payment state and order state are handled according to the backend commerce rules.

There is intentionally no standalone Payment administration page in Admin; payment information is exposed through the relevant order workflow.

---

## 2.12 Payment Gateways

The current gateway architecture supports:

* Fake gateway
* ZarinPal gateway

A gateway abstraction/resolver allows payment providers to remain separated from core order logic.

ZarinPal integration is part of the Template.

Production credentials are configured per product instance and are not part of the Template.

---

## 2.13 Subscription Plans

The API supports:

* Subscription plan listing
* Active/inactive state
* Plan creation
* Plan details
* Plan updates
* Duration
* Pricing
* Public active-plan listing

Current public endpoint:

```text id="v2q5zm"
GET /api/v1/subscription-plans
```

The public endpoint exposes active plans according to the API contract.

---

## 2.14 Subscriptions

The API supports:

* Subscription creation through subscription orders
* User subscriptions
* Subscription plans
* Start and end timestamps
* Active state
* Future state
* Expired state
* Price snapshots
* Order/payment relationships

The current subscription model is global rather than item-specific.

---

## 2.15 Dashboard

The Admin API provides dashboard statistics currently including:

* Total users
* Total items
* Total categories

---

# 3. React + Vite Admin

The Admin application is a fully implemented administrative client for the Armin API.

## 3.1 Authentication

Admin provides:

* Login
* Session restoration
* Authenticated routing
* Admin-role protection
* Auth-aware redirects
* Logout
* Non-admin access protection

---

## 3.2 Dashboard

The dashboard provides API-backed statistics including:

* Users
* Items
* Categories

---

## 3.3 Items

Admin supports:

* Item listing
* Search
* Filtering
* Sorting
* Pagination
* Item creation
* Item editing
* Item details
* Item deletion
* Free/paid configuration
* Price management
* Category selection
* Tag management
* Media upload
* Media deletion
* Primary media selection
* Media ordering
* Responsive workflows

---

## 3.4 Categories

Admin supports:

* Category listing
* Search
* Sorting
* Pagination
* Create
* Edit
* Details
* Delete
* Responsive workflows

---

## 3.5 Orders

Admin supports:

* Order listing
* Search/filter workflows
* Order status filtering
* User filtering
* Payment-status filtering
* Pagination
* Deterministic ordering
* Order details
* User information
* Item information
* Subscription information
* Subscription plan information
* Payment information

---

## 3.6 Users

Admin supports:

* User listing
* Search
* Role filtering
* Sorting
* Pagination
* User details
* Name updates
* Role updates
* Responsive workflows

---

## 3.7 Subscription Plan Management

Admin provides a dedicated Subscription Plan Management workflow supporting:

* Plan listing
* Pagination
* Plan creation
* Plan details
* Plan editing
* Active/inactive state
* Duration
* Pricing
* Navigation
* Responsive presentation

There is intentionally no standalone full subscription-lifecycle administration module.

---

## 3.8 Shared Admin UI Infrastructure

The Admin application includes reusable components and patterns for:

* Buttons
* Icon-only action buttons
* Inputs
* Selects
* Textareas
* Cards
* Tables
* Badges
* Filters
* Pagination
* Form action controls
* Media management
* Tag input
* Page headers
* Back navigation
* Loading states
* Error states
* Empty states
* Confirmation dialogs
* Toast feedback
* Theme support
* Language switching
* RTL/LTR layouts

Shared components are extracted where a genuine repeated UI pattern exists.

---

## 3.9 Admin Infrastructure

Admin includes:

* Central API client
* Centralized API types
* Authentication state
* Protected routes
* Lazy-loaded routes
* Code splitting
* Localization
* Responsive layouts
* Theme infrastructure
* Consistent feedback handling

The application has been validated through TypeScript/build checks, linting, and manual functional verification.

A dedicated automated frontend test suite is not currently part of the Template baseline.

---

# 4. Next.js Web

The Web application is the public-facing application of the Armin Template.

## 4.1 Public Pages

Current public areas include:

* Home
* Items
* Item details
* Public catalog/discovery

---

## 4.2 Catalog and Discovery

Web supports:

* Item listing
* Search
* Category filtering
* Sorting
* Item details
* Responsive presentation

---

## 4.3 Responsive UX

The public Web application supports responsive layouts across:

* Desktop
* Tablet
* Mobile

Filtering and sorting controls adapt to the available viewport while maintaining a consistent interaction model.

---

## 4.4 Localization

The Web foundation supports:

* Persian
* English
* RTL
* LTR

Text and layout direction adapt according to the selected language.

---

## 4.5 Theme

The Web visual foundation supports a modern dark-oriented presentation while retaining the underlying theme structure required for future extension.

---

## 4.6 Reusable UI

The Web application uses reusable UI patterns and components to avoid unnecessary duplication across public pages.

---

# 5. Shared Ecosystem Capabilities

Across the three applications, the Template provides foundations for:

### API Integration

Admin and Web communicate with the Laravel API rather than accessing the database directly.

### Authentication

Authentication state originates from the API.

### Authorization

Backend authorization remains authoritative.

### Localization

Frontend applications support localized presentation and RTL/LTR behavior where applicable.

### Responsive Design

Both frontend applications are designed for responsive use.

### Reusable Components

Common UI patterns are implemented as reusable components where appropriate.

---

# 6. Feature Boundaries

The following are intentionally **not** standalone modules in the current Template:

* Tag administration
* Standalone media administration
* Standalone file administration
* Standalone access administration
* Standalone payment administration
* Full subscription lifecycle administration
* Settings management
* Notifications
* Activity/audit log
* Advanced permission management
* Subscription tier management

Their absence is intentional and reflects the current Template scope.

---

# 7. Current Seed / Demo Data

The API includes development/demo seed data suitable for local testing.

The current seeded baseline includes:

* 2 users
* 8 categories
* 12 tags
* 3 active subscription plans
* 32 items
* 32 files
* 32 media records
* 0 orders
* 0 subscriptions

Every seeded Item has associated media and category/tag relationships according to the current seed rules.

The seed data is for development and demonstration and should not be treated as production content.

---

# 8. Production Boundary

The Template contains reusable implementation.

A real product instance is responsible for its own production configuration, including:

* Environment variables
* Production database
* Domain
* Hosting/infrastructure
* Real product content
* Product-specific branding
* Production payment credentials

Real ZarinPal credentials belong to the product instance and must never be committed to the reusable Template repository.

---

# 9. Future vs. Current Features

The following should not be interpreted as missing features from the current Template:

* Recurring subscriptions
* Automatic subscription renewal
* Coupons
* Discounts
* Refunds
* Taxes
* Invoices
* Additional payment providers
* Advanced analytics
* Audit systems
* CDN/object-storage architecture
* Advanced search
* Frontend automated test suites
* More advanced protected-content delivery

These are possible future extensions, not missing requirements of the current Template baseline.

---

# 10. Definition of a Stable Template

The Armin Template is considered stable when:

* API, Admin, and Web provide their intended current workflows.
* Backend business rules are centralized and tested.
* Frontends consume the documented API contract.
* Shared UI patterns are reusable where justified.
* Responsive and localization foundations work.
* Production builds succeed.
* No known blocker prevents creating a product instance from the Template.

The current Armin Template meets this baseline.

---

## Final Status

> **Armin Template — Complete / Stable**

The Template is ready to move from development into the next lifecycle stage:

> **Productionization of real product instances.**
