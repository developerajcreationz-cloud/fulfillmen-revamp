<?php
/**
 * [fulfillmen_landing] — v2 "premium product" design.
 * Flow follows the CRO brief §7: Hero → Pick your path → How it works →
 * Offer B → Proof → Quote form → FAQ → Final CTA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$o     = fm_lp_options();
$types = fm_lp_lead_types();
$vols  = fm_lp_volumes();
$ph    = fm_lp_show_placeholders();
$stats = fm_lp_rows( $o['stats'], 3 );
$tests = fm_lp_rows( $o['testimonials'], 4 );
$media = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $o['warehouse_media'] ) ) );
// phpcs:disable WordPress.Security.NonceVerification
$pre    = isset( $_GET['path'] ) ? sanitize_key( wp_unslash( $_GET['path'] ) ) : ( isset( $_GET['lead_type'] ) ? sanitize_key( wp_unslash( $_GET['lead_type'] ) ) : '' );
$pre    = isset( $types[ $pre ] ) ? $pre : '';
$fm_err = isset( $_GET['fm_error'] );
// phpcs:enable
$proof = $o['delivery_proof'];
$wa    = $o['whatsapp'];
$city  = preg_replace( '/,\s*China$/i', '', $o['address'] );
$i     = 'fm_lp_icon';

/** Wrap each word in a span for the scroll blur-reveal. */
$words = function ( $text ) {
	$out = array();
	foreach ( preg_split( '/\s+/', trim( $text ) ) as $w ) {
		$out[] = '<span class="fm-w">' . esc_html( $w ) . '</span>';
	}
	return implode( ' ', $out );
};

$paths = array(
	'fba'          => array( '01', 'Amazon FBA', 'amazon', 'Amazon FBA sellers', 'We prep, label and ship straight to Amazon warehouses.', 'Get my FBA quote' ),
	'dropshipping' => array( '02', 'Dropshipping', 'drop', 'Dropshippers', 'Source from China and ship each order directly to your customer.', 'Get my dropshipping quote' ),
	'own_brand'    => array( '03', 'Own stock', 'brand', 'Brands with their own stock', 'Store your inventory with us. We pick, pack and ship every order.', 'Get my fulfilment quote' ),
);

$nodes = array(
	array( 'link', 'You send a product link', 'From 1688, Alibaba, Amazon or your own store.', 'Trigger' ),
	array( 'search', 'Source', 'We vet factories, negotiate price and place the order.', '' ),
	array( 'camera', 'Inspect', 'Every batch checked against your spec and photographed.', '' ),
	array( 'box', 'Store', "Stock sits in our {$city} warehouse, live in your dashboard.", '' ),
);

