# Invoicing — design

**Date:** 2026-09-17
**Status:** approved design, implementation not started
**Scope:** companies, billing clients, service-item presets, GST tax invoices, credit notes,
proforma, payments and receipts, three PDF templates, email delivery with tracking and logs.

---

## 1. Why

Invoicing currently happens outside the app. The admin already holds the lead pipeline
(`Quote`), the client list and the brand assets, so the document that closes a won lead
belongs in the same place — with one number series nobody can accidentally reuse, one
audit trail, and one record of what was actually mailed to whom.

The business is registered in India, so an invoice here is a **Rule 46 tax invoice**, not a
generic bill. That is a compliance document: the field list, the numbering constraints, the
CGST/SGST-vs-IGST decision, the rounding and the immutability after issue are all set by
law, not by preference. The design treats them as fixed requirements and puts the
discretionary parts (layout, wording, which template) behind settings.

### Non-goals

Deliberately out of scope, listed so nobody adds them by reflex:

- **Payment gateway.** No Razorpay/Stripe, no webhooks, no payment links. Payments are
  recorded by hand; the PDF carries a UPI QR so the client can pay without one.
- **E-invoicing (IRN/QR) and e-way bills.** Mandatory only above ₹5 cr aggregate turnover
  (Notification 10/2023-CT, w.e.f. 2023-08-01), and e-way bills are a goods document. Neither
  applies. See §14 for the review trigger.
- **GST return filing / GSTR-1 export.** The data is modelled so a GSTR-1 export is possible
  later; building it now is speculation.
- **Recurring invoices, subscriptions, retainers on a schedule.**
- **Client portal.** The public invoice link is a capability URL, not an account.
- **Bill of supply.** The issuing entity is GST-registered and supplies taxable services
  only. If an exempt supply ever appears, that is a new document type, not a patch here.

---

## 2. Terminology

| Term | Means |
|---|---|
| **Company** | One of *our* legal entities. Issues invoices. One is the default. |
| **Billing client** | The party being billed. Distinct from `App\Models\Client`, which is the public-site logo wall. |
| **Service item** | A saved preset — name, SAC, unit, rate — picked when building a line. |
| **Issue** | The one-way door: assigns the number, freezes the document, renders the PDF. |
| **FY** | Indian financial year, 1 April – 31 March. `2026-04-01` → FY `26-27`. |
| **Place of supply** | The state whose tax applies. Decides CGST+SGST vs IGST. |

---

## 3. Money

**All monetary values are stored as integers in the minor unit (paise) in `bigint` columns
suffixed `_paise`**, and read through a `App\Invoicing\Money` cast.

The reason is the three-way split. A taxable value of ₹1,234.55 at 18% intra-state produces
two components of ₹111.1095 each; with floats and a `decimal(14,2)` round-trip through MySQL
(which returns strings) the components and the grand total can disagree by a paisa, and that
paisa is what breaks GSTR-1 reconciliation. Integers make every total the exact sum of its
parts. It also makes the SQLite test database behave identically to MySQL, which
`decimal` does not.

Tax rates are stored as integer **basis points** (`tax_rate_bps`: 18% → `1800`) for the same
reason — a 2.5% CGST half-rate is `250` bps exactly, where `2.5` as a float is not.

`App\Invoicing\Money` handles formatting (`₹1,23,456.00`, Indian digit grouping) and words
(`Rupees One Lakh Twenty Three Thousand Four Hundred Fifty Six Only`), because the grand
total in words is expected by every AP department even though Rule 46 does not require it.

---

## 4. Data model

Column lists below are the schema contract. Types are MySQL types; every table carries
`timestamps()`.

**Deviation, deliberate: the address and email columns listed NOT NULL below were all built
nullable.** This applies to `companies` (`address_line1`, `address_city`, `address_state`,
`address_state_code`, `address_postal_code`, `email`) and to the equivalent columns on
`billing_clients`. It is not an oversight and should not be "corrected" by a later migration.

Rule 46 requires those fields to be present on an *issued* invoice, not on a row in a table.
A draft has to be saveable while half-filled — an admin starts a company record with the name
and comes back for the bank details, and a NOT NULL column turns that into a database error
on a form that had no way to warn them. Presence is therefore enforced at issue time, by
phase 2's `InvoiceValidator` (§9), which is the point where the legal requirement actually
attaches. The one case pulled forward into the form is `companies.address_state_code`, which
`CompanyResource` requires once the entity is GST-registered, because it decides the tax
split rather than merely printing.

