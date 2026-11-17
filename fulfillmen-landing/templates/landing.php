<?php
/**
 * [fulfillmen_landing] — v3 "logistics premium".
 * The visual language comes from the trade itself: route maps, waybills,
 * departure boards, cartons, inspection reports and tracking timelines.
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
$city  = trim( strtok( $o['address'], ',' ) );
$i     = 'fm_lp_icon';
$map   = include FM_LP_DIR . 'templates/partials/world-map-data.php';

/* Routes from the Huizhou hub: quadratic arcs bowed upwards. */
$hub    = $map['cities']['hz'];
$dests  = array( 'la' => 1, 'ny' => 1, 'to' => 0, 'lo' => 1, 'fr' => 0, 'du' => 1, 'sg' => 1, 'sy' => 1 ); // 1 = show label.
$routes = array();
foreach ( $dests as $k => $label ) {
	$c    = $map['cities'][ $k ];
	$dx   = $c['x'] - $hub['x'];
	$dy   = $c['y'] - $hub['y'];
	$dist = sqrt( $dx * $dx + $dy * $dy );
	$cx   = ( $hub['x'] + $c['x'] ) / 2;
	$cy   = min( $hub['y'], $c['y'] ) - $dist * ( $c['y'] > $hub['y'] + 80 ? 0.1 : 0.32 );
	$routes[ $k ] = array(
		'd'     => sprintf( 'M%.1f %.1f Q%.1f %.1f %.1f %.1f', $hub['x'], $hub['y'], $cx, $cy, $c['x'], $c['y'] ),
		'c'     => $c,
		'label' => $label,
	);
}

$world = function ( $id_prefix, $cls ) use ( $map, $routes, $hub ) {
	ob_start();
	?>
	<svg class="fm-world <?php echo esc_attr( $cls ); ?>" viewBox="0 0 <?php echo (int) $map['w']; ?> <?php echo (int) $map['h']; ?>" preserveAspectRatio="xMidYMid meet" aria-hidden="true" focusable="false">
		<path class="fm-world__land" d="<?php echo esc_attr( $map['dots'] ); ?>"/>
		<g class="fm-world__routes">
			<?php foreach ( $routes as $k => $r ) : ?>
				<path id="<?php echo esc_attr( $id_prefix . $k ); ?>" d="<?php echo esc_attr( $r['d'] ); ?>"/>
			<?php endforeach; ?>
		</g>
		<g class="fm-world__parcels">
			<?php foreach ( $routes as $k => $r ) : ?>
				<circle r="3.2" data-route="<?php echo esc_attr( $id_prefix . $k ); ?>"/>
			<?php endforeach; ?>
		</g>
		<g class="fm-world__cities">
			<?php foreach ( $routes as $k => $r ) : ?>
				<g transform="translate(<?php echo esc_attr( $r['c']['x'] . ' ' . $r['c']['y'] ); ?>)">
					<circle r="3.5"/>
					<?php if ( $r['label'] ) : ?><text x="8" y="4"><?php echo esc_html( strtoupper( $r['c']['name'] ) ); ?></text><?php endif; ?>
				</g>
			<?php endforeach; ?>
			<g class="fm-world__hub" transform="translate(<?php echo esc_attr( $hub['x'] . ' ' . $hub['y'] ); ?>)">
				<circle class="fm-world__ping" r="7"/><circle class="fm-world__ping fm-world__ping--2" r="7"/>
				<circle r="6"/>
				<text x="12" y="4">HUIZHOU HUB</text>
			</g>
		</g>
	</svg>
	<?php
	return ob_get_clean();
};

$board = array(
	array( 'LOS ANGELES', 'US', 'B2C LINE', '7–15 D', 'IN TRANSIT', 's-transit' ),
	array( 'LONDON', 'UK', 'B2C LINE', '7–15 D', 'QC PASSED', 's-qc' ),
	array( 'AMAZON FBA', 'US', 'FBA FREIGHT', 'QUOTED', 'PREPPED', 's-pack' ),
	array( 'SYDNEY', 'AU', 'B2C LINE', '7–15 D', 'DELIVERED', 's-done' ),
	array( 'FRANKFURT', 'DE', 'B2C LINE', '7–15 D', 'PACKED', 's-pack' ),
);