$faqs = array(
	array( 'How is pricing worked out?', 'You only pay for what you use: product cost (if we source for you), quality check, prep, storage and shipping per unit dispatched. No hidden fees and no surprise line items at month end. Your free landed-cost quote itemises every line so you know your margin before you commit.' ),
	array( 'Is there a minimum order?', 'No minimum storage, no compulsory SKUs and no monthly order commitments. Ship 50 units to test a product or 50,000 ahead of peak season. If we source from a factory, that factory\'s own minimum may apply and your quote will show it upfront.' ),
	array( 'How long does shipping take?', 'B2C special lines deliver in 7–15 days door-to-door, fully tracked. Express couriers (DHL, FedEx, UPS) and air, sea and rail freight for Amazon FBA replenishment are also available. Your quote shows the options and price for each destination country.' ),
	array( 'How does your quality-check process work?', 'Goods arrive at our China warehouse, are counted and checked against your specification (product, colour, size, packaging and labels) before they are stored or shipped. If anything is off, we flag it to you with photos before it leaves the building.' ),
	array( 'What is the free photo inspection?', 'On your first order, before anything ships, we photograph your products so you know exactly what your customers will receive. It costs nothing and you approve the photos before dispatch.' ),
	array( 'Which stores and marketplaces do you work with?', 'Shopify, WooCommerce and Amazon connect directly so orders sync into our warehouse system automatically. eBay, TikTok Shop and other channels can be handled too: tell us what you sell on in the quote form.' ),
);
?>
<div class="fm-lp" data-fm-lp>

	<!-- ============ 1. HERO ============ -->
	<section class="fm-hero" aria-labelledby="fm-hero-title">
		<div class="fm-hero__glow" aria-hidden="true"></div>

		<!-- floating tiles (decor) -->
		<div class="fm-tiles" aria-hidden="true">
			<span class="fm-tile" style="--x:6%;--y:14%;--r:-8deg;--s:1" data-depth="0.6"><b class="fm-mark fm-mark--amz">a</b></span>
			<span class="fm-tile" style="--x:88%;--y:10%;--r:6deg;--s:.9" data-depth="0.9"><?php echo $i( 'bag' ); // phpcs:ignore ?></span>
			<span class="fm-tile" style="--x:3%;--y:52%;--r:5deg;--s:1.1" data-depth="1.2"><?php echo $i( 'camera' ); // phpcs:ignore ?></span>
			<span class="fm-tile" style="--x:92%;--y:44%;--r:-5deg;--s:1.15" data-depth="0.7"><?php echo $i( 'plane' ); // phpcs:ignore ?></span>
			<span class="fm-tile fm-tile--sm" style="--x:16%;--y:76%;--r:10deg;--s:.8" data-depth="1.4"><b class="fm-mark">eBay</b></span>
			<span class="fm-tile fm-tile--sm" style="--x:80%;--y:74%;--r:-10deg;--s:.8" data-depth="1.1"><?php echo $i( 'barcode' ); // phpcs:ignore ?></span>
		</div>

		<div class="fm-wrap fm-hero__in">
			<p class="fm-chip fm-rise" style="--d:0">
				<span class="fm-chip__marks"><b>a</b><b>S</b><b>e</b></span>
				Sourcing, QC, warehousing and shipping from China
			</p>
			<h1 id="fm-hero-title" class="fm-h1 fm-rise" style="--d:1">
				<span class="fm-ink">Source, store and ship from China</span>
				<span class="fm-tile-inline" aria-hidden="true"><?php echo $i( 'box' ); // phpcs:ignore ?></span>
				<span class="fm-mute">with one team, one dashboard and no surprise costs.</span>
			</h1>
			<p class="fm-hero__sub fm-rise" style="--d:2">Sourcing, quality checks, warehousing, Amazon FBA prep, dropshipping and global shipping, all handled by Fulfillmen's own China-based team.</p>
			<div class="fm-ctas fm-rise" style="--d:3">
				<a class="fm-btn fm-btn--dark fm-magnetic" href="#quote" data-fm-cta="hero">Get my free landed-cost quote <span class="fm-btn__dot"><?php echo $i( 'upright' ); // phpcs:ignore ?></span></a>
				<a class="fm-btn fm-btn--line" href="#how">See how it works</a>
			</div>
			<ul class="fm-checks fm-rise" style="--d:4" aria-label="Key facts">
				<li><?php echo $i( 'check' ); // phpcs:ignore ?> <?php echo esc_html( $proof ); ?></li>
				<li><?php echo $i( 'check' ); // phpcs:ignore ?> Itemised quote in 24 hours</li>
				<li><?php echo $i( 'check' ); // phpcs:ignore ?> No minimums, no lock-in</li>
			</ul>
		</div>

		<!-- Product mock: the "one dashboard" -->
		<div class="fm-wrap">
			<div class="fm-stage" data-fm-stage>
				<div class="fm-dash fm-rise" style="--d:5" aria-hidden="true">
					<div class="fm-dash__bar">
						<span class="fm-dash__dots"><i></i><i></i><i></i></span>
						<span class="fm-dash__url">app.fulfillmen.com/overview</span>
						<span class="fm-dash__live"><i></i> Live</span>
					</div>
					<div class="fm-dash__body">
						<aside class="fm-dash__side">
							<span class="fm-dash__logo">f</span>
							<span class="is-on"><?php echo $i( 'grid' ); // phpcs:ignore ?> Overview</span>
							<span><?php echo $i( 'search' ); // phpcs:ignore ?> Sourcing</span>
							<span><?php echo $i( 'camera' ); // phpcs:ignore ?> QC photos</span>
							<span><?php echo $i( 'box' ); // phpcs:ignore ?> Inventory</span>
							<span><?php echo $i( 'truck' ); // phpcs:ignore ?> Shipments</span>
						</aside>
						<div class="fm-dash__main">
							<div class="fm-dash__head">
								<div><small>Overview · <?php echo esc_html( $city ); ?> warehouse</small><b>Everything in one place</b></div>
								<span class="fm-tag">Demo data</span>
							</div>
							<div class="fm-kpis">
								<div class="fm-kpi"><small>Orders today</small><b data-fm-count="1284">1,284</b><em class="up">▲ 12%</em></div>
								<div class="fm-kpi"><small>In QC</small><b data-fm-count="86">86</b><em>12 photos each</em></div>
								<div class="fm-kpi"><small>Units in stock</small><b data-fm-count="42910">42,910</b><em>18 SKUs</em></div>
								<div class="fm-kpi"><small>Door-to-door</small><b>7–15<span> days</span></b><em>Fully tracked</em></div>
							</div>
							<div class="fm-dash__grid">
								<div class="fm-card fm-card--map">
									<div class="fm-card__h"><b>Shipments</b><small>B2C special lines</small></div>
									<svg viewBox="0 0 420 200" class="fm-routes">
										<g class="fm-routes__dots"></g>
										<g fill="none" stroke-width="1.6" stroke-linecap="round">
											<path id="fmR1" d="M290 120 C 240 60, 160 40, 90 60"/>
											<path id="fmR2" d="M290 120 C 250 50, 200 30, 150 36"/>
											<path id="fmR3" d="M290 120 C 330 70, 370 60, 400 70"/>
											<path id="fmR4" d="M290 120 C 330 150, 350 175, 360 185"/>
											<path id="fmR5" d="M290 120 C 240 130, 200 140, 180 120"/>
										</g>
										<g class="fm-routes__parcels">
											<rect data-route="fmR1" width="8" height="8" rx="2"/><rect data-route="fmR2" width="8" height="8" rx="2"/><rect data-route="fmR3" width="8" height="8" rx="2"/><rect data-route="fmR4" width="8" height="8" rx="2"/><rect data-route="fmR5" width="8" height="8" rx="2"/>
										</g>
										<g class="fm-routes__nodes">
											<g transform="translate(90 60)"><circle r="4"/><text y="-9">EU</text></g>
											<g transform="translate(150 36)"><circle r="4"/><text y="-9">UK</text></g>
											<g transform="translate(400 70)"><circle r="4"/><text y="-9">US</text></g>
											<g transform="translate(360 185)"><circle r="4"/><text x="16" y="4">AU</text></g>
											<g transform="translate(180 120)"><circle r="4"/><text y="16">UAE</text></g>
											<g transform="translate(290 120)" class="fm-routes__hq"><circle r="10" class="fm-ping"/><circle r="6"/><text y="24">Huizhou</text></g>
										</g>
									</svg>
								</div>
								<div class="fm-card fm-card--feed">
									<div class="fm-card__h"><b>Live orders</b><small>Auto-synced</small></div>
									<ul class="fm-feed" data-fm-feed>
										<li><span class="fm-cc">US</span><span>#FM-20481 · Shopify</span><em class="s-ship">Shipped</em></li>
										<li><span class="fm-cc">UK</span><span>#FM-20480 · Amazon FBA</span><em class="s-qc">QC passed</em></li>
										<li><span class="fm-cc">DE</span><span>#FM-20479 · WooCommerce</span><em class="s-pack">Packed</em></li>
										<li><span class="fm-cc">AU</span><span>#FM-20478 · eBay</span><em class="s-done">Delivered</em></li>
									</ul>
								</div>
								<div class="fm-card fm-card--cost">
									<div class="fm-card__h"><b>Landed cost / unit</b><small>Example</small></div>
									<ul class="fm-costs">
										<li><span>Product</span><i style="--w:64%"></i><b>$3.10</b></li>
										<li><span>QC + prep</span><i style="--w:8%"></i><b>$0.20</b></li>
										<li><span>Shipping</span><i style="--w:32%"></i><b>$1.52</b></li>
									</ul>
									<div class="fm-costs__t"><span>Total</span><b>$4.82</b></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Trust strip -->
		<div class="fm-wrap">
			<div class="fm-trust">
				<p>Built for sellers on</p>
				<div class="fm-logos" aria-label="Amazon, Shopify, eBay, WooCommerce, TikTok Shop">
					<span class="fm-logo-amz">amazon</span><span class="fm-logo-shp">shopify</span><span class="fm-logo-ebay">ebay</span><span class="fm-logo-woo">WooCommerce</span><span class="fm-logo-tt">TikTok Shop</span>
				</div>
			</div>
		</div>
	</section>

	<!-- Statement (blur reveal) -->
	<section class="fm-statement" aria-label="Why Fulfillmen">
		<div class="fm-wrap">
			<p class="fm-statement__card" data-fm-words>
				<?php echo $words( 'Most sellers juggle a sourcing agent, a QC inspector, a warehouse and a freight forwarder. Every handoff adds cost and delay. Fulfillmen is all four, under one roof in ' . $city . '.' ); // phpcs:ignore ?>
			</p>
		</div>
	</section>

	<!-- ============ 2. PICK YOUR PATH ============ -->
	<section class="fm-section fm-paths" id="path" aria-labelledby="fm-path-title">
		<div class="fm-wrap">
			<div class="fm-head">
				<p class="fm-mono fm-reveal">Pick your path</p>
				<h2 id="fm-path-title" class="fm-h2 fm-reveal"><span class="fm-ink">How do you sell?</span> <span class="fm-mute">We'll tailor your quote.</span></h2>
			</div>
			<div class="fm-paths__grid">
				<?php foreach ( $paths as $key => $p ) : ?>
					<a class="fm-path fm-reveal<?php echo $pre === $key ? ' is-selected' : ''; ?>" href="?path=<?php echo esc_attr( $key ); ?>#quote" data-fm-path="<?php echo esc_attr( $key ); ?>">
						<span class="fm-path__spot" aria-hidden="true"></span>
						<span class="fm-path__top"><span class="fm-mono"><b><?php echo esc_html( $p[0] ); ?></b> <?php echo esc_html( $p[1] ); ?></span><span class="fm-path__check" aria-hidden="true"><?php echo $i( 'check' ); // phpcs:ignore ?></span></span>

						<span class="fm-path__art" aria-hidden="true">
							<?php if ( 'fba' === $key ) : ?>
								<span class="fm-label">
									<span class="fm-label__bars"></span>
									<span class="fm-label__code">X00AB12CD3</span>
									<span class="fm-label__meta">FNSKU · New · Made in China</span>
								</span>
								<span class="fm-carton"><?php echo $i( 'box' ); // phpcs:ignore ?> Carton 1/24 · to FBA LGB8</span>
							<?php elseif ( 'dropshipping' === $key ) : ?>
								<span class="fm-flow">
									<span><?php echo $i( 'bag' ); // phpcs:ignore ?> Order #1043</span>
									<span class="fm-flow__ln"></span>
									<span><?php echo $i( 'box' ); // phpcs:ignore ?> Packed in China</span>
									<span class="fm-flow__ln"></span>
									<span class="is-ok"><?php echo $i( 'check' ); // phpcs:ignore ?> At customer's door</span>
								</span>
							<?php else : ?>
								<span class="fm-stock">
									<span><em>SKU-RED-M</em><i style="--w:82%"></i></span>
									<span><em>SKU-BLU-L</em><i style="--w:56%"></i></span>
									<span><em>SKU-GRN-S</em><i style="--w:34%"></i></span>
								</span>
							<?php endif; ?>
						</span>

						<h3><?php echo esc_html( $p[3] ); ?></h3>
						<p><?php echo esc_html( $p[4] ); ?></p>
						<span class="fm-path__cta"><?php echo esc_html( $p[5] ); ?> <?php echo $i( 'arrow' ); // phpcs:ignore ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ============ 3. HOW IT WORKS ============ -->
	<section class="fm-section fm-how" id="how" aria-labelledby="fm-how-title">
		<div class="fm-wrap">
			<div class="fm-how__panel">
				<div class="fm-how__copy">
					<p class="fm-mono fm-reveal"><span class="fm-dot"></span> How it works</p>
					<h2 id="fm-how-title" class="fm-h2 fm-reveal"><span class="fm-ink">Source, Inspect, Store, Ship.</span> <span class="fm-mute">One team, zero handoffs.</span></h2>
					<p class="fm-reveal">No agent here and 3PL there. The same people source, check, store and ship your products, so nothing gets lost between companies and every cost is on one invoice.</p>
					<a class="fm-btn fm-btn--light fm-reveal" href="#quote" data-fm-cta="how">Start with a free quote</a>
				</div>
				<div class="fm-nodes" data-fm-nodes aria-label="Process: Source, Inspect, Store, Ship">
					<?php foreach ( $nodes as $n ) : ?>
						<div class="fm-node">
							<?php if ( $n[3] ) : ?><span class="fm-node__trig">→ <?php echo esc_html( $n[3] ); ?></span><?php endif; ?>
							<span class="fm-node__done"><?php echo $i( 'check' ); // phpcs:ignore ?> Completed</span>
							<b><span class="fm-node__ic"><?php echo $i( $n[0] ); // phpcs:ignore ?></span><?php echo esc_html( $n[1] ); ?></b>
							<small><?php echo esc_html( $n[2] ); ?></small>
						</div>
						<span class="fm-node__ln" aria-hidden="true"><i></i></span>
					<?php endforeach; ?>
					<div class="fm-node__split" aria-hidden="true"><i></i></div>
					<div class="fm-node__pair">
						<div class="fm-node">
							<span class="fm-node__done"><?php echo $i( 'check' ); // phpcs:ignore ?> Completed</span>
							<b><span class="fm-node__ic"><?php echo $i( 'truck' ); // phpcs:ignore ?></span>Ship to customers</b>
							<small>Picked, packed, tracked to the door.</small>
						</div>
						<div class="fm-node">
							<span class="fm-node__done"><?php echo $i( 'check' ); // phpcs:ignore ?> Completed</span>
							<b><span class="fm-node__ic"><?php echo $i( 'amazon' ); // phpcs:ignore ?></span>Ship to Amazon</b>
							<small>Prepped and labelled for FBA.</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- ============ 4. OFFER B ============ -->
	<section class="fm-section fm-offer" aria-labelledby="fm-offer-title">
		<div class="fm-wrap">
			<div class="fm-offer__panel">
				<div class="fm-offer__glow" aria-hidden="true"></div>
				<div class="fm-offer__copy">
					<p class="fm-mono fm-mono--light fm-reveal">Free on your first order</p>
					<h2 id="fm-offer-title" class="fm-h2 fm-reveal">See it before your customers do.</h2>
					<p class="fm-reveal">Before anything ships, we photograph your products so you know exactly what your customers will receive.</p>
					<ul class="fm-ticks fm-reveal">
						<li><?php echo $i( 'check' ); // phpcs:ignore ?> Real photos from our warehouse floor, not supplier renders</li>
						<li><?php echo $i( 'check' ); // phpcs:ignore ?> You approve before dispatch</li>
						<li><?php echo $i( 'check' ); // phpcs:ignore ?> Problems fixed in China, not after refunds</li>
					</ul>
					<a class="fm-btn fm-btn--white fm-reveal" href="#quote" data-fm-cta="offer">Claim it with my free quote <span class="fm-btn__dot"><?php echo $i( 'upright' ); // phpcs:ignore ?></span></a>
				</div>
				<div class="fm-qc fm-reveal" aria-hidden="true">
					<div class="fm-qc__head"><span><?php echo $i( 'camera' ); // phpcs:ignore ?> QC report · PO-7731</span><em>Awaiting your approval</em></div>
					<div class="fm-qc__view">
						<span class="fm-qc__corner tl"></span><span class="fm-qc__corner tr"></span><span class="fm-qc__corner bl"></span><span class="fm-qc__corner br"></span>
						<svg viewBox="0 0 200 150" class="fm-qc__product"><rect x="45" y="48" width="110" height="82" rx="8"/><path d="M45 68 100 86l55-18M100 86v44M45 48l28-20h54l28 20"/></svg>
						<span class="fm-qc__scan"></span><span class="fm-qc__flash"></span>
					</div>
					<div class="fm-qc__thumbs"><i></i><i></i><i></i><i></i><i></i><i></i></div>
					<ul class="fm-qc__list">
						<li><?php echo $i( 'check' ); // phpcs:ignore ?> Colour matches sample</li>
						<li><?php echo $i( 'check' ); // phpcs:ignore ?> Size &amp; dimensions</li>
						<li><?php echo $i( 'check' ); // phpcs:ignore ?> Logo &amp; print</li>
						<li><?php echo $i( 'check' ); // phpcs:ignore ?> Packaging &amp; labels</li>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<!-- ============ 5. PROOF ============ -->
	<section class="fm-section fm-proof" id="proof" aria-labelledby="fm-proof-title">
		<div class="fm-wrap">
			<h2 id="fm-proof-title" class="fm-h2 fm-h2--xl fm-reveal"><span class="fm-ink">Faster from factory</span><br><span class="fm-mute">to front door</span> <span class="fm-tile-inline fm-tile-inline--sm" aria-hidden="true"><?php echo $i( 'bolt' ); // phpcs:ignore ?></span></h2>

			<?php if ( $stats ) : ?>
				<div class="fm-stats">
					<?php foreach ( $stats as $s ) : ?>
						<div class="fm-stat fm-reveal">
							<b><span data-fm-count="<?php echo esc_attr( $s[0] ); ?>" data-fm-dec="<?php echo false !== strpos( $s[0], '.' ) ? 1 : 0; ?>"><?php echo esc_html( $s[0] ); ?></span><?php echo esc_html( $s[1] ); ?></b>
							<span><?php echo esc_html( $s[2] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="fm-bento">
				<div class="fm-box fm-box--track fm-reveal">
					<p class="fm-mono">Delivery-time proof</p>
					<h3><?php echo esc_html( $proof ); ?></h3>
					<div class="fm-track">
						<div class="fm-track__rail"><i class="fm-track__fill"></i><span class="fm-track__pin"><?php echo $i( 'truck' ); // phpcs:ignore ?></span></div>
						<ol class="fm-track__stops">
							<li><b>Dispatched</b><small><?php echo esc_html( $city ); ?></small></li>
							<li><b>Line haul &amp; customs</b><small>Tracked at every milestone</small></li>
							<li><b>Delivered</b><small>Day 7–15</small></li>
						</ol>
					</div>
				</div>

				<div class="fm-box fm-box--compare fm-reveal">
					<p class="fm-mono">One team vs agent + 3PL</p>
					<div class="fm-vs" role="table" aria-label="Fulfillmen compared with a sourcing agent plus a separate 3PL">
						<div class="fm-vs__row fm-vs__row--h" role="row"><span role="columnheader"></span><span role="columnheader">Agent + 3PL</span><span role="columnheader">Fulfillmen</span></div>
						<?php
						foreach ( array(
							array( 'Companies', '2–3, separate invoices', 'One team, one invoice' ),
							array( 'Quality check', 'Often extra or skipped', 'Every batch, with photos' ),
							array( 'Stock view', 'Emails and spreadsheets', 'One live dashboard' ),
							array( 'Pricing', 'Costs at every handoff', 'Full landed cost upfront' ),
							array( 'Commitment', 'Minimums and contracts', 'No minimums, no lock-in' ),
						) as $r ) :
							?>
							<div class="fm-vs__row" role="row"><span role="rowheader"><?php echo esc_html( $r[0] ); ?></span><span role="cell" class="is-them"><?php echo esc_html( $r[1] ); ?></span><span role="cell" class="is-us"><?php echo $i( 'check' ); // phpcs:ignore ?> <?php echo esc_html( $r[2] ); ?></span></div>
						<?php endforeach; ?>
					</div>
				</div>

				<?php if ( $media || $ph ) : ?>
					<div class="fm-box fm-box--gallery fm-reveal">
						<p class="fm-mono">Inside our <?php echo esc_html( $city ); ?> warehouse</p>
						<div class="fm-gallery">
							<?php if ( $media ) : ?>
								<?php
								foreach ( array_slice( $media, 0, 4 ) as $m ) :
									$is_vid = (bool) preg_match( '/\.(mp4|webm)(\?|$)/i', $m );
									?>
									<figure>
										<?php if ( $is_vid ) : ?>
											<video data-fm-lazy-video muted loop playsinline preload="none" aria-label="Fulfillmen warehouse video"><source data-src="<?php echo esc_url( $m ); ?>" type="video/mp4"></video>
										<?php else : ?>
											<img src="<?php echo esc_url( $m ); ?>" alt="Inside the Fulfillmen warehouse" loading="lazy" decoding="async" width="600" height="450">
										<?php endif; ?>
									</figure>
								<?php endforeach; ?>
							<?php else : ?>
								<?php for ( $k = 0; $k < 4; $k++ ) : ?>
									<figure class="fm-ph"><span>Warehouse photo / video<small>Settings → Fulfillmen Landing · admins only</small></span></figure>
								<?php endfor; ?>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $tests || $ph ) : ?>
			<div class="fm-love">
				<div class="fm-wrap fm-head">
					<h2 class="fm-h2 fm-reveal"><span class="fm-ink">Sellers who switched</span></h2>
				</div>
				<div class="fm-marquee" data-fm-marquee>
					<div class="fm-marquee__track">
						<?php
						$cards = $tests ? $tests : array_fill( 0, 4, array( 'Client name', 'Store type', 'Real result, e.g. “Delivery cut from 21 to 9 days”', 'Add 3–5 real testimonials in Settings → Fulfillmen Landing. Only admins can see these placeholders.' ) );
						for ( $rep = 0; $rep < 2; $rep++ ) :
							foreach ( $cards as $t ) :
								?>
								<figure class="fm-review<?php echo $tests ? '' : ' fm-ph'; ?>"<?php echo $rep ? ' aria-hidden="true"' : ''; ?>>
									<div class="fm-review__stars"><?php echo str_repeat( $i( 'star' ), 5 ); // phpcs:ignore ?></div>
									<b><?php echo esc_html( $t[2] ); ?></b>
									<blockquote><?php echo esc_html( $t[3] ); ?></blockquote>
									<figcaption><?php echo esc_html( $t[0] ); ?> · <?php echo esc_html( $t[1] ); ?> <span class="fm-ok"><?php echo $i( 'check' ); // phpcs:ignore ?> Client</span></figcaption>
								</figure>
								<?php
							endforeach;
						endfor;
						?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</section>

	<!-- ============ 6. QUOTE FORM ============ -->
	<section class="fm-quote" id="quote" aria-labelledby="fm-quote-title">
		<div class="fm-quote__glow" aria-hidden="true"></div>
		<div class="fm-wrap fm-quote__in">
			<div class="fm-quote__copy">
				<p class="fm-mono fm-mono--light fm-reveal">Free landed-cost quote</p>
				<h2 id="fm-quote-title" class="fm-h2 fm-h2--xl fm-reveal">Know your <em>real cost</em> per unit in 24 hours.</h2>
				<p class="fm-reveal">Send your product or store link. We reply within 24 hours with full pricing, line by line.</p>
				<ul class="fm-receipt fm-reveal" aria-label="Your quote includes">
					<li><span>Product cost</span><i></i></li>
					<li><span>Quality check</span><i></i></li>
					<li><span>Prep &amp; labelling</span><i></i></li>
					<li><span>Warehousing</span><i></i></li>
					<li><span>Shipping to customer or Amazon</span><i></i></li>
					<li class="fm-receipt__t"><span>Landed cost per unit</span><b>No surprises</b></li>
				</ul>
				<ul class="fm-checks fm-checks--light fm-reveal">
					<li><?php echo $i( 'check' ); // phpcs:ignore ?> <?php echo esc_html( $proof ); ?></li>
					<li><?php echo $i( 'check' ); // phpcs:ignore ?> Free photo inspection on your first order</li>
					<li><?php echo $i( 'check' ); // phpcs:ignore ?> Amazon · Shopify · eBay sellers</li>
				</ul>
			</div>

			<div class="fm-formcard fm-reveal">
				<form class="fm-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate data-fm-form>
					<input type="hidden" name="action" value="fm_quote">
					<input type="hidden" name="lead_type" value="<?php echo esc_attr( $pre ); ?>" data-fm-lead-type>
					<?php foreach ( fm_lp_tracking_keys() as $k ) : ?>
						<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo isset( $_GET[ $k ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ $k ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification ?>">
					<?php endforeach; ?>
					<input type="hidden" name="landing_url" value="">
					<input type="hidden" name="referrer" value="">
					<input type="hidden" name="fm_ts" value="<?php echo esc_attr( time() ); ?>">
					<div class="fm-hp" aria-hidden="true"><label>Company website <input type="text" name="company_website" tabindex="-1" autocomplete="off"></label></div>

					<p class="fm-form__path" data-fm-path-line>
						<span data-fm-path-empty <?php echo $pre ? 'hidden' : ''; ?>>How do you sell? <a href="#path">Pick your path</a> (optional)</span>
						<span data-fm-path-set <?php echo $pre ? '' : 'hidden'; ?>><?php echo $i( 'check' ); // phpcs:ignore ?> Quote for <b data-fm-path-label><?php echo $pre ? esc_html( $types[ $pre ] ) : ''; ?></b> · <a href="#path">change</a></span>
					</p>

					<?php if ( $fm_err ) : ?>
						<p class="fm-form__alert" role="alert">Please check your details and try again.</p>
					<?php endif; ?>
					<p class="fm-form__alert" role="alert" data-fm-alert hidden></p>

					<div class="fm-row2">
						<div class="fm-field">
							<label for="fm-name">Name <span>*</span></label>
							<input id="fm-name" name="name" type="text" autocomplete="name" required minlength="2" placeholder="Full name">
							<small class="fm-err" data-err="name"></small>
						</div>
						<div class="fm-field">
							<label for="fm-email">Email <span>*</span></label>
							<input id="fm-email" name="email" type="email" autocomplete="email" inputmode="email" required placeholder="you@store.com">
							<small class="fm-err" data-err="email"></small>
						</div>
					</div>
					<div class="fm-field">
						<label for="fm-phone">WhatsApp / phone <span>*</span></label>
						<input id="fm-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required placeholder="+1 555 010 2030">
						<small class="fm-err" data-err="phone"></small>
					</div>
					<div class="fm-field">
						<label for="fm-links">Store link or product link(s) <span>*</span></label>
						<textarea id="fm-links" name="links" rows="3" required placeholder="yourstore.com, or 1688 / Alibaba / Amazon links. Add destination countries if you like."></textarea>
						<small class="fm-err" data-err="links"></small>
					</div>
					<fieldset class="fm-field fm-chips">
						<legend>Monthly order volume <span>*</span></legend>
						<?php foreach ( $vols as $k => $label ) : ?>
							<label class="fm-chipopt"><input type="radio" name="volume" value="<?php echo esc_attr( $k ); ?>" required><span><?php echo esc_html( preg_replace( '/ orders \/ month$/', '', $label ) ); ?></span></label>
						<?php endforeach; ?>
						<small class="fm-err" data-err="volume"></small>
					</fieldset>

					<button class="fm-btn fm-btn--teal fm-btn--block fm-magnetic" type="submit" data-fm-submit>
						<span class="fm-btn__label">Get my free quote</span>
						<span class="fm-btn__spin" aria-hidden="true"></span>
					</button>
					<p class="fm-form__under">Reply within 24 hours. No commitment.</p>
					<p class="fm-form__legal">We only use your details to prepare your quote. <a href="<?php echo esc_url( $o['privacy_url'] ); ?>">Privacy policy</a></p>
					<?php if ( $wa ) : ?>
						<p class="fm-form__wa"><a href="https://wa.me/<?php echo esc_attr( $wa ); ?>" target="_blank" rel="noopener" data-fm-cta="whatsapp"><?php echo $i( 'chat' ); // phpcs:ignore ?> Prefer WhatsApp? Message our team</a></p>
					<?php endif; ?>
				</form>
			</div>
		</div>
	</section>

	<!-- ============ 7. FAQ ============ -->
	<section class="fm-section fm-faq" id="faq" aria-labelledby="fm-faq-title">
		<div class="fm-wrap">
			<div class="fm-head">
				<h2 id="fm-faq-title" class="fm-h2 fm-h2--xl fm-reveal"><span class="fm-ink">Everything you're wondering</span></h2>
				<p class="fm-reveal">Or ask us anything <a href="#quote">through the quote form</a>.</p>
			</div>
			<div class="fm-acc">
				<?php foreach ( $faqs as $f ) : ?>
					<details class="fm-acc__item fm-reveal">
						<summary><?php echo esc_html( $f[0] ); ?><span class="fm-acc__pm" aria-hidden="true"></span></summary>
						<div class="fm-acc__body"><p><?php echo esc_html( $f[1] ); ?></p></div>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
		<script type="application/ld+json">
		<?php
		echo wp_json_encode(
			array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => array_map(
					function ( $f ) {
						return array(
							'@type'          => 'Question',
							'name'           => $f[0],
							'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f[1] ),
						);
					},
					$faqs
				),
			),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);
		?>
		</script>
	</section>

	<!-- ============ 8. FINAL CTA + FOOTER ============ -->
	<section class="fm-final" aria-labelledby="fm-final-title">
		<div class="fm-final__glow" aria-hidden="true"></div>
		<div class="fm-wrap fm-final__in">
			<h2 id="fm-final-title" class="fm-h2 fm-h2--xl fm-reveal">Find out what it <em>really costs</em> to sell from China.</h2>
			<p class="fm-reveal">Send a product link. Get an itemised landed-cost quote within 24 hours.</p>
			<div class="fm-ctas fm-ctas--c fm-reveal">
				<a class="fm-btn fm-btn--teal fm-magnetic" href="#quote" data-fm-cta="final">Get my free landed-cost quote <span class="fm-btn__dot"><?php echo $i( 'upright' ); // phpcs:ignore ?></span></a>
			</div>
			<ul class="fm-checks fm-checks--light fm-checks--c fm-reveal">
				<li><?php echo $i( 'check' ); // phpcs:ignore ?> Free photo inspection on first order</li>
				<li><?php echo $i( 'check' ); // phpcs:ignore ?> No minimums</li>
				<li><?php echo $i( 'check' ); // phpcs:ignore ?> No lock-in</li>
			</ul>
		</div>
		<footer class="fm-foot">
			<div class="fm-wrap fm-foot__in">
				<p><?php echo $i( 'pin' ); // phpcs:ignore ?> Fulfillmen · <?php echo esc_html( $o['address'] ); ?></p>
				<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Fulfillmen. All rights reserved. · <a href="<?php echo esc_url( $o['privacy_url'] ); ?>">Privacy</a> · <a href="#top" data-fm-top>Back to top ↑</a></p>
			</div>
		</footer>
	</section>

	<div class="fm-sticky" data-fm-sticky aria-hidden="true">
		<a class="fm-btn fm-btn--teal fm-btn--block" href="#quote" data-fm-cta="sticky" tabindex="-1">Get my free landed-cost quote</a>
	</div>
</div>