### 4.1 `companies`

Our own entities.

```
id
name                      string        trading name, on the PDF
legal_name                string|null   if it differs from the trading name
is_gst_registered         bool          default true
gstin                     string(15)|null   required when is_gst_registered
pan                       string(10)|null
cin                       string(21)|null
lut_number                string|null       export without IGST
lut_valid_till            date|null
address_line1             string
address_line2             string|null
address_city              string
address_state             string            "Delhi"
address_state_code        string(2)         "07" — drives the intra/inter decision
address_postal_code       string(6)
address_country           string(2)         default "IN"
email                     string
phone                     string|null
website                   string|null
bank_name                 string|null
bank_account_name         string|null
bank_account_number       string|null
bank_ifsc                 string|null
bank_branch               string|null
upi_id                    string|null
invoice_prefix            string(5)         "TLC"
credit_note_prefix        string(5)         "TLCC"
proforma_prefix           string(5)         "TLCP"
receipt_prefix            string(5)         "TLCR"
default_template          string            classic|modern|minimal
default_currency          string(3)         "INR"
default_payment_terms_days  unsigned int    default 7
default_terms             text|null         prefilled into new invoices
default_notes             text|null
footer_note               text|null
is_default                bool              exactly one true among active rows
is_active                 bool
```

Media collections (medialibrary, `MEDIA_DISK`): `logo`, `signature`, `stamp` — each
`singleFile()`.

**The default flag.** MySQL has no partial unique index, so uniqueness is enforced in code:
`Company::makeDefault()` runs in a transaction that clears `is_default` on every other row
before setting its own. The `CompanyPolicy` refuses to delete or deactivate the last active
company, and a `saving` observer promotes the first company created to default automatically
— an admin should never be able to reach a state with no default, because the invoice form
reads it to prefill.

### 4.2 `billing_clients`

The party we bill. A separate table from `clients` on purpose: the logo wall is public
content with `order` and `is_active` and is queried on every page render; adding GSTIN and a
billing address to it would widen those queries and leave every showcase row half-empty.

```
id
client_id                 FK clients nullOnDelete   optional link to the logo-wall row
name                      string
legal_name                string|null
gstin                     string(15)|null           null ⇒ unregistered recipient
pan                       string(10)|null
email                     string|null
cc_emails                 json|null                 additional recipients
phone                     string|null
billing_address_line1     string|null
billing_address_line2     string|null
billing_address_city      string|null
billing_address_state     string|null
billing_address_state_code string(2)|null
billing_address_postal_code string(6)|null
billing_address_country   string(2)     default "IN"
place_of_supply_state_code string(2)|null           default for new invoices
payment_terms_days        unsigned int|null         overrides the company default
currency                  string(3)     default "INR"
notes                     text|null
is_active                 bool
```

`is_registered` is not a column — it is `gstin !== null`. One source of truth.

### 4.3 `service_items`

The presets.

```
id
company_id     FK companies cascadeOnDelete|null   null ⇒ shared by all companies
name           string
description    text|null
sac_code       string(8)|null    default "998383"
unit           string            project|day|hour|shoot|item
rate_paise     bigint
tax_rate_bps   unsigned int      default 1800
is_expense     bool              default false — pass-through costs
sort           unsigned int
is_active      bool
```

`is_expense` marks travel, drone crew, album printing and the like. It changes nothing in
the tax maths; it groups those lines under a **Reimbursable expenses** subheading on the PDF
so the client can see what is fee and what is cost.

### 4.4 `invoices`

