# Prompt Box — Current Session

## Current Status

| Area                         | Status                                  |
| ---------------------------- | --------------------------------------- |
| Prompt Box                   | Production / Online                     |
| API                          | Production / Online                     |
| API Base                     | `https://api.promptbox.casfa.ir/api/v1` |
| Documentation                | Refresh in progress                     |
| Current Documentation Branch | `docs/refresh-prompt-box-documentation` |

## Product Context

Prompt Box is the first real product instance built from the reusable Armin foundation.

Armin provides the reusable architecture and generic backend foundation. Prompt Box owns its own product-specific implementation, content, branding, domains, deployment configuration, and production configuration.

## API State

The Prompt Box API is implemented as a Laravel API-first backend under:

```text
~/projects/prompt-box-api
```

The current `/api/v1` implementation exposes **48 routes**.

The API is deployed and online at:

```text
https://api.promptbox.casfa.ir/api/v1
```

The production API uses the standard Laravel `public` document root.

## Development / Seed State

The repository includes development/demo seed data for the Prompt Box domain.

Current seeded development data includes:

* 2 users
* 8 categories
* 12 tags
* 32 items
* 32 files
* 32 media records
* 3 active subscription plans
* 0 orders
* 0 subscriptions

Each seeded item has one associated File and one associated Media record.

Seed data is development/demo data and should not be treated as production business data.

## Current Repository State

The documentation refresh is being performed on a dedicated branch:

```text
docs/refresh-prompt-box-documentation
```

The branch was created from the clean `main` branch at:

```text
bdcf88f fix: make default item ordering deterministic
```

The documentation audit is limited to the files under:

```text
Armin Documentation/
```

No application code, routes, tests, database schema, configuration, or deployment implementation should be changed as part of this documentation batch.

## Documentation Scope

The current documentation refresh covers:

```text
Armin Documentation/README.md
Armin Documentation/ARMIN.md
Armin Documentation/ARCHITECTURE.md
Armin Documentation/FEATURES.md
Armin Documentation/API Contract.md
Armin Documentation/SESSION.md
```

Documentation must describe the actual current implementation and must not document planned or nonexistent routes and features as if they already exist.

## Working Rule

Use the established development workflow:

```text
Inspect
  ↓
Decide
  ↓
Change
  ↓
Validate
  ↓
Browser / API Test
  ↓
Commit
```

Do not assume a command succeeded without checking its output.

Do not make unrelated refactors, speculative abstractions, or implementation changes while performing documentation work.

## Architecture Rule

Armin remains the reusable foundation.

Changes that are genuinely shared and reusable across product instances should be implemented in the Armin Template first, validated and committed there, and then transferred to the relevant product instance and committed separately.

Prompt Box-specific requirements remain within the Prompt Box repositories.

## Next Step

Complete the documentation audit and validate the final documentation diff.

After the documentation commit, return to the actual Prompt Box development, production, and deployment backlog. Documentation work must not be used as a reason to modify unrelated application behavior.
