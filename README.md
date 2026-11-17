# Fulfillmen landing page revamp (WordPress plugin)

A conversion-focused, animated quote landing page for fulfillmen.com, built from the *Fulfillmen Landing Page CRO Brief*.

**Stack:** a WordPress plugin. PHP renders the pages on the server and vanilla JS handles the behaviour. Lenis 1.1 provides smooth scrolling and GSAP 3.13 + ScrollTrigger run the animations; both are bundled locally. It is not a static HTML page. It runs on any Hostinger WordPress plan with PHP 7.4+ and WordPress 6.2+, and it works with Elementor, block themes and LiteSpeed Cache. There is no build step.

## Design direction (v3: logistics premium)

The look is built from the trade's own artifacts, so it reads as a premium China-fulfilment brand rather than a generic SaaS or agency page.

| Section | Artifact | Motion |
|---|---|---|
| Hero | A real dotted world map (Natural Earth data), with routes from the Huizhou hub to the US, UK, EU, UAE, Singapore and Australia | Parcels travel each lane; the waybill tilts toward the cursor |
| Hero card | A shipping label / waybill: 7–15 days, ship from Huizhou, QC passed, barcode | The "Ship to" destination cycles |
| Below hero | An airport-style departure board of outbound lanes (labelled illustrative) | Split-flap rows flip |
| Pick your path | Kraft cartons with tape, handling marks and FBA / DS / OB stickers | A SELECTED rubber stamp on click |
| How it works | A conveyor line through Source → Inspect → Store → Ship | The carton rides the belt on scroll and gains a QC sticker, tape and a shipping label |
| Offer B | A pre-shipment photo inspection report with polaroids | An APPROVED stamp slams down |
| Proof | A manifest-style stats table, a carrier tracking timeline and a comparison table | Count-ups; milestones fill in |
| Quote | A printed receipt of the quote lines, and the form laid out as a numbered waybill (01–05) | — |

- **Type:** Archivo, which fulfillmen.com already uses, in its expanded industrial cut, plus JetBrains Mono for data labels.
- **Colour:** brand teal #00A89C, harbour dark, manifest paper, kraft and signal amber.

**Research behind it:**

- **Fulfillment design leaders (ShipMonk, Stord, Flexport, ShipBob):** they win with product-specific visuals, network maps and big stat rows.
- **The China-niche sites that rank (SendFromChina, ChinaDivision, NextSmartShip, Fulfillbot):** they use generic orange-and-blue stock-photo templates. None of them uses the trade's own visual language.

The map data lives in `templates/partials/world-map-data.php`. It was generated once from world-atlas / Natural Earth (public domain) with d3-geo.

## Install

1. Zip the `fulfillmen-landing/` folder, then upload it in **Plugins → Add New → Upload** and activate it.
2. Activation creates two **draft** pages:
   - *Free Landed-Cost Quote*, at `/china-fulfillment-quote/`, containing `[fulfillmen_landing]`
   - *Your Quote Is On Its Way*, at `/quote-thank-you/`, containing `[fulfillmen_thankyou]`

   Both use the **Fulfillmen Canvas** template, which hides the theme header and footer.
3. Fill in **Settings → Fulfillmen Landing**:
   - GoHighLevel webhook
   - Meta Pixel ID
   - Google Ads ID and conversion label
   - Calendar URL
   - WhatsApp number
   - Testimonials
   - Warehouse media
4. Publish both pages.
5. If you use LiteSpeed Cache, exclude the thank-you page from the cache.

## What maps to the brief

| Brief | Where |
|---|---|
| §1 Hero, CTAs, trust strip | `templates/landing.php` (CSS-only entrance, so it never waits for JS) |
| §2 Path selector → hidden `lead_type` (fba / dropshipping / own_brand) | Cards; can also be preset from ad URLs with `?path=fba` |
| §3 Offer A / Offer B | Quote section + photo-inspection section + FAQ |
| §4 Trust & proof | Trust strip, counters, 7–15 day tracker, gallery, comparison, testimonials |
| §5 Form: 5 visible fields + hidden lead_type and UTMs | First-touch UTMs, gclid and fbclid are kept for 30 days |
| §6 Thank-you page, calendar, Pixel Lead + Ads conversion **only here** | `templates/thank-you.php` (fires once per lead) |
| §8 301 `/signup1/` → `/sign-up/`, sticky mobile CTA, speed | Settings + `class-fm-frontend.php` |

## Lead delivery

Each submission is:

- stored in wp-admin under **Quote Leads**, so a lead is never lost if the webhook fails
- POSTed as JSON to the GHL inbound webhook. The payload includes `lead_type`, `tags[]` (`landing-quote`, `lead_type:fba`, …), `utm_*`, `gclid` and `fbclid`. In the GHL workflow, map the fields and add an **Add Tag** action using `{{inboundWebhookRequest.lead_type}}`.
- emailed to the notification address

Spam is blocked with a honeypot, a 3-second time trap and a limit of 5 submissions per 10 minutes per IP. Nonces are deliberately not used because page caches serve stale ones, which would silently break the form.

If JavaScript is off, the form still submits through `admin-post.php`.

## Content still needed from the client

These sections are shown only to logged-in admins (as dashed placeholders) until filled in:

- real warehouse photos and video
- 3–5 real testimonials (name, store type and result)
- the full street address in Huizhou

The headline numbers (2.5×, 20%, 20+ years) come from the current fulfillmen.com homepage.

The "landed cost $4.82" card in the hero is labelled **Example**. Replace it if the client has a real one.