$paths = array(
	'fba'          => array( 'FBA', 'Amazon FBA sellers', 'We prep, label and ship straight to Amazon warehouses.', array( 'FNSKU labels & poly bags', 'Carton & pallet prep to Amazon spec', 'Sea, air or rail to FBA centres' ), 'Get my FBA quote' ),
	'dropshipping' => array( 'DS', 'Dropshippers', 'Source from China and ship each order directly to your customer.', array( 'Factory sourcing & price negotiation', 'Orders sync from your store', 'Tracked door-to-door in 7–15 days' ), 'Get my dropshipping quote' ),
	'own_brand'    => array( 'OB', 'Brands with their own stock', 'Store your inventory with us. We pick, pack and ship every order.', array( 'Warehousing in ' . $city, 'Branded packaging & inserts', 'Pick, pack and ship per order' ), 'Get my fulfilment quote' ),
);

$steps = array(
	array( 'search', 'Source', 'We vet factories, negotiate your price and place the order.' ),
	array( 'camera', 'Inspect', 'Every batch is checked against your spec and photographed.' ),
	array( 'box', 'Store', "Stock sits in our {$city} warehouse, live in your dashboard." ),
	array( 'plane', 'Ship', 'Picked, packed and shipped to customers or Amazon, fully tracked.' ),
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

	<!-- ================= 1. HERO ================= -->
	<section class="fm-hero" aria-labelledby="fm-hero-title">
		<?php echo $world( 'fmh-', 'fm-world--hero' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="fm-hero__shade" aria-hidden="true"></div>

		<div class="fm-wrap">
			<p class="fm-coords fm-rise" style="--d:0"><span class="fm-live"></span> Fulfillmen hub · <?php echo esc_html( $o['address'] ); ?> · 23.1°N 114.4°E</p>
		</div>

		<div class="fm-wrap fm-hero__grid">
			<div class="fm-hero__copy">
				<p class="fm-tagline fm-rise" style="--d:1">China sourcing · QC · 3PL warehousing · FBA prep · Dropshipping</p>
				<h1 id="fm-hero-title" class="fm-h1 fm-rise" style="--d:2">
					Source, store and ship from&nbsp;China, <span class="fm-h1__2">with one team, one dashboard and no surprise costs.</span>
				</h1>
				<p class="fm-hero__sub fm-rise" style="--d:3">Sourcing, quality checks, warehousing, Amazon FBA prep, dropshipping and global shipping, all handled by Fulfillmen's own China-based team.</p>
				<div class="fm-ctas fm-rise" style="--d:4">
					<a class="fm-btn fm-btn--primary fm-magnetic" href="#quote" data-fm-cta="hero">Get my free landed-cost quote <?php echo $i( 'arrow' ); // phpcs:ignore ?></a>
					<a class="fm-btn fm-btn--ghost" href="#how">See how it works</a>
				</div>
				<p class="fm-micro fm-rise" style="--d:5"><?php echo $i( 'clock' ); // phpcs:ignore ?> Itemised quote within 24 hours. No commitment.</p>
			</div>

			<!-- Waybill: the brief's promise, printed as a shipping label -->
			<div class="fm-hero__label fm-rise" style="--d:3" aria-hidden="true">
				<div class="fm-waybill" data-fm-waybill>
					<div class="fm-waybill__top">
						<span class="fm-waybill__brand">FULFILLMEN</span>
						<span class="fm-waybill__svc">B2C SPECIAL LINE</span>
					</div>
					<div class="fm-waybill__big">
						<span><small>DOOR-TO-DOOR</small><b>7–15</b><em>DAYS</em></span>
						<span class="fm-waybill__trk"><small>TRACKING</small><b>FULL</b><em>EVERY MILESTONE</em></span>
					</div>
					<div class="fm-waybill__row">
						<span><small>SHIP FROM</small><b>Fulfillmen Warehouse</b><em><?php echo esc_html( $o['address'] ); ?></em></span>
					</div>
					<div class="fm-waybill__row">
						<span><small>SHIP TO</small><b data-fm-dest>Your customer</b><em data-fm-dest-c>Los Angeles, US</em></span>
					</div>
					<div class="fm-waybill__grid">
						<span><small>QC</small><b class="ok"><?php echo $i( 'check' ); // phpcs:ignore ?> Passed</b></span>
						<span><small>PHOTOS</small><b>12 / 12</b></span>
						<span><small>LANDED / UNIT</small><b>$4.82<sup>*</sup></b></span>
					</div>
					<div class="fm-waybill__code">
						<?php echo fm_lp_barcode( 'FM20481193CN' ); // phpcs:ignore ?>
						<span>FM 2048 1193 CN</span>
					</div>
					<p class="fm-waybill__foot">* Example only. Your quote is itemised for your product.</p>
					<span class="fm-stamp fm-stamp--hero">QC<br>PASSED</span>
				</div>
			</div>
		</div>

		<!-- Departure board -->
		<div class="fm-wrap">
			<div class="fm-board fm-rise" style="--d:6" aria-hidden="true">
				<div class="fm-board__head"><span>Outbound · <?php echo esc_html( strtoupper( $city ) ); ?> hub</span><span>Illustrative lanes</span></div>
				<div class="fm-board__cols"><span>Destination</span><span>Service</span><span>Transit</span><span>Status</span></div>
				<div class="fm-board__rows" data-fm-board>
					<?php foreach ( array_slice( $board, 0, 3 ) as $b ) : ?>
						<div class="fm-board__row">
							<span class="fm-flap"><?php echo esc_html( $b[0] ); ?> <em><?php echo esc_html( $b[1] ); ?></em></span>
							<span class="fm-flap"><?php echo esc_html( $b[2] ); ?></span>
							<span class="fm-flap"><?php echo esc_html( $b[3] ); ?></span>
							<span class="fm-flap fm-st <?php echo esc_attr( $b[5] ); ?>"><?php echo esc_html( $b[4] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
				<script type="application/json" data-fm-board-data><?php echo wp_json_encode( $board ); ?></script>
			</div>
		</div>

		<!-- Trust strip -->
		<div class="fm-wrap">
			<ul class="fm-trust" aria-label="Trust">
				<li><?php echo $i( 'clock' ); // phpcs:ignore ?> <?php echo esc_html( $proof ); ?></li>
				<li><?php echo $i( 'shield' ); // phpcs:ignore ?> No minimums, no lock-in</li>
				<li class="fm-trust__plat"><span>Amazon</span><span>Shopify</span><span>eBay</span><span>WooCommerce</span><span>TikTok Shop</span></li>
			</ul>
		</div>
	</section>

	<!-- ================= 2. PICK YOUR PATH ================= -->
	<section class="fm-section fm-paper fm-paths" id="path" aria-labelledby="fm-path-title">
		<div class="fm-wrap">
			<div class="fm-head fm-head--split">
				<div>
					<p class="fm-kicker fm-reveal"><span>02</span> Pick your path</p>
					<h2 id="fm-path-title" class="fm-h2 fm-reveal">How do you sell?</h2>
				</div>
				<p class="fm-reveal">Choose one. Your quote is built around your business model, and the right specialist replies.</p>
			</div>

			<div class="fm-cartons">
				<?php foreach ( $paths as $key => $p ) : ?>
					<a class="fm-carton fm-reveal<?php echo $pre === $key ? ' is-selected' : ''; ?>" href="?path=<?php echo esc_attr( $key ); ?>#quote" data-fm-path="<?php echo esc_attr( $key ); ?>">
						<span class="fm-carton__flap" aria-hidden="true"><i class="fm-carton__tape"></i></span>
						<span class="fm-carton__marks" aria-hidden="true">
							<span>↑↑</span><span>FRAGILE</span><span>KEEP DRY</span>
						</span>
						<span class="fm-carton__sticker">
							<span class="fm-carton__code"><?php echo esc_html( $p[0] ); ?></span>
							<?php echo fm_lp_barcode( $key, 120, 22 ); // phpcs:ignore ?>
						</span>
						<h3><?php echo esc_html( $p[1] ); ?></h3>
						<p><?php echo esc_html( $p[2] ); ?></p>
						<ul>
							<?php foreach ( $p[3] as $li ) : ?>
								<li><?php echo $i( 'check' ); // phpcs:ignore ?> <?php echo esc_html( $li ); ?></li>
							<?php endforeach; ?>
						</ul>
						<span class="fm-carton__cta"><?php echo esc_html( $p[4] ); ?> <?php echo $i( 'arrow' ); // phpcs:ignore ?></span>
						<span class="fm-stamp fm-stamp--sel" aria-hidden="true">SELECTED</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ================= 3. HOW IT WORKS ================= -->
	<section class="fm-section fm-how" id="how" aria-labelledby="fm-how-title">
		<div class="fm-wrap">
			<div class="fm-head fm-head--split">
				<div>
					<p class="fm-kicker fm-reveal"><span>03</span> How it works</p>
					<h2 id="fm-how-title" class="fm-h2 fm-reveal">Source <i>→</i> Inspect <i>→</i> Store <i>→</i> Ship</h2>
				</div>
				<p class="fm-reveal">One team handles the whole line. No agent here and 3PL there, so nothing gets lost between companies and every cost is on one invoice.</p>
			</div>

			<div class="fm-line" data-fm-line>
				<div class="fm-line__belt" aria-hidden="true">
					<span class="fm-line__rail"><i class="fm-line__fill"></i></span>
					<span class="fm-line__box" data-fm-box>
						<svg viewBox="0 0 64 56">
							<path class="b-side" d="M6 18 32 30v24L6 42z"/><path class="b-front" d="M58 18 32 30v24l26-12z"/><path class="b-top" d="M6 18 32 6l26 12-26 12z"/>
							<path class="b-tape" d="M19 12l26 12"/>
							<rect class="b-qc" x="38" y="34" width="12" height="8" rx="1.5"/>
							<rect class="b-label" x="12" y="30" width="12" height="9" rx="1"/>
						</svg>
					</span>
				</div>
				<ol class="fm-line__stations">
					<?php foreach ( $steps as $n => $s ) : ?>
						<li class="fm-station">
							<span class="fm-station__no"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span>
							<span class="fm-station__ic"><?php echo $i( $s[0] ); // phpcs:ignore ?></span>
							<h3><?php echo esc_html( $s[1] ); ?></h3>
							<p><?php echo esc_html( $s[2] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
			<div class="fm-center fm-reveal"><a class="fm-btn fm-btn--dark" href="#quote" data-fm-cta="how">Get my free landed-cost quote <?php echo $i( 'arrow' ); // phpcs:ignore ?></a></div>
		</div>
	</section>

	<!-- ================= 4. OFFER B ================= -->
	<section class="fm-section fm-dark fm-offer" aria-labelledby="fm-offer-title">
		<div class="fm-wrap fm-offer__grid">
			<div class="fm-report fm-reveal" aria-hidden="true">
				<div class="fm-report__head">
					<div><small>PRE-SHIPMENT PHOTO INSPECTION</small><b>Report QC-7731</b></div>
					<span>PO 2048 · 500 units</span>
				</div>
				<div class="fm-report__photos">
					<?php for ( $k = 1; $k <= 6; $k++ ) : ?>
						<figure class="fm-polaroid" style="--k:<?php echo (int) $k; ?>"><span class="fm-polaroid__img"></span><figcaption>IMG_00<?php echo (int) $k; ?></figcaption></figure>
					<?php endfor; ?>
				</div>
				<ul class="fm-report__list">
					<li><span>Colour matches approved sample</span><b><?php echo $i( 'check' ); // phpcs:ignore ?> Pass</b></li>
					<li><span>Size &amp; dimensions</span><b><?php echo $i( 'check' ); // phpcs:ignore ?> Pass</b></li>
					<li><span>Logo &amp; print quality</span><b><?php echo $i( 'check' ); // phpcs:ignore ?> Pass</b></li>
					<li><span>Packaging &amp; labels</span><b><?php echo $i( 'check' ); // phpcs:ignore ?> Pass</b></li>
				</ul>
				<div class="fm-report__sign"><span>Inspector · <?php echo esc_html( $city ); ?> warehouse</span><span>Awaiting your approval</span></div>
				<span class="fm-stamp fm-stamp--ok" data-fm-stamp>APPROVED</span>
			</div>
			<div class="fm-offer__copy">
				<p class="fm-kicker fm-kicker--light fm-reveal"><span>04</span> Free on your first order</p>
				<h2 id="fm-offer-title" class="fm-h2 fm-reveal">See it before your customers do.</h2>
				<p class="fm-lead fm-reveal">Before anything ships, we photograph your products so you know exactly what your customers will receive.</p>
				<ul class="fm-ticks fm-reveal">
					<li><?php echo $i( 'check' ); // phpcs:ignore ?> Real photos from our warehouse floor, not supplier renders</li>
					<li><?php echo $i( 'check' ); // phpcs:ignore ?> You approve before anything is dispatched</li>
					<li><?php echo $i( 'check' ); // phpcs:ignore ?> Problems fixed in China, not after refunds</li>
				</ul>
				<a class="fm-btn fm-btn--primary fm-reveal" href="#quote" data-fm-cta="offer">Claim it with my free quote <?php echo $i( 'arrow' ); // phpcs:ignore ?></a>
			</div>
		</div>
	</section>

	<!-- ================= 5. PROOF ================= -->
	<section class="fm-section fm-paper fm-proof" id="proof" aria-labelledby="fm-proof-title">
		<div class="fm-wrap">
			<div class="fm-head fm-head--split">
				<div>
					<p class="fm-kicker fm-reveal"><span>05</span> Proof</p>
					<h2 id="fm-proof-title" class="fm-h2 fm-reveal">Faster from factory to front door.</h2>
				</div>
				<p class="fm-reveal">Numbers from our own operation, and the delivery promise we put in writing.</p>
			</div>

			<?php if ( $stats ) : ?>
				<dl class="fm-manifest">
					<?php foreach ( $stats as $n => $s ) : ?>
						<div class="fm-manifest__row fm-reveal">
							<dt><span><?php echo esc_html( sprintf( 'M-%02d', $n + 1 ) ); ?></span><?php echo esc_html( $s[2] ); ?></dt>
							<dd><span data-fm-count="<?php echo esc_attr( $s[0] ); ?>" data-fm-dec="<?php echo false !== strpos( $s[0], '.' ) ? 1 : 0; ?>"><?php echo esc_html( $s[0] ); ?></span><?php echo esc_html( $s[1] ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>

			<div class="fm-proof__grid">
				<div class="fm-tracking fm-reveal">
					<div class="fm-tracking__head">
						<div><small>TRACKING</small><b>FM 2048 1193 CN</b></div>
						<span class="fm-st s-done">Delivered</span>
					</div>
					<p class="fm-tracking__svc"><?php echo esc_html( $proof ); ?></p>
					<ol class="fm-tracking__list" data-fm-track>
						<li><b>Dispatched</b><small><?php echo esc_html( $city ); ?> hub · Day 0</small></li>
						<li><b>Export customs cleared</b><small>China</small></li>
						<li><b>In transit</b><small>Line haul, tracked</small></li>
						<li><b>Import customs &amp; last mile</b><small>Destination country</small></li>
						<li><b>Delivered</b><small>Day 7–15</small></li>
					</ol>
				</div>

				<div class="fm-vs fm-reveal">
					<p class="fm-vs__title">One team vs an agent plus a separate 3PL</p>
					<div role="table" aria-label="Fulfillmen compared with a sourcing agent plus a separate 3PL">
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
							<div class="fm-vs__row" role="row"><span role="rowheader"><?php echo esc_html( $r[0] ); ?></span><span role="cell" class="is-them"><?php echo $i( 'x' ); // phpcs:ignore ?> <?php echo esc_html( $r[1] ); ?></span><span role="cell" class="is-us"><?php echo $i( 'check' ); // phpcs:ignore ?> <?php echo esc_html( $r[2] ); ?></span></div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<?php if ( $media || $ph ) : ?>
				<div class="fm-gallery">
					<?php if ( $media ) : ?>
						<?php
						foreach ( array_slice( $media, 0, 5 ) as $m ) :
							$is_vid = (bool) preg_match( '/\.(mp4|webm)(\?|$)/i', $m );
							?>
							<figure class="fm-reveal">
								<?php if ( $is_vid ) : ?>
									<video data-fm-lazy-video muted loop playsinline preload="none" aria-label="Fulfillmen warehouse video"><source data-src="<?php echo esc_url( $m ); ?>" type="video/mp4"></video>
								<?php else : ?>
									<img src="<?php echo esc_url( $m ); ?>" alt="Inside the Fulfillmen warehouse" loading="lazy" decoding="async" width="600" height="450">
								<?php endif; ?>
							</figure>
						<?php endforeach; ?>
					<?php else : ?>
						<?php for ( $k = 0; $k < 5; $k++ ) : ?>
							<figure class="fm-ph fm-reveal"><span>Warehouse photo / video<small>Settings → Fulfillmen Landing · admins only</small></span></figure>
						<?php endfor; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $tests || $ph ) : ?>
				<div class="fm-tests">
					<?php
					$cards = $tests ? array_slice( $tests, 0, 3 ) : array_fill( 0, 3, array( 'Client name', 'Store type', 'Real result', 'Add 3–5 real testimonials in Settings → Fulfillmen Landing. Only admins see these placeholders.' ) );
					foreach ( $cards as $t ) :
						?>
						<figure class="fm-test fm-reveal<?php echo $tests ? '' : ' fm-ph'; ?>">
							<p class="fm-test__result"><?php echo esc_html( $t[2] ); ?></p>
							<blockquote>“<?php echo esc_html( $t[3] ); ?>”</blockquote>
							<figcaption><b><?php echo esc_html( $t[0] ); ?></b><span><?php echo esc_html( $t[1] ); ?></span></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- ================= 6. QUOTE FORM ================= -->
	<section class="fm-section fm-dark fm-quote" id="quote" aria-labelledby="fm-quote-title">
		<?php echo $world( 'fmq-', 'fm-world--quote' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="fm-wrap fm-quote__grid">
			<div class="fm-quote__copy">
				<p class="fm-kicker fm-kicker--light fm-reveal"><span>06</span> Free landed-cost quote</p>
				<h2 id="fm-quote-title" class="fm-h2 fm-reveal">Know your real cost per unit in 24&nbsp;hours.</h2>
				<p class="fm-lead fm-reveal">Send your product or store link. We reply within 24 hours with full pricing, line by line.</p>

				<div class="fm-receipt fm-reveal" aria-label="What your quote includes">
					<div class="fm-receipt__h"><b>LANDED-COST QUOTE</b><span>Reply ≤ 24 h</span></div>
					<ul>
						<li><span>Product cost</span><i></i></li>
						<li><span>Quality check</span><i></i></li>
						<li><span>Prep &amp; labelling</span><i></i></li>
						<li><span>Warehousing</span><i></i></li>
						<li><span>Shipping to customer / Amazon</span><i></i></li>
					</ul>
					<div class="fm-receipt__t"><span>Landed cost per unit</span><b>No surprises</b></div>
				</div>

				<ul class="fm-ticks fm-ticks--sm fm-reveal">
					<li><?php echo $i( 'check' ); // phpcs:ignore ?> <?php echo esc_html( $proof ); ?></li>
					<li><?php echo $i( 'check' ); // phpcs:ignore ?> Free photo inspection on your first order</li>
					<li><?php echo $i( 'check' ); // phpcs:ignore ?> Trusted by Amazon, Shopify and eBay sellers</li>
				</ul>
			</div>

			<div class="fm-formdoc fm-reveal">
				<div class="fm-formdoc__head">
					<div><small>QUOTE REQUEST</small><b>Free landed-cost quote</b></div>
					<?php echo fm_lp_barcode( 'QUOTE', 110, 30 ); // phpcs:ignore ?>
				</div>
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
						<span data-fm-path-empty <?php echo $pre ? 'hidden' : ''; ?>>Service: not chosen · <a href="#path">pick your path</a> (optional)</span>
						<span data-fm-path-set <?php echo $pre ? '' : 'hidden'; ?>><?php echo $i( 'check' ); // phpcs:ignore ?> Service: <b data-fm-path-label><?php echo $pre ? esc_html( $types[ $pre ] ) : ''; ?></b> · <a href="#path">change</a></span>
					</p>

					<?php if ( $fm_err ) : ?>
						<p class="fm-form__alert" role="alert">Please check your details and try again.</p>
					<?php endif; ?>
					<p class="fm-form__alert" role="alert" data-fm-alert hidden></p>

					<div class="fm-boxes">
						<div class="fm-field">
							<label for="fm-name"><span>01</span> Your name</label>
							<input id="fm-name" name="name" type="text" autocomplete="name" required minlength="2" placeholder="Jane Smith">
							<small class="fm-err" data-err="name"></small>
						</div>
						<div class="fm-field">
							<label for="fm-email"><span>02</span> Email</label>
							<input id="fm-email" name="email" type="email" autocomplete="email" inputmode="email" required placeholder="you@store.com">
							<small class="fm-err" data-err="email"></small>
						</div>
						<div class="fm-field fm-field--full">
							<label for="fm-phone"><span>03</span> WhatsApp / phone</label>
							<input id="fm-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required placeholder="+1 555 010 2030">
							<small class="fm-err" data-err="phone"></small>
						</div>
						<div class="fm-field fm-field--full">
							<label for="fm-links"><span>04</span> Store link or product link(s)</label>
							<textarea id="fm-links" name="links" rows="2" required placeholder="yourstore.com, or 1688 / Alibaba / Amazon links. Destination countries help too."></textarea>
							<small class="fm-err" data-err="links"></small>
						</div>
						<fieldset class="fm-field fm-field--full fm-chips">
							<legend><span>05</span> Monthly order volume</legend>
							<div class="fm-chips__row">
								<?php foreach ( $vols as $k => $label ) : ?>
									<label class="fm-chipopt"><input type="radio" name="volume" value="<?php echo esc_attr( $k ); ?>" required><span><?php echo esc_html( preg_replace( '/ orders \/ month$/', '', $label ) ); ?></span></label>
								<?php endforeach; ?>
							</div>
							<small class="fm-err" data-err="volume"></small>
						</fieldset>
					</div>

					<button class="fm-btn fm-btn--primary fm-btn--block fm-magnetic" type="submit" data-fm-submit>
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

	<!-- ================= 7. FAQ ================= -->
	<section class="fm-section fm-faq" id="faq" aria-labelledby="fm-faq-title">
		<div class="fm-wrap fm-faq__grid">
			<div class="fm-faq__side">
				<p class="fm-kicker fm-reveal"><span>07</span> FAQ</p>
				<h2 id="fm-faq-title" class="fm-h2 fm-reveal">Questions sellers ask before they switch.</h2>
				<p class="fm-reveal">Something else? <a href="#quote">Ask in the quote form</a> and a real person replies within 24 hours.</p>
			</div>
			<div class="fm-acc">
				<?php foreach ( $faqs as $n => $f ) : ?>
					<details class="fm-acc__item fm-reveal">
						<summary><span class="fm-acc__no"><?php echo esc_html( sprintf( 'Q.%02d', $n + 1 ) ); ?></span><span class="fm-acc__q"><?php echo esc_html( $f[0] ); ?></span><span class="fm-acc__pm" aria-hidden="true"></span></summary>
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

	<!-- ================= 8. FINAL CTA + FOOTER ================= -->
	<section class="fm-final fm-dark" aria-labelledby="fm-final-title">
		<div class="fm-wrap fm-final__in">
			<p class="fm-kicker fm-kicker--light fm-reveal"><span>08</span> Ready when you are</p>
			<h2 id="fm-final-title" class="fm-h2 fm-h2--xl fm-reveal">Find out what it really costs to sell from China.</h2>
			<p class="fm-lead fm-reveal">Send a product link. Get an itemised landed-cost quote within 24 hours.</p>
			<div class="fm-ctas fm-reveal">
				<a class="fm-btn fm-btn--primary fm-magnetic" href="#quote" data-fm-cta="final">Get my free landed-cost quote <?php echo $i( 'arrow' ); // phpcs:ignore ?></a>
			</div>
			<p class="fm-micro fm-reveal">Free photo inspection on your first order · No minimums · No lock-in</p>
		</div>
		<footer class="fm-foot">
			<div class="fm-wrap fm-foot__in">
				<p><?php echo $i( 'pin' ); // phpcs:ignore ?> Fulfillmen · <?php echo esc_html( $o['address'] ); ?> · 23.1°N 114.4°E</p>
				<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Fulfillmen · <a href="<?php echo esc_url( $o['privacy_url'] ); ?>">Privacy</a> · <a href="#top">Back to top ↑</a></p>
			</div>
		</footer>
	</section>

	<div class="fm-sticky" data-fm-sticky aria-hidden="true">
		<a class="fm-btn fm-btn--primary fm-btn--block" href="#quote" data-fm-cta="sticky" tabindex="-1">Get my free landed-cost quote</a>
	</div>
</div>