```
id
uuid                       uuid, indexed        public link identity
public_token               string(40)|null      bearer secret for the public link
company_id                 FK companies restrictOnDelete
billing_client_id          FK billing_clients restrictOnDelete
quote_id                   FK quotes nullOnDelete      raised off a won lead
type                       string    tax_invoice|proforma|credit_note
status                     string    draft|issued|sent|partially_paid|paid|overdue|cancelled
number                     string(16)|null      NULL until issue; unique with type+company
fy                         string(5)|null       "26-27"
issue_date                 date|null
due_date                   date|null
place_of_supply_state_code string(2)|null
place_of_supply_state      string|null
supply_type                string    intra|inter|export
is_reverse_charge          bool      default false
is_export_under_lut        bool      default false
currency                   string(3) default "INR"
exchange_rate              decimal(12,6) default 1     to INR, frozen at issue
template                   string    classic|modern|minimal
subtotal_paise             bigint    sum of line gross, before discount
discount_total_paise       bigint
taxable_total_paise        bigint
cgst_total_paise           bigint
sgst_total_paise           bigint
igst_total_paise           bigint
tax_total_paise            bigint
round_off_paise            bigint    signed, −49..+50
grand_total_paise          bigint    whole rupees ⇒ always a multiple of 100
grand_total_inr_paise      bigint    grand_total × exchange_rate, for books
amount_paid_paise          bigint    default 0
amount_due_paise           bigint
party_snapshot             json|null            frozen at issue, see below
original_invoice_id        FK invoices nullOnDelete   credit note → its invoice
converted_from_id          FK invoices nullOnDelete   proforma → the invoice it became
notes                      text|null
terms                      text|null
internal_notes             text|null            never rendered
created_by                 FK users nullOnDelete
issued_at                  timestamp|null
sent_at                    timestamp|null
cancelled_at               timestamp|null
cancel_reason              text|null
```

Indexes: `unique(company_id, type, number)`, `index(status, due_date)` for the overdue
sweep, `index(billing_client_id)`, `unique(uuid)`.

**`party_snapshot`** freezes the company block (name, address, GSTIN, PAN, bank, UPI) and the
client block (name, address, GSTIN, place of supply) as they were at issue. Without it,
reprinting a 2026 invoice in 2032 — which §36 requires us to be able to do — would silently
print today's address. The stored PDF is the primary artifact; the snapshot is what lets the
HTML view agree with it.

### 4.5 `invoice_lines`

```
id
invoice_id          FK invoices cascadeOnDelete
service_item_id     FK service_items nullOnDelete    provenance only
description         text
sac_code            string(8)|null
quantity            decimal(10,2)   default 1
unit                string|null
unit_price_paise    bigint
discount_percent    decimal(5,2)    default 0
discount_paise      bigint          default 0   authoritative over the percent
taxable_paise       bigint
tax_rate_bps        unsigned int
cgst_paise          bigint default 0
sgst_paise          bigint default 0
igst_paise          bigint default 0
line_total_paise    bigint          taxable + tax
is_expense          bool
sort                unsigned int
```

### 4.6 `invoice_payments`

```
id
invoice_id        FK invoices cascadeOnDelete
amount_paise      bigint
paid_on           date
mode              string   upi|neft|imps|cash|cheque|card|other
reference         string|null    UTR / cheque no.
receipt_number    string(16)|null   own series, allocated on record
notes             text|null
recorded_by       FK users nullOnDelete
receipt_sent_at   timestamp|null
```

An observer recalculates `amount_paid_paise` / `amount_due_paise` on the parent and moves
`status` to `partially_paid` or `paid`. A payment may not exceed the amount due; overpayment
is a validation error, not a negative due.

### 4.7 `invoice_sends` — what left the building

Mirrors `outreach_sends`, for the same reason it exists there: a status can be edited by
hand, but nothing un-sends an email.

```
id
invoice_id     FK invoices cascadeOnDelete
type           string   invoice|reminder|receipt|credit_note|proforma
to             string
cc             json|null
subject        string|null
message_id     string|null
status         string   queued|sent|failed
error          text|null
sent_at        timestamp|null
sent_by        FK users nullOnDelete
index(invoice_id, type, status)
```

### 4.8 `invoice_events` — what the client did

Append-only, recipient-side signals. Separate from `invoice_sends` because they answer a
different question and have a different trust level: a send is a fact we observed, an open is
an inference.

```
id
invoice_id   FK invoices cascadeOnDelete
type         string   opened|viewed|downloaded
ip           string|null
user_agent   string|null
occurred_at  timestamp
index(invoice_id, type)
```

