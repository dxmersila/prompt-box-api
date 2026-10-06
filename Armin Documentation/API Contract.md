# Armin API Contract

## 1. Purpose

This document defines the public HTTP contract of the Armin API.

The API is the source of truth for backend state, business rules, authorization, access control, commerce, payments, and subscriptions.

Admin and Web are API consumers.

The contract should be stable, explicit, and independent of the internal implementation details of the Laravel application.

---

# 2. Base API

All API endpoints are versioned under:

```text
/api/v1
```

The API is REST-oriented and returns JSON for normal API responses.

Payment-provider callbacks may use redirects where required by the external payment flow.

---

# 3. Authentication

Authentication uses Laravel Sanctum bearer tokens.

Authenticated requests send:

```text
Authorization: Bearer <token>
```

The API has three practical access levels:

| Role  | Description                       |
| ----- | --------------------------------- |
| Guest | Unauthenticated public consumer   |
| User  | Authenticated normal user         |
| Admin | Authenticated administrative user |

Administrative endpoints require both:

```text
auth:sanctum
```

and administrative authorization.

Frontend route protection must not be considered a security boundary. The API remains authoritative.

---

# 4. General HTTP Conventions

The API uses standard HTTP semantics where appropriate.

Common responses include:

| Status | Meaning                                    |
| ------ | ------------------------------------------ |
| `200`  | Successful request                         |
| `201`  | Resource created                           |
| `204`  | Successful request with no response body   |
| `401`  | Authentication required or invalid         |
| `403`  | Authenticated but not authorized           |
| `404`  | Resource not found or intentionally hidden |
| `422`  | Validation failure                         |
| `429`  | Rate limit exceeded                        |

Payment initiation/callback flows may additionally use redirects where required.

---

# 5. Resource Conventions

Resources are returned using Laravel API Resources where a structured resource representation is appropriate.

The API should expose stable domain data rather than internal database implementation details.

Typical collection responses include:

```text
data
links
meta
```

where pagination applies.

---

# 6. Pagination

Current pagination defaults are:

| API area           | Default |
| ------------------ | ------: |
| Public collections |      12 |
| Admin collections  |      15 |

Endpoints with different behavior must document their specific pagination rules.

Pagination should use deterministic ordering.

---

# 7. Deterministic Ordering

Paginated collections must use stable ordering.

When the primary sort field can contain duplicate values, a deterministic secondary ordering should be applied where necessary.

This prevents records from unpredictably moving between pages.

---

# 8. Public API

## 8.1 Register

```http
POST /api/v1/auth/register
```

Creates a new user account.

Validation is performed by the API.

---

## 8.2 Login

```http
POST /api/v1/auth/login
```

Authenticates a user and returns the authentication response.

Login is rate-limited.

The rate-limiting strategy considers both client IP and normalized email behavior.

---

## 8.3 Logout

```http
POST /api/v1/auth/logout
```

Invalidates the authenticated session/token according to the current Sanctum authentication implementation.

Requires authentication.

---

## 8.4 Current User

```http
GET /api/v1/auth/me
```

Returns the currently authenticated user.

Requires authentication.

---

## 8.5 Email Verification Notification

```http
POST /api/v1/auth/email/verification-notification
```

Sends an email verification notification for the authenticated user.

Requires authentication.

---

## 8.6 Verify Email

```http
GET /api/v1/auth/email/verify/{id}
```

Verifies the user's email address through the API's email verification flow.

---

## 8.7 Forgot Password

```http
POST /api/v1/auth/password/forgot
```

Initiates the password reset flow for a user account.

---

## 8.8 Reset Password

```http
POST /api/v1/auth/password/reset
```

Completes the password reset flow using the provided reset data.

---

# 9. Categories

## 9.1 List Categories

```http
GET /api/v1/categories
```

Returns public categories.

---

# 10. Items

## 10.1 List Items

```http
GET /api/v1/items
```

Returns publicly discoverable items.

Supported capabilities include:

* Search
* Category filtering
* Sorting
* Pagination

Hidden items are not included in public discovery.

---

## 10.2 Show Item

```http
GET /api/v1/items/{item:slug}
```

Returns the public representation of an item.

The normal public item representation does not expose protected content.

---

## 10.3 Access Item

```http
GET /api/v1/items/{item:slug}/access
```

Checks or resolves whether the authenticated user can access the item according to the backend access rules.

Requires authentication.

Current access rules include:

* Free items are accessible to authenticated users.
* Paid items require direct access or an active global subscription.
* Hidden items are not exposed through protected access.
* Direct access remains valid independently of later subscription expiration.

Guests receive an authentication-required response.

---

## 10.4 Protected Item Content

```http
GET /api/v1/items/{item:slug}/content
```

Returns protected item content when the authenticated user is authorized to access it.

Requires authentication and backend access authorization.

The client must not determine access independently.

---

## 10.5 User Library

```http
GET /api/v1/library
```

Returns the authenticated user's accessible library according to the current backend access rules.

Requires authentication.

