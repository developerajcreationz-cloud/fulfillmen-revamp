# Fulfillmen landing page revamp (WordPress plugin)

A conversion-focused, animated quote landing page for fulfillmen.com, built from the *Fulfillmen Landing Page CRO Brief*.

**Stack:** a WordPress plugin. PHP renders the pages on the server and vanilla JS handles the behaviour. Lenis 1.1 provides smooth scrolling and GSAP 3.13 + ScrollTrigger run the animations; both are bundled locally. It is not a static HTML page. It runs on any Hostinger WordPress plan with PHP 7.4+ and WordPress 6.2+, and it works with Elementor, block themes and LiteSpeed Cache. There is no build step.

## Design (v4)

- **Colours:** Fulfillmen's own palette only — teal `#00A89C`, deep teal `#02353C` and mint `#EBF7F8` — on a **white** page. Sections alternate white and mint, with deep-teal panels for the photo inspection offer and the quote form.
- **Header (Raze-style):** a floating dark-glass pill with a white logo, dot-separated section links, a glowing teal "f" mark in the centre and a glowing CTA. On mobile it collapses to the logo and the CTA.
- **Footer (Raze-style):** dark, with the brand block, a CTA, link columns (this page, the real fulfillmen.com service pages, company pages, address) and a teal glow along the bottom edge. It lives in `templates/partials/footer.php`, so the thank-you page uses it too.
- **Hero:** benchmarked against ShipBob, ShipMonk and Stord (bright split layout, real photography, floating product cards) instead of the current dark full-bleed photo on fulfillmen.com.
  - Uses Fulfillmen's own team photo, with floating tracking, QC and landed-cost cards over it.
  - Below it sit Fulfillmen's own platform logos and a KPI strip (7–15 days, 24 h quote, 2.5×, 20%).
- **Path cards:** Fulfillmen's own photography — containers for FBA, sourcing for dropshipping and the warehouse for own stock.
- **Logistics details from v3, recoloured:** the scroll-driven conveyor through Source → Inspect → Store → Ship, the photo inspection report with its APPROVED stamp, the tracking timeline, the comparison table and the numbered quote form.
- **Type:** Archivo, the font fulfillmen.com already uses, plus JetBrains Mono for small data labels.
- **Photos are editable** under Settings → Fulfillmen Landing → Photography. They default to images already on fulfillmen.com.

The world map on the quote section comes from `templates/partials/world-map-data.php`, generated once from world-atlas / Natural Earth (public domain) with d3-geo.

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