Admin-side field changes are covered by `spatie/laravel-activitylog` on the `Invoice` model
(`logOnly(['status','number','grand_total_paise','template'])`, `logOnlyDirty`). Three logs,
three questions: what we sent, what they did, what we changed.

### 4.9 `invoice_number_sequences`

```
id
company_id    FK companies cascadeOnDelete
doc_type      string   tax_invoice|credit_note|proforma|receipt
fy            string(5)
last_number   unsigned int
unique(company_id, doc_type, fy)
```

---

## 5. Tax engine

`App\Invoicing\TaxCalculator` — a pure class with no database access, so the whole matrix is
unit-testable without fixtures.

**Input:** company state code, place-of-supply state code, client country, export flag, and
the lines (quantity, unit price, discount, tax rate).
**Output:** a `TaxSummary` with per-line splits and the totals.

### Supply type

```
client country ≠ IN                          → export
company.state_code == place_of_supply         → intra
otherwise                                     → inter
```

`export` with `is_export_under_lut` is zero-rated: no tax lines, and the statutory
endorsement prints on the PDF (§8). Without LUT, IGST applies at the line rate.

### Split

- **intra** → CGST and SGST, each at `tax_rate_bps / 2`.
- **inter** and taxed export → IGST at `tax_rate_bps`.

Never both. The two are mutually exclusive by construction — the calculator returns zero for
the unused columns rather than leaving them null.

### Rounding direction

**Rounding is half away from zero, not half up.** Earlier drafts of this section said
"round_half_up"; the implementation in `App\Invoicing\Money` rounds a half away from zero and
that is the correct behaviour, so this text has been corrected to match it rather than the
other way round. `Money::applyBps(-123455, 1800)` is `-22222`, where half-up would give
`-22221`.

The reason is credit notes. A credit note is the same arithmetic with the sign flipped, and
only symmetric rounding makes `credit(x) === -invoice(x)`. Under half-up a half-paisa on a
negative line rounds toward zero while its positive twin rounds away, so a credit note issued
to reverse an invoice fails to cancel it by a paisa — on the exact pair of documents whose
whole purpose is to net to nothing.

### Order of operations (exact)

1. `gross = round_half_away(quantity × unit_price_paise)`
2. `discount_paise` — if a percent was given, `round_half_away(gross × percent / 100)`; the
   stored amount wins if both are present.
3. `taxable_paise = gross − discount_paise`
4. Each tax component: `round_half_away(taxable_paise × component_bps / 10000)`, at 2dp — i.e.
   to the paise. **Components are never rounded to the rupee.**
5. Totals are the sums of the line values.
6. `grand_unrounded = taxable_total + tax_total`
7. `grand_total_paise = round_half_away_to_rupee(grand_unrounded)`; `round_off_paise =
   grand_total_paise − grand_unrounded`, which lies in −49..+50.

Step 7 implements §170 (round to the nearest rupee, ≥50 paise away from zero). Step 4 must
not: rounding the components is what makes a GSTR-1 return disagree with the ledger. The
`Round Off` line absorbs the difference and prints on the PDF.

**Step 1 must be integer arithmetic.** `quantity × unit_price_paise` is a product of two
integers and has to stay one; the obvious `(float) $quantity * $paise` is exactly what the
paise columns exist to prevent, and it reintroduces the drift described in §3 at the first
step of the calculation rather than the last. Phase 2 implements this inside
`TaxCalculator`. There is deliberately no `Money::multiply()` today — it would be unused API
until the calculator exists — so this is a note about what that code must do, not a pointer
to a helper that already does it.

### Place of supply

Derived by default — registered client → their GSTIN state; unregistered → their address
state; neither → the company's state (IGST §12(2)) — but **stored as an editable field on
the invoice**, not recomputed at render.

This is deliberate. Whether a wedding or event shoot for an *unregistered* client in another
state falls under IGST §12(7) (place of supply = where the event is held) or the §12(2)
default is genuinely arguable and has no CBIC clarification. Hardcoding a derivation would
bake an unreviewed legal opinion into the tax engine. The form shows the derived value, says
where it came from, and lets a human override it.

---

## 6. Numbering

`App\Invoicing\NumberAllocator`.

**Format:** `{prefix}/{fy}/{seq}` → `TLC/26-27/001`.

Rule 46(b) constraints, all enforced before a number is returned:

