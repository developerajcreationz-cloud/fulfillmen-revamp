# Fulfillmen landing page revamp (WordPress plugin)

A conversion-focused, animated quote landing page for fulfillmen.com, built from the *Fulfillmen Landing Page CRO Brief*.

**Stack:** a WordPress plugin. PHP renders the pages on the server, vanilla JS handles the behaviour, Lenis 1.1 provides smooth scrolling (as on razepremier.com) and GSAP 3.13 + ScrollTrigger run the animations. All three libraries are bundled locally. It is not a static HTML page. It runs on any Hostinger WordPress plan with PHP 7.4+ and WordPress 6.2+, and it works with Elementor, block themes and LiteSpeed Cache. There is no build step.

## Design direction (v2)

v2 follows the razepremier.com design language in Fulfillmen's teal:

- Geist and Geist Mono type
- two-tone headlines with an icon tile set inline
- a floating pill navigation bar
- words that un-blur as you scroll
- a workflow diagram whose steps light up as "Completed"
- dark panels with a glow
- a two-column FAQ and a testimonial marquee

Research behind it:

- **Fulfillment design leaders (ShipMonk, Stord, Flexport, ShipBob):** they win with product UI built in HTML, bento capability grids, network maps and big stat rows.
- **China-niche competitors that rank for "china fulfillment / 3PL" (SendFromChina, ChinaDivision, NextSmartShip, Fulfillbot):** they use dated orange-and-blue stock-photo layouts.

The v2 hero therefore shows a live "one dashboard" mock with KPIs, a shipments map, a live order feed and a landed-cost breakdown. It proves the headline's promise, and no China competitor has anything like it.

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
