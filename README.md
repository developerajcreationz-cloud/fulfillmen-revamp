# Fulfillmen landing page revamp (WordPress plugin)

A conversion-focused, animated quote landing page for fulfillmen.com, built from the *Fulfillmen Landing Page CRO Brief*.

**Stack:** a WordPress plugin. PHP renders the pages on the server and vanilla JS handles the behaviour. Lenis 1.1 provides smooth scrolling and GSAP 3.13 + ScrollTrigger run the animations; both are bundled locally. It is not a static HTML page. It runs on any Hostinger WordPress plan with PHP 7.4+ and WordPress 6.2+, and it works with Elementor, block themes and LiteSpeed Cache. There is no build step.

## Design (v5)

Researched against Flexport, ShipBob, ShipMonk, ShipHero, Easyship, Freightos, project44, Shipwell and Cart.com.

- **Colours:** Fulfillmen's own palette only (teal `#00A89C`, deep teal `#02353C`, mint `#EBF7F8`) on a white page, with no gradient backgrounds.
- **Header and footer:** unchanged from v4 (Raze-style floating pill, dark glow footer).
- **Hero:** centred copy on a light dotted world map with routes from Huizhou and moving parcels (Flexport pattern). A sample client dashboard sits below the copy (KPIs, live shipments map, QC report with real photos, recent shipments, stock). It is labelled **Sample data**, starts tilted and flattens as you scroll.
- **Platform logos:** in full colour.
- **How do you sell?:** the three path cards start stacked in the centre and fan out into a tilted arc with a teal glow as you scroll, like the Raze Premier services section. They lay out as a normal grid on mobile.
- **How it works:** four steps with outlined numbers on a progress rail, plus a sticky product screen that changes per step (supplier comparison, QC report, stock and barcode, tracking). On mobile each screen sits inside its step.
- **Free photo inspection:** a bento grid with the inspection report, "real photos", "you approve before dispatch", a "found → fixed → shipped" example and a CTA tile.
- **Proof:** a bento grid with stat cards with small charts, the tracking timeline and the comparison table.
- **Quote:** one split card, with a deep-teal panel (copy, receipt, route map) and the form on white.
- **FAQ and final CTA:** unchanged.
- **Type:** Archivo, the font fulfillmen.com already uses, plus JetBrains Mono for small data labels.
- **Photos are editable** under Settings → Fulfillmen Landing → Photography. The QC visuals reuse these photos until the client sends real product QC photos.

The world map comes from `templates/partials/world-map-data.php`, generated once from world-atlas / Natural Earth (public domain) with d3-geo.

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