---

# 11. Public Subscription Plans

## 11.1 List Active Subscription Plans

```http
GET /api/v1/subscription-plans
```

Returns publicly available active subscription plans.

The endpoint is intended for public product/subscription presentation.

Inactive plans are not returned by the public endpoint.

Current response representation includes:

```text
id
name
duration_months
price
is_active
created_at
updated_at
```

Plans are ordered deterministically according to the current API behavior.

---

# 12. Orders

## 12.1 List User Orders

```http
GET /api/v1/orders
```

Returns orders belonging to the authenticated user.

Requires authentication.

The endpoint does not expose another user's orders.

---

## 12.2 Create Order

```http
POST /api/v1/orders
```

Creates an order according to the supported order workflows.

The server is authoritative for:

* Item availability
* Visibility
* Pricing
* Order type
* Eligibility

Client-provided prices must not be trusted.

---

## 12.3 Show Order

```http
GET /api/v1/orders/{order}
```

Returns an order belonging to the authenticated user.

Ownership is enforced by the API.

---

## 12.4 Delete Order

```http
DELETE /api/v1/orders/{order}
```

Deletes an order belonging to the authenticated user according to the backend order rules.

Ownership is enforced by the API.

---

# 13. Payments

## 13.1 Create Payment

```http
POST /api/v1/orders/{order}/payments
```

Creates a payment associated with the specified order.

Payment creation follows the backend commerce rules.

---

## 13.2 Initiate Payment

```http
POST /api/v1/payments/{payment}/initiate
```

Initiates payment through the resolved payment gateway.

The gateway is selected by the backend.

---

## 13.3 Payment Callback

```http
GET /api/v1/payments/{payment}/callback
```

The callback completes the external payment flow.

The API must verify the payment with the gateway before treating it as successful.

A callback request itself is not proof of payment success.

After processing, the API redirects the user to the configured Web application payment result page.

---

# 14. Payment Semantics

The payment lifecycle follows the general model:

```text
Order
  │
  ▼
Payment
  │
  ▼
Gateway Initiation
  │
  ▼
External Payment
  │
  ▼
Callback
  │
  ▼
Gateway Verification
  │
  ▼
Internal Transactional Fulfillment
```

The backend must remain authoritative for payment state.

Successful payment verification may trigger the corresponding fulfillment workflow.

Internal order/payment/subscription/access state transitions should be performed transactionally where required.

---

# 15. Payment Ownership

A user must not be able to operate on another user's payment through user-owned endpoints.

Payment ownership and related order ownership are enforced by the API.

Administrative workflows may inspect payment information through authorized order-management endpoints.

---

# 16. Subscription Access

An active global subscription grants access to paid items according to the current access rules.

The current model does not map individual subscription plans to individual items.

The subscription model is intentionally global.

---

# 17. Subscription List

## 17.1 List User Subscriptions

```http
GET /api/v1/subscriptions
```

Returns subscriptions belonging to the authenticated user.

Requires authentication.

The endpoint does not expose another user's subscriptions.

---

# 18. Subscription State

A subscription contains information including:

```text
user
plan
starts_at
ends_at
```

The active condition is:

```text
starts_at <= now < ends_at
```

A subscription may therefore be:

* Future
* Active
* Expired

The current implementation does not use recurring billing or automatic renewal.

---

# 19. Admin API

Administrative endpoints require:

```text
auth:sanctum
```

plus administrative authorization.

The Admin application uses these endpoints for management workflows.

---

## 19.1 Dashboard

```http
GET /api/v1/admin/dashboard
```

Returns administrative dashboard statistics.

Current statistics include:

* Total users
* Total items
* Total categories

---

# 20. Admin Users

## 20.1 List Users

```http
GET /api/v1/admin/users
```

Supports:

* Search
* Role filtering
* Sorting
* Pagination

---

## 20.2 Show User

```http
GET /api/v1/admin/users/{user}
```

Returns administrative user details.

---

## 20.3 Update User

```http
PATCH /api/v1/admin/users/{user}
```

Supports the currently permitted administrative user updates, including:

* Name
* Role

Authorization and validation are enforced by the API.

---

# 21. Admin Categories

## 21.1 List

```http
GET /api/v1/admin/categories
```

## 21.2 Create

```http
POST /api/v1/admin/categories
```

## 21.3 Show

```http
GET /api/v1/admin/categories/{category}
```

## 21.4 Update

```http
PATCH /api/v1/admin/categories/{category}
```

## 21.5 Delete

```http
DELETE /api/v1/admin/categories/{category}
```

Administrative category operations support the current Category Management workflow.

---

# 22. Admin Items

## 22.1 List

```http
GET /api/v1/admin/items
```

Supports:

* Search
* Filtering
* Sorting
* Pagination

---

## 22.2 Create

```http
POST /api/v1/admin/items
```

Creates an Item.

---

## 22.3 Show

```http
GET /api/v1/admin/items/{item}
```

Returns administrative Item details.

---

## 22.4 Update

