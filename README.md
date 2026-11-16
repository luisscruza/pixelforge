# InteriorIA

An AI interior designer built on the [Pixelforge](https://pixelforge.promptedit.dev/docs) image-generation API. Users describe a room, see exactly what it will cost before they submit, and pay Pixelforge's cost plus 25% only for renders that succeed. Operators get a revenue panel that reconciles every provider cent against Pixelforge's own usage report.

**Live:** _deployment URL_ · **Stack:** Laravel 13, Inertia v3, Vue 3, Postgres, Saloon, Pest

Built by [Luis De la Cruz](https://github.com/luisscruza).

---

## What it does

| Requirement                  | How it's met                                                                                                                                                        |
| ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Generating**               | One studio page: prompt, format (size), finish (quality) and up to four inspiration URLs. A live estimate (expected and maximum) updates as you type.               |
| **Pricing**                  | Provider cost from token usage at the generation's own rates, plus a 25% markup, exact to 12 decimals. Failed renders cost nothing.                                 |
| **History**                  | Live status (queued, processing, succeeded, failed), estimated and actual cost, and the image, with infinite scroll and live polling of in-flight renders.          |
| **Webhooks**                 | Signature verified on the raw body; duplicates, out-of-order events and webhooks that beat the API response all reach the right state, and are billed exactly once. |
| **Admin report** _(stretch)_ | Revenue, provider cost and profit per UTC day and per customer, reconciled day by day against `GET /v1/usage`.                                                      |
| **Balances** _(stretch)_     | A credit ledger with "Buy credits". Submitting holds the maximum price, and generating is blocked when you can't afford it.                                         |

## How money works

**No floats, ever.** Amounts are `BcMath\Number` objects (PHP 8.4) in the domain and `DECIMAL(20,12)` in Postgres. Pixelforge costs have at most 9 decimals, and the ×1.25 markup adds two, so 12 decimals hold every amount exactly. Nothing is rounded in storage; the UI shows exact values with trailing zeros trimmed (`$0.3870475`), and only the balance is shown to the cent (rounded down).

- **Estimate before submit.** `ceil(chars / 4)` text tokens (Unicode characters, ±15%), 765 tokens per reference image, the exact output table, and the reasoning range for the quality. Expected uses the reasoning midpoint; maximum uses +15% text and maximum reasoning. The browser computes the same estimate with `BigInt`, so it matches the server to the last digit (the docs' worst case, `0.373875`, is reproduced in both).
- **Actual cost after completion.** Usage × the `rates` returned on the generation (fixed at creation, never today's pricing) ÷ 1M, then × 1.25.
- **Pricing is read live** from `GET /v1/pricing` and cached for five minutes. It is cached as an array, because Laravel 13 refuses to unserialize objects from the cache.

### The credit ledger

Balances are the sum of an append-only `credit_transactions` ledger, never a number we overwrite:

| Event                      | Entry                | Amount                   |
| -------------------------- | -------------------- | ------------------------ |
| Buy credits                | `purchase`           | `+10.00`                 |
| Submit (max price $0.0875) | `hold`               | `−0.0875`                |
| Render succeeded           | `release` + `charge` | `+0.0875`, `−0.07064375` |
| Render failed              | `release`            | `+0.0875`                |

A unique index on `(generation_id, type)` means Postgres itself refuses a second hold, release or charge for the same generation. Submitting locks the user row, checks that the balance covers the maximum price and writes the hold in one transaction, so two tabs can't spend the same dollar. Settlement runs in the same transaction as the status change, so money and status never disagree.

## How generations flow

```
User submits ──► DB transaction: generation (pending) + credit hold   ← the outbox
                       │
                       ▼  after commit
               SubmitGenerationJob ──POST /v1/generations (Idempotency-Key = our uuid)──► Pixelforge
                                                                                            │
Pixelforge ──webhook──► verify HMAC ──► INSERT … ON CONFLICT DO NOTHING (event id) ──► job   ← the inbox
                                                                                            │
                         ApplyGenerationSnapshot  ◄── webhook / POST response / polling ◄──┘
                         (row lock, forward-only status, bill once, settle credits)
```

- **One place changes state.** Webhooks, the POST response and polling all hand a snapshot to `ApplyGenerationSnapshot`, which locks the row, only ever moves status forward (`pending → queued → processing → succeeded | failed`) and never leaves a final state.
- **Early webhooks.** Our uuid travels in `metadata`, so a webhook that arrives before the POST returns still finds its row.
- **Duplicates and disorder.** Duplicate events are dropped by the database (the event id is the primary key); a late `processing` after `succeeded` is ignored by the forward-only rule.
- **Retries.** Retryable errors (`rate_limit_exceeded`, `concurrency_limit_exceeded`, `overloaded`, any 5xx) wait `max(Retry-After, 2^attempt)` seconds, capped at 60, for up to ten minutes. Final errors (`content_policy_violation`, `daily_limit_exceeded`, `validation_failed`, …) fail the render at $0.

### A timeout is not a failure

If our POST times out, Pixelforge may or may not have created the generation. The provider's lifecycle (`status`) and our submission's state (`submission_status`) are tracked separately, and only Pixelforge's own answer moves `status`:

- A transport failure, a 5xx without a Pixelforge error code, or an unreadable 2xx marks the submission **`unknown`**. The hold stays.
- If retries run out after an `unknown` attempt, it becomes **`needs_reconciliation`** (not `failed`). The reconciler re-POSTs with the same idempotency key every 15 minutes: Pixelforge returns the original generation if it exists, or creates it if it never arrived. A late webhook can still resolve it and bill it exactly once.
- If every attempt got a definitive "nothing created" (a coded `overloaded` or rate limit), the render fails at $0 and the hold is released.
- Writes that record uncertainty are conditional on the row still being `pending`, so they can never overwrite a success that landed mid-timeout.

### Reconciliation

`pixelforge:reconcile` runs every minute (never overlapping). It re-queues submissions that stalled, re-POSTs unresolved ones, and polls `GET /v1/generations/{id}` for in-flight renders whose webhooks never arrived, at most 20 requests per run and stopping on the first rate limit.

## The admin panel

Admins (the `is_admin` flag, gated by `viewSpendReport`) get **Revenue**: KPIs with margin, a stacked daily revenue chart, a daily table with totals, and top customers. Days are **UTC completion days**, because that's when Pixelforge bills. Every day shows what the provider billed (from `/v1/usage`) next to our recorded cost, with a "Reconciled" or "Off by $x" status. Renders still waiting on an unknown outcome are counted as an alert, not silently accepted as a loss.

## Architecture

- **Actions** hold the business logic (`app/Actions`, one public `handle()` each). Controllers are resourceful and only validate with Form Requests and delegate.
- **DTOs** (`app/Data`) parse every Pixelforge payload; `Decimal::from()` is the single place untrusted values become money.
- **Saloon** (`app/Http/Integrations/Pixelforge`) has one request class per endpoint. `PixelforgeException` knows whether an error is retryable, its `Retry-After`, and whether the outcome is unknown.
- **Frontend:** pure helpers in `resources/js/lib`, reactive logic in composables, small components by feature. It uses Inertia v3 throughout: optimistic updates, typed `createForm` and `useFormContext`, deferred, once and optional props, infinite scroll, polling and instant visits.

## Running locally

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
```

Set Postgres credentials, then the Pixelforge values in `.env`:

```dotenv
PIXELFORGE_API_KEY=pf_live_…
PIXELFORGE_WEBHOOK_SECRET=whsec_…
```

```bash
php artisan migrate --seed
composer run dev
```

`--seed` creates an admin and a regular demo account with starting credits; see `database/seeders/DemoAccountsSeeder.php` for the logins. Webhooks need a public URL: `herd share` (or ngrok) and point the API key's webhook at `https://<tunnel>/webhooks/pixelforge`. Without webhooks, the reconciler's polling still completes every render.

Admins can add Pixelforge's test keywords (`[fail]`, `[slow]`, `[duplicate]`, `[overloaded]`, `[blocked]`, `[succeed]`) from the **Test scenarios** panel under the form.

## Quality

```bash
composer test     # Pint, Rector, PHPStan (level max) and Pest
npm run check     # lint and formatting
npm run types:check
```

Over 180 Pest tests cover the money paths end to end: exact pricing (including the docs' worst case), signed webhooks (forged, replayed, tampered, duplicated, out of order, early), retries and `Retry-After`, the timeout race, the ledger, reconciliation and the report.

## Trade-offs

- **Images aren't stored.** Pixelforge URLs expire after 24 hours, and the brief allows them to stop loading; the UI shows a "link expired" state.
- **`GET /v1/generations` isn't used.** Replaying the POST with the same idempotency key resolves any uncertain submission without scanning the list.
- **Rate limits are handled reactively** (`Retry-After` and backoff) rather than pre-throttled with `X-RateLimit-Remaining`, which is enough at this scale.
- **The daily quota fails a render** instead of retrying after UTC midnight, so users aren't left waiting hours with credits on hold.