- ≤ 16 characters
- characters limited to `A–Z`, `a–z`, `0–9`, `-`, `/`
- consecutive within the series
- unique within the financial year

With the 5-character prefix cap the longest possible number is `TLCCC/26-27/0001` = 16
characters exactly, which is why the cap is 5 and not 6. The
sequence prints as 3 digits and widens to 4 past 999 — still inside the limit. A prefix
longer than 5 characters is a validation error on the company form, with the reason shown.

**FY** comes from `issue_date`: April–December → `YY-(YY+1)`, January–March → `(YY−1)-YY`.
Resetting the counter each FY is convention rather than statute (the rule says *unique for a
financial year*), but it is what GSTR-1 and every CA expects, and embedding the FY makes
uniqueness structural.

**Allocation** happens inside the issue transaction:

```php
DB::transaction(function () {
    $seq = InvoiceNumberSequence::where(...)->lockForUpdate()->firstOrCreate(...);
    $seq->increment('last_number');
    // format, validate, assign
});
```

`lockForUpdate` is the point: two admins pressing Issue at the same moment must not receive
the same number. A test drives this with concurrent transactions.

**Gaps and reuse.** A number is allocated at *issue*, never at draft creation, so abandoned
drafts burn nothing. A cancelled invoice keeps its number forever and is never deleted —
"consecutive" means the series must be explainable, not that every number is live. The
allocator has no path that reuses a number.

Each `doc_type` has its own counter; credit notes legally require a separate series.

---

## 7. Lifecycle

```
draft ──issue()──► issued ──send()──► sent ──payment──► partially_paid ──payment──► paid
  │                   │                 │                    │
  │                   └────────── cancel() ──────────────────┴────► cancelled
  └── delete() (only while draft)

overdue is not a stored transition — it is `due_date < today AND amount_due > 0`,
written onto status by the daily sweep so it can be filtered and badged.
```

### `App\Invoicing\IssueInvoice` — the one-way door

In one transaction:

1. Validate (§9). Any failure aborts before a number is consumed.
2. Allocate the number and set `fy`.
3. Freeze `party_snapshot` and `exchange_rate`.
4. Recompute totals from the lines one final time — the stored totals become the record, not
   a cached projection.
5. Set `issue_date` (today unless given), `due_date` (issue + terms days), `issued_at`,
   `status = issued`.
6. Render the PDF and store it to the media disk in collection `document`.

### Immutability

A `saving` guard on `Invoice` throws `InvoiceLockedException` when a non-draft row is dirty on
any of: `number`, `type`, `company_id`, `billing_client_id`, `issue_date`, `place_of_supply_*`,
`supply_type`, `currency`, `exchange_rate`, any `*_paise` column, `party_snapshot`. `status`,
`sent_at`, `amount_paid_paise`, `amount_due_paise` and `internal_notes` stay writable — an
invoice must still be able to record that it was paid. `InvoiceLine` refuses any write when
its parent is locked.

The Filament form renders read-only once locked, and `InvoicePolicy::delete()` returns false
for anything but a draft. GST provides no mechanism to edit an issued invoice; corrections
are new documents.

### Corrections

- **Credit note** — `type = credit_note`, `original_invoice_id` set, own number series, its
  own lines (full or partial reversal). The PDF prints "Credit Note" and, as Rule 53(1A)
  requires, the original invoice's number and date. It reduces `amount_due` on the original
  through the same observer that handles payments.
- **Cancel** — soft, requires a reason, keeps the number and the PDF. Legal only before the
  client has acted on the invoice; the confirmation dialog says so.

### Proforma

`type = proforma`, its own series, no GST liability, never counts toward revenue. A
**Convert to invoice** action clones it into a `draft` tax invoice with
`converted_from_id` set. The proforma stays as it was — it is a record of what was quoted.

---

## 8. Documents and templates

Three templates under `resources/views/invoices/templates/`: `classic.blade.php`,
`modern.blade.php`, `minimal.blade.php`. Each is a layout only; all of them include
`resources/views/invoices/partials/mandatory.blade.php`, which emits the Rule 46 fields. A
compliance change lands in one file and all three inherit it.