```http
PATCH /api/v1/admin/items/{item}
```

Updates an Item.

---

## 22.5 Delete

```http
DELETE /api/v1/admin/items/{item}
```

Deletes an Item according to the backend integrity rules.

---

# 23. Admin Item Media

Media management is scoped to Items.

## 23.1 Upload Media

```http
POST /api/v1/admin/items/{item}/media
```

Uploads media for the specified Item.

---

## 23.2 Delete Media

```http
DELETE /api/v1/admin/items/{item}/media/{media}
```

Deletes the specified media from the Item.

---

## 23.3 Set Primary Media

```http
PATCH /api/v1/admin/items/{item}/media/{media}/primary
```

Sets the specified media as the primary media for the Item.

---

Media ordering is part of the Item media workflow and remains subject to the current implementation contract.

---

# 24. Admin Orders

## 24.1 List Orders

```http
GET /api/v1/admin/orders
```

Supports:

* Search/filtering
* Order-status filtering
* User filtering
* Payment-status filtering
* Pagination
* Deterministic ordering

---

## 24.2 Show Order

```http
GET /api/v1/admin/orders/{order}
```

Returns administrative order details including relevant:

* User information
* Item information
* Subscription information
* Subscription plan information
* Payment information

---

# 25. Admin Subscription Plans

## 25.1 List

```http
GET /api/v1/admin/subscription-plans
```

## 25.2 Create

```http
POST /api/v1/admin/subscription-plans
```

## 25.3 Show

```http
GET /api/v1/admin/subscription-plans/{subscriptionPlan}
```

## 25.4 Update

```http
PATCH /api/v1/admin/subscription-plans/{subscriptionPlan}
```

The administrative Subscription Plan workflow supports:

* Name
* Duration
* Pricing
* Active/inactive state

---

# 26. Resource Representations

The API currently exposes structured resources for entities including:

* User
* Item
* Category
* Tag
* Media
* Payment
* Subscription Plan
* Subscription
* Order

The exact representation of each resource is governed by the implementation and should remain synchronized with the API Resources.

---

# 27. Core Status Values

## User Roles

```text
user
admin
```

## Item Access Types

```text
free
paid
```

## Order Types

```text
item
subscription
```

Order/payment status values are implementation-defined enums and must be treated as API contract values rather than frontend-generated labels.

---

# 28. Business Rules

## Server-Authoritative Pricing

The client must never be trusted to determine the final purchase price.

The API determines the applicable price.

---

## Price Snapshots

When an order is created, the relevant purchase price is stored as a historical snapshot.

Later product price changes do not rewrite historical order prices.

---

## Access

Access is determined by the backend.

Frontend applications must not infer access from price, UI state, or subscription labels alone.

---

## Hidden Content

Hidden items must not become publicly accessible by manipulating client-side routes or requests.

Protected endpoints enforce visibility and authorization.

---

## Ownership

User-owned resources must only be accessible to their owner unless the endpoint is explicitly administrative.

---

## Payment Verification

A successful-looking callback is not sufficient to mark a payment as successful.

The backend must verify the transaction with the configured payment gateway.

---

## Transactional Fulfillment

After successful payment verification, internal fulfillment state changes should be performed transactionally.

Depending on the order type, fulfillment may include:

* Direct item access
* Subscription creation
* Related order/payment state changes

---

# 29. Admin / Web Consumers

The API currently has two primary frontend consumers:

### Admin

Uses administrative endpoints for:

* Dashboard
* Users
* Items
* Categories
* Orders
* Subscription Plans
* Item Media

### Web

Uses public endpoints for:

* Authentication
* Categories
* Items
* Search/filter/sort
* Item details
* Access/content workflows
* Subscription plan presentation
* User orders/subscriptions where implemented

---

# 30. Contract Stability

The API contract is a shared dependency.

Changes should therefore be deliberate.

Before changing an endpoint, consider:

1. Which frontend consumers use it?
2. Does the request structure change?
3. Does the response structure change?
4. Do status codes change?
5. Do business semantics change?
6. Does the change require a version bump?

Breaking changes should not be introduced casually into `/api/v1`.

---

# 31. Implementation vs. Contract

This document describes externally observable API behavior.

Internal Laravel details may change without requiring a contract change.

For example, the following can change internally without changing the contract:

* Controller implementation
* Service implementation
* Model internals
* Query optimization
* Database implementation details that do not affect the response contract

The API contract should change only when externally observable behavior changes.

---

# 32. Source of Truth

The API implementation and automated tests are the final technical authority for actual runtime behavior.

This document is the human-readable contract and must remain synchronized with the implementation.

When documentation and implementation disagree:

1. Verify the actual route/controller/resource behavior.
2. Determine the intended contract.
3. Update the appropriate source.
4. Update this document.

Documentation must not silently describe behavior that the API does not actually provide.

---

## Final Contract Principle

> **The API is the source of truth for backend state, business rules, authorization, access, pricing, payments, and subscriptions.**

Admin and Web consume the contract.

They do not redefine it.