**Rule 46 field checklist** (the partial's contract):

- (a) supplier name, address, GSTIN
- (b) serial number
- (c) issue date
- (d) recipient name, address, GSTIN — GSTIN only when registered
- (e) unregistered recipient **and grand total ≥ ₹50,000** → name, address, address of
  delivery, state name **and code**
- (g) SAC per line — 4 digits while turnover ≤ ₹5 cr; the field takes whatever is stored
- (h) description
- (j) total value
- (k) taxable value after discount
- (l) tax rate per component
- (m) tax amount per component
- (n) place of supply with state name
- (o) delivery address where it differs
- (p) "Tax payable on reverse charge: Yes/No" — always printed
- (q) authorised signatory block with the signature image

Not required but printed: grand total in words, `Round Off` line, bank block, UPI QR,
payment status, terms. A wet signature is not required for an electronically issued invoice
(proviso to Rule 46, Notification 74/2018-CT); the signature image is there because clients
ask for it.

**Export invoices** additionally print the statutory endorsement, verbatim, chosen by the LUT
flag:

> SUPPLY MEANT FOR EXPORT/SUPPLY TO SEZ UNIT OR SEZ DEVELOPER FOR AUTHORISED OPERATIONS UNDER
> BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF INTEGRATED TAX

…with the LUT number and validity, plus the country of destination in place of clause (e).

### Rendering

`barryvdh/laravel-dompdf`. No Node, no Chromium — the deploy target is plain nginx + PHP-FPM
and adding a browser to it is a deploy landmine we do not need. Renders in ~20 ms.

Three constraints the templates must respect, each with a failure behind it:

- **CSS 2.1 only.** Tables for layout. No flexbox, no grid — dompdf ignores both silently and
  the invoice collapses into a single column.
- **`font-family: "DejaVu Sans"` declared explicitly.** dompdf's default font is a core PDF
  font with WinAnsi encoding only, and ₹ (U+20B9) renders as a blank box in it. dompdf has no
  fallback font and fails silently.
- **No external assets.** `enable_remote` stays off (it is dompdf's standard security
  footgun). The logo, signature and QR are embedded as base64 data URIs, fetched from the
  media disk at render time.

The templates get their own stylesheet, inlined into the Blade. `pages.css` is not involved —
dompdf cannot parse most of it.

The UPI QR is generated locally (`endroid/qr-code`) into a data URI from a standard
`upi://pay?pa=…&pn=…&am=…&tn=…` string. No external QR service; nothing about an invoice
should leave the building to render.

### Storage and retention

On issue the PDF is written to the media disk in collection `document` and that stored file
is what every later download serves — never a re-render, which could drift as templates
change. §36 requires records for 72 months from the annual-return due date (an FY 2026-27
invoice must survive to roughly 2033-12-31), so the media-prune command must exclude the
`document` collection and the invoice policy must never allow a hard delete of a non-draft.

---

## 9. Validation

Enforced in `App\Invoicing\InvoiceValidator`, called by `IssueInvoice` and surfaced in the
Filament form so failures appear before the button is pressed.

| Rule | Condition |
|---|---|
| Company GSTIN present | when `is_gst_registered` |
| GSTIN format | 15 chars, `\d{2}[A-Z]{5}\d{4}[A-Z][A-Z\d]Z[A-Z\d]`, and its first two digits equal the state code |
| Client state required | when unregistered **and** `grand_total ≥ ₹50,000` — Rule 46(e) |
| Place of supply set | always, for a tax invoice |
| At least one line | with a non-zero quantity |
| SAC per line | when the company is GST-registered |
| Due date | not before `issue_date` |
| Issue date | not in the future; not more than 30 days after the work date if one is recorded (Rule 47) — a warning, not a block |
| LUT | `lut_number` present and `lut_valid_till ≥ issue_date` when `is_export_under_lut` |
| Credit note | `original_invoice_id` set, references an issued invoice of the same company and client, and its total does not exceed the original's |
| Currency | `exchange_rate` > 0 when currency ≠ INR |

---

## 10. Public link and tracking

Routes (in `routes/web.php`, **outside** the `cacheResponse` group):

```
GET  /i/{uuid}             invoice.public.show      HTML view
GET  /i/{uuid}/download    invoice.public.download  streams the stored PDF
GET  /i/{uuid}/open        invoice.public.pixel     1×1 GIF, records an open
```

Access is a bearer token: `?t={public_token}`, 40 random characters, compared with
`hash_equals`, mismatch → 404 (not 403 — a 403 confirms the invoice exists). Tokens do not
expire; an expiring link is one that dies in the client's inbox exactly when they go looking
for it. A **Revoke link** action rotates the token.

Two repo-specific landmines these routes must avoid:

1. **Response cache.** `routes/web.php` wraps every public GET in `cacheResponse`. An invoice
   route inside that group would let responsecache serve one client's invoice to the next
   visitor. These three routes sit outside it and send `Cache-Control: private, no-store`
   plus `X-Robots-Tag: noindex, nofollow`.
2. **No PHP-served file extensions.** Production nginx matches static extensions in a
   location whose miss path is `error_page 404 → /index.php`, and the internal redirect
   *keeps* the 404 — which is exactly how `/livewire/livewire.min.js` killed admin login. So
   the pixel route is `/open`, not `/open.gif`, and the download route is `/download`, not
   `/invoice.pdf`. The PDF filename is set with `Content-Disposition` instead. A test pins
   both route shapes so nobody "tidies" them back to an extension.

**Open tracking honesty.** The pixel records `opened` events, but Gmail proxies and caches
images and Apple Mail pre-fetches them, so an open is weak evidence and a *lack* of opens is
almost none. The admin UI labels it "Opened (approximate)" and shows the `viewed` event —
someone actually loading the link — as the stronger signal. A dashboard that quietly implies
the client read it would be worse than no tracking.

---

## 11. Email

`App\Mail\InvoiceDocumentMail` — one mailable, four subjects, driven by `type`. Markdown mail
views under `resources/views/emails/invoices/`. The PDF is attached from the stored media
file; the body carries the number, amount, due date and the public link.

`App\Jobs\SendInvoiceDocument` (queued; `QUEUE_CONNECTION=database`):

1. Re-check idempotency immediately before sending, the way the outreach jobs do: for
   `invoice`, `receipt` and `credit_note`, refuse if a `sent` row of that type already
   exists; for `reminder`, refuse if one was already sent today.
2. Write the `invoice_sends` row with the pre-generated `message_id`.
3. Send, then stamp `sent_at`, or record `status = failed` with the exception message.

A failure never throws away the row — a failed send that leaves no trace is the thing this
table exists to prevent.

`invoices:sweep-overdue` (scheduled daily): marks overdue invoices and queues reminders for
those past due by the configured intervals, skipping any invoice whose client has no email
and any that was reminded today.

---

## 12. Admin

New navigation group **Billing**, ordered `Leads → Billing → Content → Site → Access`.

| Resource | Notes |
|---|---|
| `CompanyResource` | Sections: identity, tax registration, address, bank + UPI, branding (logo/signature/stamp), defaults. A **Make default** action. |
| `BillingClientResource` | Identity + GSTIN, addresses, terms, optional link to a logo-wall `Client`. Invoices relation manager with the outstanding total. |
| `ServiceItemResource` | Flat table, reorderable, `is_expense` toggle. |
| `InvoiceResource` | The main surface. |

`InvoiceResource` form: company (defaults to the default company), client, type, dates,
place of supply with the derivation shown and overridable, a lines repeater where picking a
service item prefills description/SAC/unit/rate, a live totals panel recomputed by the same
`TaxCalculator` the backend uses, then terms/notes/template.

Header actions, each gated by policy: **Issue**, **Send** (with a recipient preview),
**Record payment**, **Send receipt**, **Credit note**, **Cancel**, **Download PDF**,
**Preview** (HTML in a new tab), **Duplicate**, **Revoke link**.

Relation managers: Payments, Sends & events (a merged read-only timeline).

The logo-wall `ClientResource` gets its navigation label changed to **Client logos** under
Content. No code change beyond the label — the collision is only in the human reading of it.

Widgets (phase 4): outstanding total, overdue count and value, this-FY revenue.

**Permissions.** Each new resource needs a policy in `app/Policies/` and a
filament-shield / `PermissionsSeeder` run, or nobody sees it. Beyond the CRUD verbs, these
get their own abilities: `issue`, `cancel`, `recordPayment`, `sendMail`, `creditNote`. The
list view is not scoped by user — invoices are company-wide, and a role that cannot see them
should not have the resource at all.

---

## 13. Testing

Pest, `tests/Feature/Invoicing/` and `tests/Unit/Invoicing/`, `RefreshDatabase` per file.

**Unit — `TaxCalculator`:**
- intra-state 18% splits into equal CGST and SGST that sum to the tax total
- inter-state produces IGST only, with CGST and SGST at zero
- export under LUT produces no tax and sets the endorsement flag
- a line whose tax lands on a half-paise rounds half-up, and the components still sum
- the grand total is always a whole rupee and `round_off` stays in −49..+50
- a 100% discount produces zero taxable value, not a negative
- mixed tax rates across lines total correctly

**Unit — `NumberAllocator`:**
- format and 16-character limit, for the longest prefix allowed
- charset rejection
- FY derivation on 31 March and 1 April
- counter resets at the FY boundary and continues within one
- concurrent allocation in two transactions yields two different numbers
- cancelling does not free the number

**Feature:**
- issuing assigns a number, freezes the snapshot, writes the PDF, flips the status
- editing a locked invoice throws; editing a draft does not
- `status`, `amount_paid_paise` and `sent_at` remain writable after lock
- an unregistered client at ₹50,000 fails validation without a state; at ₹49,999 it passes
- deleting an issued invoice is refused by policy; a draft deletes
- the public link 404s on a wrong or missing token and renders on the right one
- the public invoice route is **not** inside the response-cache group, and a second request
  by a different visitor does not receive the first one's HTML
- no invoicing route ends in a file extension (guards the nginx `error_page` landmine)
- sending twice creates one `invoice_sends` row; a failed send records `failed` with the error
- a payment updates paid/due and the status; an overpayment is rejected
- a credit note reduces the original's due and carries the original's number and date
- `assertQueryCount` on the invoice list — the table renders client, company and payment
  totals, which is where the N+1 will be

---

## 14. Compliance review triggers

Recorded here so they are revisited deliberately rather than discovered:

- **Place of supply for events (IGST §12(7) vs §12(2)).** Unresolved; the field is a human
  override for this reason. Needs a CA's view before any automation is added.
- **E-invoicing threshold.** Currently > ₹5 cr aggregate turnover in any FY since 2017-18
  (Notification 10/2023-CT). Secondary sources discuss a reduction to ₹2 cr but no
  notification was found enacting one. Re-check when turnover approaches ₹2 cr; crossing the
  threshold is permanent and makes IRN/QR mandatory for B2B and export.
- **SAC and rate.** 998383 (event photography and videography) at 18%. The 56th Council
  rationalisation of 2025-09-22 left standard-rate services unchanged.
- **HSN/SAC digits.** 4 digits on B2B while turnover ≤ ₹5 cr, 6 above. The field stores what
  it is given; the change is a data change, not a code change.
- **TDS labels.** The Income-tax Act 2025 renumbers 194J/194C into Section 393 from
  2026-04-01. Nothing in this design prints a TDS section number, which is why.

---

## 15. Phases

Each phase is independently useful and independently reviewable.

**Phase 1 — parties.** `companies`, `billing_clients`, `service_items`, their resources,
policies, shield permissions, the default-company invariant, media collections, factories and
seeds. Delivers a usable address book and rate card.

**Phase 2 — the invoice.** Invoice and line schema, `Money`, `TaxCalculator`,
`NumberAllocator`, `InvoiceValidator`, `IssueInvoice`, the lock guard, the Filament form with
live totals. Delivers issuable, numbered, locked invoices with no PDF yet.

**Phase 3 — render and deliver.** dompdf, the three templates and the shared mandatory
partial, UPI QR, PDF storage, public link routes, mail, `invoice_sends`, `invoice_events`,
the pixel. Delivers an invoice a client actually receives.

**Phase 4 — money and the rest.** Payments, receipts with their own series, credit notes,
proforma and conversion, the overdue sweep and reminders, raising an invoice from a won
`Quote`, export/LUT handling, dashboard widgets.

New dependencies: `barryvdh/laravel-dompdf` (phase 3), `endroid/qr-code` (phase 3). Nothing
else.
