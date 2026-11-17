<?php
/**
 * [fulfillmen_landing] — v5.
 * White page, Fulfillmen teal / deep teal / mint, real Fulfillmen photography,
 * logistics details (tracking, QC report, conveyor, waybill form).
 * Flow follows the CRO brief §7: Hero → Pick your path → How it works →
 * Offer B → Proof → Quote form → FAQ → Final CTA. Header/footer live in canvas.php.
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
$logos = fm_lp_rows( $o['platform_logos'], 2 );
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

/* Routes from the Huizhou hub (used on the dark quote section). */
$hub    = $map['cities']['hz'];
$routes = array();
foreach ( array( 'la', 'ny', 'to', 'lo', 'fr', 'du', 'sg', 'sy' ) as $k ) {
	$c    = $map['cities'][ $k ];
	$dx   = $c['x'] - $hub['x'];
	$dy   = $c['y'] - $hub['y'];
	$dist = sqrt( $dx * $dx + $dy * $dy );
	$cy   = min( $hub['y'], $c['y'] ) - $dist * ( $c['y'] > $hub['y'] + 80 ? 0.1 : 0.32 );
	$routes[ $k ] = sprintf( 'M%.1f %.1f Q%.1f %.1f %.1f %.1f', $hub['x'], $hub['y'], ( $hub['x'] + $c['x'] ) / 2, $cy, $c['x'], $c['y'] );
}

$paths = array(
	'fba'          => array( 'Amazon FBA', 'Amazon FBA sellers', 'We prep, label and ship straight to Amazon warehouses.', array( 'FNSKU labels & poly bags', 'Carton & pallet prep to Amazon spec', 'Sea, air or rail to FBA centres' ), 'Get my FBA quote', $o['img_fba'], 'amazon' ),
	'dropshipping' => array( 'Dropshipping', 'Dropshippers', 'Source from China and ship each order directly to your customer.', array( 'Factory sourcing & price negotiation', 'Orders sync from your store', 'Tracked door-to-door in 7–15 days' ), 'Get my dropshipping quote', $o['img_dropshipping'], 'drop' ),
	'own_brand'    => array( 'Own stock', 'Brands with their own stock', 'Store your inventory with us. We pick, pack and ship every order.', array( 'Warehousing in ' . $city, 'Branded packaging & inserts', 'Pick, pack and ship per order' ), 'Get my fulfilment quote', $o['img_own_brand'], 'brand' ),
);

$steps = array(
	array( 'search', 'Source', 'We vet factories, negotiate your price and place the order.', 'a shortlist of vetted factories with prices' ),
	array( 'camera', 'Inspect', 'Every batch is checked against your spec and photographed.', 'a photo QC report to approve' ),
	array( 'box', 'Store', "Stock sits in our {$city} warehouse, live in your dashboard.", 'live stock levels per SKU and channel' ),
	array( 'plane', 'Ship', 'Picked, packed and shipped to customers or Amazon, fully tracked.', 'a tracking number for every parcel' ),
);

/* Real Fulfillmen photography reused for the QC visuals. */
$qc_imgs = array_values( array_filter( array( $o['img_fba'], $o['hero_image'], $o['img_dropshipping'], $o['img_own_brand'], $o['img_fba'], $o['hero_image'] ) ) );

/* The small product screens shown for each "How it works" step. */
$panel = function ( $n ) use ( $i, $city, $qc_imgs ) {
	if ( 0 === $n ) {
		echo '<div class="fm-ui"><p class="fm-ui__h"><b>Supplier comparison</b><small>Glass bottle 500 ml · 500 units</small></p>';
		foreach ( array( array( 'A', 'Factory A', 'Ningbo · audited', '$1.92', 'MOQ 500', true ), array( 'B', 'Factory B', 'Yiwu · audited', '$2.10', 'MOQ 300', false ), array( 'C', 'Factory C', 'Shenzhen', '$1.78', 'MOQ 5,000', false ) ) as $f ) {
			printf( '<div class="fm-ui__row%s"><span class="fm-ui__av">%s</span><b>%s<small>%s</small></b><span>%s<small>/unit</small></span><span class="fm-ui__moq">%s</span>%s</div>', $f[5] ? ' is-pick' : '', esc_html( $f[0] ), esc_html( $f[1] ), esc_html( $f[2] ), esc_html( $f[3] ), esc_html( $f[4] ), $f[5] ? '<em>Selected</em>' : '<em class="is-off">Compare</em>' );
		}
		echo '<p class="fm-ui__foot"><span>Negotiated on your behalf</span><b>−11% vs first offer</b></p></div>';
	} elseif ( 1 === $n ) {
		echo '<div class="fm-ui"><p class="fm-ui__h"><b>QC-7731</b><small>500 units · 12 photos</small></p><div class="fm-ui__photos">';
		foreach ( array_slice( $qc_imgs, 0, 3 ) as $k => $src ) {
			printf( '<span style="--p:%d%%"><img src="%s" alt="" loading="lazy" decoding="async" width="200" height="200"></span>', 20 + $k * 25, esc_url( $src ) );
		}
		echo '</div><ul class="fm-ui__list">';
		foreach ( array( 'Colour matches sample', 'Size & dimensions', 'Packaging & labels' ) as $c ) {
			printf( '<li><span>%s</span><b>%s Pass</b></li>', esc_html( $c ), $i( 'check' ) ); // phpcs:ignore
		}
		echo '</ul><p class="fm-ui__foot"><span>Result</span><b>Passed · awaiting your approval</b></p></div>';
	} elseif ( 2 === $n ) {
		echo '<div class="fm-ui"><p class="fm-ui__h"><b>GB-500-CLR</b><small>Glass bottle 500 ml</small></p>';
		echo '<div class="fm-ui__big"><small>Available to sell</small><b>2,184</b><span>units · +500 received today</span></div>';
		echo '<div class="fm-ui__code">' . fm_lp_barcode( 'GB-500-CLR', 240, 40 ) . '<span>GB-500-CLR · LOT 2048</span></div>'; // phpcs:ignore
		printf( '<div class="fm-ui__split"><p><small>Bin</small><b>%s</b></p><p><small>Shopify</small><b>1,240</b></p><p><small>Amazon</small><b>944</b></p></div></div>', 'A14-03' );
	} else {
		printf( '<div class="fm-ui"><p class="fm-ui__h"><b>FM 2048 1193 CN</b><small>%s → Los Angeles · B2C line</small></p>', esc_html( $city ) );
		printf( '<div class="fm-ui__route"><b>%s</b><i><span>%s</span></i><b>Los Angeles</b></div>', esc_html( $city ), $i( 'plane' ) ); // phpcs:ignore
		echo '<ol class="fm-ui__track"><li class="ok">Packed</li><li class="ok">Dispatched</li><li class="now">In transit</li><li>Delivered</li></ol>';
		echo '<div class="fm-ui__split fm-ui__split--ship"><p><small>Service</small><b>B2C special line</b></p><p><small>Parcel</small><b>0.42 kg</b></p><p><small>Last scan</small><b>Export cleared</b></p></div>';
		echo '<p class="fm-ui__foot"><span>Estimated delivery</span><b>Day 7–15, fully tracked</b></p></div>';
	}
};

$faqs = array(
	array( 'How is pricing worked out?', 'You only pay for what you use: product cost (if we source for you), quality check, prep, storage and shipping per unit dispatched. No hidden fees and no surprise line items at month end. Your free landed-cost quote itemises every line so you know your margin before you commit.' ),
	array( 'Is there a minimum order?', 'No minimum storage, no compulsory SKUs and no monthly order commitments. Ship 50 units to test a product or 50,000 ahead of peak season. If we source from a factory, that factory\'s own minimum may apply and your quote will show it upfront.' ),
	array( 'How long does shipping take?', 'B2C special lines deliver in 7–15 days door-to-door, fully tracked. Express couriers (DHL, FedEx, UPS) and air, sea and rail freight for Amazon FBA replenishment are also available. Your quote shows the options and price for each destination country.' ),
	array( 'How does your quality-check process work?', 'Goods arrive at our China warehouse, are counted and checked against your specification (product, colour, size, packaging and labels) before they are stored or shipped. If anything is off, we flag it to you with photos before it leaves the building.' ),
	array( 'What is the free photo inspection?', 'On your first order, before anything ships, we photograph your products so you know exactly what your customers will receive. It costs nothing and you approve the photos before dispatch.' ),
	array( 'Which stores and marketplaces do you work with?', 'Shopify, WooCommerce and Amazon connect directly so orders sync into our warehouse system automatically. eBay, Etsy, Lazada, TikTok Shop and other channels can be handled too: tell us what you sell on in the quote form.' ),
);
?>
<div class="fm-lp" data-fm-lp>

	<!-- ================= 1. HERO: centred copy on a route map, live dashboard below ================= -->
	<section class="fm-hero" aria-labelledby="fm-hero-title">
		<svg class="fm-hero__map" data-fm-map viewBox="0 0 <?php echo (int) $map['w']; ?> <?php echo (int) $map['h']; ?>" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
			<defs><path id="fm-land" d="<?php echo esc_attr( $map['dots'] ); ?>"/></defs>
			<use href="#fm-land" class="fm-map__land"/>
			<g class="fm-map__routes">
				<?php foreach ( $routes as $k => $d ) : ?><path id="fmh-<?php echo esc_attr( $k ); ?>" d="<?php echo esc_attr( $d ); ?>"/><?php endforeach; ?>
			</g>
			<g class="fm-map__parcels">
				<?php foreach ( $routes as $k => $d ) : ?><circle r="4.5" data-route="fmh-<?php echo esc_attr( $k ); ?>"/><?php endforeach; ?>
			</g>
			<?php foreach ( $routes as $k => $d ) : $c = $map['cities'][ $k ]; ?>
				<circle class="fm-map__city" cx="<?php echo esc_attr( $c['x'] ); ?>" cy="<?php echo esc_attr( $c['y'] ); ?>" r="4"/>
			<?php endforeach; ?>
			<g transform="translate(<?php echo esc_attr( $hub['x'] . ' ' . $hub['y'] ); ?>)" class="fm-map__hub"><circle class="fm-world__ping" r="9"/><circle r="6.5"/></g>
		</svg>

		<div class="fm-wrap fm-hero__in">
			<p class="fm-chip fm-rise" style="--d:0"><span class="fm-live"></span> Sourcing · QC · 3PL · FBA prep · Dropshipping</p>
			<h1 id="fm-hero-title" class="fm-h1 fm-rise" style="--d:1">Source, store and ship from China, with <span class="fm-hl">one team, one dashboard</span> and no surprise costs.</h1>
			<p class="fm-hero__sub fm-rise" style="--d:2">Sourcing, quality checks, warehousing, Amazon FBA prep, dropshipping and global shipping, all handled by Fulfillmen's own China-based team.</p>
			<div class="fm-ctas fm-rise" style="--d:3">
				<a class="fm-btn fm-btn--primary fm-magnetic" href="#quote" data-fm-cta="hero">Get my free landed-cost quote <span class="fm-btn__dot"><?php echo $i( 'arrow' ); // phpcs:ignore ?></span></a>
				<a class="fm-btn fm-btn--line" href="#how">See how it works</a>
			</div>
			<ul class="fm-checks fm-rise" style="--d:4">
				<li><?php echo $i( 'check' ); // phpcs:ignore ?> Itemised quote in 24 hours</li>
				<li><?php echo $i( 'check' ); // phpcs:ignore ?> No minimums, no lock-in</li>
				<li><?php echo $i( 'check' ); // phpcs:ignore ?> Free photo inspection</li>
			</ul>
		</div>

		<div class="fm-wrap fm-hero__stage fm-rise" style="--d:5">
			<div class="fm-dash" data-fm-dash role="img" aria-label="Example of the Fulfillmen client dashboard: orders, quality checks, stock and shipments in one place">
				<div class="fm-dash__top">
					<span class="fm-dash__dots"><i></i><i></i><i></i></span>
					<span class="fm-dash__brand"><b>f</b> fulfillmen</span>
					<span class="fm-dash__tabs"><b>Overview</b><span>Orders</span><span>Inventory</span><span>Shipments</span><span>QC reports</span></span>
					<span class="fm-dash__search"><?php echo $i( 'search' ); // phpcs:ignore ?> Search SKU, order or tracking no.</span>
					<span class="fm-dash__av">JS</span>
				</div>
				<div class="fm-dash__body">
					<div class="fm-dash__hello">
						<div><small>Overview · updated just now</small><b>Good morning, Jane</b></div>
						<span class="fm-dash__tag">Sample data</span>
					</div>
					<div class="fm-dash__kpis">
						<?php
						foreach ( array(
							array( 'bag', 'Orders today', '1,284', '+12% vs last week', '0,22 10,18 20,20 30,12 40,14 50,8 60,6' ),
							array( 'camera', 'In quality check', '46', '12 photos per SKU', '0,14 10,16 20,10 30,14 40,9 50,12 60,8' ),
							array( 'truck', 'In transit', '318', '7–15 days door-to-door', '0,20 10,16 20,17 30,11 40,12 50,7 60,4' ),
							array( 'box', 'Landed cost / unit', '$4.82', 'All-in, itemised', '0,10 10,12 20,11 30,13 40,12 50,14 60,13' ),
						) as $k ) :
							?>
							<div class="fm-dash__kpi">
								<span class="fm-dash__ki"><?php echo $i( $k[0] ); // phpcs:ignore ?></span>
								<small><?php echo esc_html( $k[1] ); ?></small>
								<b><?php echo esc_html( $k[2] ); ?></b>
								<em><?php echo esc_html( $k[3] ); ?></em>
								<svg class="fm-dash__spark" viewBox="0 0 60 24" preserveAspectRatio="none"><polyline points="<?php echo esc_attr( $k[4] ); ?>"/></svg>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="fm-dash__grid">
						<div class="fm-dash__card fm-dash__map">
							<div class="fm-dash__ch"><b>Live shipments</b><span><i class="fm-live"></i> 318 in transit</span></div>
							<svg viewBox="140 70 980 430" preserveAspectRatio="xMidYMid meet">
								<use href="#fm-land" class="fm-map__land"/>
								<g class="fm-dash__routes"><?php foreach ( $routes as $d ) : ?><path d="<?php echo esc_attr( $d ); ?>"/><?php endforeach; ?></g>
								<?php foreach ( $routes as $k => $d ) : $c = $map['cities'][ $k ]; ?><circle class="fm-map__city" cx="<?php echo esc_attr( $c['x'] ); ?>" cy="<?php echo esc_attr( $c['y'] ); ?>" r="6"/><?php endforeach; ?>
								<g transform="translate(<?php echo esc_attr( $hub['x'] . ' ' . $hub['y'] ); ?>)" class="fm-map__hub"><circle class="fm-world__ping" r="11"/><circle r="8"/></g>
							</svg>
							<ul class="fm-dash__legend"><li><i></i>B2C special line</li><li><i></i>Air freight</li><li><i></i>Sea to FBA</li></ul>
						</div>
						<div class="fm-dash__card fm-dash__qc">
							<div class="fm-dash__ch"><b>QC-7731 · 500 units</b><span class="fm-pill fm-pill--ok">Passed</span></div>
							<div class="fm-dash__photos">
								<?php foreach ( array_slice( $qc_imgs, 0, 4 ) as $n => $src ) : ?>
									<span style="--p:<?php echo (int) ( 20 + $n * 22 ); ?>%"><img src="<?php echo esc_url( $src ); ?>" alt="" loading="lazy" decoding="async" width="160" height="160"></span>
								<?php endforeach; ?>
							</div>
							<ul class="fm-dash__checks">
								<li><?php echo $i( 'check' ); // phpcs:ignore ?> Colour matches sample</li>
								<li><?php echo $i( 'check' ); // phpcs:ignore ?> Size &amp; dimensions</li>
								<li><?php echo $i( 'check' ); // phpcs:ignore ?> Labels &amp; packaging</li>
							</ul>
							<span class="fm-dash__btn">Approve dispatch</span>
						</div>
						<div class="fm-dash__card fm-dash__table">
							<div class="fm-dash__ch"><b>Recent shipments</b><span>View all</span></div>
							<?php
							foreach ( array(
								array( 'FM 2048 1193', 'Los Angeles, US', 'B2C line', 'In transit', 'go' ),
								array( 'FM 2048 1187', 'London, UK', 'Air', 'Customs', 'wait' ),
								array( 'FM 2048 1176', 'Sydney, AU', 'B2C line', 'Delivered', 'ok' ),
								array( 'FBA 0931', 'Amazon FBA, US', 'Sea', 'Booked', 'new' ),
							) as $r ) :
								?>
								<div class="fm-dash__row"><b><?php echo esc_html( $r[0] ); ?></b><span><?php echo esc_html( $r[1] ); ?></span><span><?php echo esc_html( $r[2] ); ?></span><span class="fm-pill fm-pill--<?php echo esc_attr( $r[4] ); ?>"><?php echo esc_html( $r[3] ); ?></span></div>
							<?php endforeach; ?>
						</div>
						<div class="fm-dash__card fm-dash__inv">
							<div class="fm-dash__ch"><b>Stock · <?php echo esc_html( $city ); ?></b><span>4 SKUs</span></div>
							<?php foreach ( array( array( 'Glass bottle 500 ml', '2,184', 82 ), array( 'Yoga mat, cork', '1,420', 58 ), array( 'Phone case, matte', '960', 40 ), array( 'Desk lamp, LED', '310', 14 ) ) as $s ) : ?>
								<div class="fm-dash__bar<?php echo $s[2] < 20 ? ' is-low' : ''; ?>"><span><?php echo esc_html( $s[0] ); ?></span><b><?php echo esc_html( $s[1] ); ?></b><i style="--w:<?php echo (int) $s[2]; ?>%"></i></div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="fm-wrap">
			<?php if ( $logos ) : ?>
				<div class="fm-logos">
					<p>Built for sellers on</p>
					<div class="fm-logos__row">
						<?php foreach ( $logos as $l ) : ?>
							<img src="<?php echo esc_url( $l[0] ); ?>" alt="<?php echo esc_attr( $l[1] ); ?>" width="245" height="93" loading="lazy" decoding="async">
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<dl class="fm-kpis">
				<div><dt>Door-to-door, fully tracked</dt><dd>7–15<span> days</span></dd></div>
				<div><dt>To get your itemised quote</dt><dd>24<span> h</span></dd></div>
				<?php foreach ( array_slice( $stats, 0, 2 ) as $s ) : ?>
					<div><dt><?php echo esc_html( $s[2] ); ?></dt><dd><span class="fm-kpis__n" data-fm-count="<?php echo esc_attr( $s[0] ); ?>" data-fm-dec="<?php echo false !== strpos( $s[0], '.' ) ? 1 : 0; ?>"><?php echo esc_html( $s[0] ); ?></span><?php echo esc_html( $s[1] ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		</div>
	</section>

	<!-- ================= 2. PICK YOUR PATH: cards fan out on scroll ================= -->
	<section class="fm-section fm-paths" id="path" aria-labelledby="fm-path-title">
		<div class="fm-wrap">
			<div class="fm-head fm-head--center">
				<p class="fm-kicker fm-reveal">Pick your path</p>
				<h2 id="fm-path-title" class="fm-h2 fm-reveal">How do you sell?</h2>
				<p class="fm-reveal">Choose one. Your quote is built around your business model, and the right specialist replies.</p>
			</div>

			<div class="fm-fan" data-fm-fan>
				<span class="fm-fan__glow" aria-hidden="true"></span>
				<?php
				$n = -1;
				foreach ( $paths as $key => $p ) :
					?>
					<div class="fm-fan__slot" style="--i:<?php echo (int) $n; ?>">
						<a class="fm-pathcard<?php echo $pre === $key ? ' is-selected' : ''; ?>" href="?path=<?php echo esc_attr( $key ); ?>#quote" data-fm-path="<?php echo esc_attr( $key ); ?>">
							<span class="fm-pathcard__img">
								<?php if ( $p[5] ) : ?>
									<img src="<?php echo esc_url( $p[5] ); ?>" alt="" loading="lazy" decoding="async" width="800" height="500">
								<?php endif; ?>
								<span class="fm-pathcard__ic"><?php echo $i( $p[6] ); // phpcs:ignore ?></span>
								<span class="fm-pathcard__check" aria-hidden="true"><?php echo $i( 'check' ); // phpcs:ignore ?></span>
							</span>
							<span class="fm-pathcard__body">
								<span class="fm-pathcard__tag"><?php echo esc_html( $p[0] ); ?></span>
								<h3><?php echo esc_html( $p[1] ); ?></h3>
								<p><?php echo esc_html( $p[2] ); ?></p>
								<ul>
									<?php foreach ( $p[3] as $li ) : ?>
										<li><?php echo $i( 'check' ); // phpcs:ignore ?> <?php echo esc_html( $li ); ?></li>
									<?php endforeach; ?>
								</ul>
								<span class="fm-pathcard__cta"><?php echo esc_html( $p[4] ); ?> <?php echo $i( 'arrow' ); // phpcs:ignore ?></span>
							</span>
						</a>
					</div>
					<?php
					$n++;
				endforeach;
				?>
			</div>
		</div>
	</section>

	<!-- ================= 3. HOW IT WORKS: steps + live screen ================= -->
	<section class="fm-section fm-how" id="how" aria-labelledby="fm-how-title">
		<div class="fm-wrap">
			<div class="fm-head fm-head--split">
				<div>
					<p class="fm-kicker fm-reveal">How it works</p>
					<h2 id="fm-how-title" class="fm-h2 fm-reveal">Source <i>→</i> Inspect <i>→</i> Store <i>→</i> Ship</h2>
				</div>
				<p class="fm-reveal">One team handles the whole line. No agent here and 3PL there, so nothing gets lost between companies and every cost is on one invoice.</p>
			</div>

			<div class="fm-flow" data-fm-flow>
				<ol class="fm-flow__steps">
					<li class="fm-flow__rail" aria-hidden="true"><i data-fm-flow-fill></i></li>
					<?php foreach ( $steps as $n => $s ) : ?>
						<li class="fm-flow__step<?php echo 0 === $n ? ' is-on' : ''; ?>" data-fm-step="<?php echo (int) $n; ?>">
							<span class="fm-flow__no"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span>
							<div class="fm-flow__txt">
								<span class="fm-flow__ic"><?php echo $i( $s[0] ); // phpcs:ignore ?></span>
								<h3><?php echo esc_html( $s[1] ); ?></h3>
								<p><?php echo esc_html( $s[2] ); ?></p>
								<span class="fm-flow__get"><?php echo $i( 'check' ); // phpcs:ignore ?> You get: <?php echo esc_html( $s[3] ); ?></span>
								<div class="fm-flow__inline" aria-hidden="true"><?php $panel( $n ); ?></div>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
				<div class="fm-flow__stage" aria-hidden="true">
					<div class="fm-flow__screen">
						<div class="fm-flow__bar"><span class="fm-dash__dots"><i></i><i></i><i></i></span><span data-fm-flow-label><?php echo esc_html( $steps[0][1] ); ?></span></div>
						<?php foreach ( $steps as $n => $s ) : ?>
							<div class="fm-flow__panel<?php echo 0 === $n ? ' is-on' : ''; ?>" data-fm-panel="<?php echo (int) $n; ?>" data-label="<?php echo esc_attr( $s[1] ); ?>"><?php $panel( $n ); ?></div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="fm-center fm-reveal"><a class="fm-btn fm-btn--primary" href="#quote" data-fm-cta="how">Get my free landed-cost quote <span class="fm-btn__dot"><?php echo $i( 'arrow' ); // phpcs:ignore ?></span></a></div>
		</div>
	</section>

	<!-- ================= 4. OFFER B: inspection bento ================= -->
	<section class="fm-section fm-offer" id="inspection" aria-labelledby="fm-offer-title">
		<div class="fm-wrap">
			<div class="fm-head fm-head--split">
				<div>
					<p class="fm-kicker fm-reveal">Free on your first order</p>
					<h2 id="fm-offer-title" class="fm-h2 fm-reveal">See it before your customers do.</h2>
				</div>
				<p class="fm-reveal">Before anything ships, we photograph your products so you know exactly what your customers will receive.</p>
			</div>

			<div class="fm-bento fm-bento--offer">
				<div class="fm-bcard fm-bcard--report fm-reveal">
					<div class="fm-report">
						<div class="fm-report__head">
							<div><small>PRE-SHIPMENT PHOTO INSPECTION</small><b>Report QC-7731</b></div>
							<span>PO 2048 · 500 units</span>
						</div>
						<div class="fm-report__photos">
							<?php foreach ( $qc_imgs as $k => $src ) : ?>
								<figure class="fm-polaroid" style="--k:<?php echo (int) ( $k + 1 ); ?>;--p:<?php echo (int) ( 15 + $k * 14 ); ?>%"><span class="fm-polaroid__img"><img src="<?php echo esc_url( $src ); ?>" alt="" loading="lazy" decoding="async" width="240" height="240"></span><figcaption>IMG_00<?php echo (int) ( $k + 1 ); ?></figcaption></figure>
							<?php endforeach; ?>
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
				</div>

				<div class="fm-bcard fm-bcard--photos fm-reveal">
					<span class="fm-bcard__ic"><?php echo $i( 'camera' ); // phpcs:ignore ?></span>
					<h3>Real photos, not renders</h3>
					<p>Shot on our warehouse floor, of the exact batch that will ship.</p>
					<div class="fm-bcard__stack" aria-hidden="true">
						<?php foreach ( array_slice( $qc_imgs, 0, 3 ) as $k => $src ) : ?><img src="<?php echo esc_url( $src ); ?>" alt="" loading="lazy" decoding="async" width="160" height="160" style="--k:<?php echo (int) $k; ?>"><?php endforeach; ?>
					</div>
				</div>

				<div class="fm-bcard fm-bcard--approve fm-reveal">
					<span class="fm-bcard__ic"><?php echo $i( 'shield' ); // phpcs:ignore ?></span>
					<h3>You approve before dispatch</h3>
					<div class="fm-approve" aria-hidden="true">
						<p><b>QC-7731 is ready</b><small>500 units · 4 checks passed</small></p>
						<span class="fm-approve__yes"><?php echo $i( 'check' ); // phpcs:ignore ?> Approve</span>
						<span class="fm-approve__no">Request a fix</span>
					</div>
				</div>

				<div class="fm-bcard fm-bcard--fix fm-reveal">
					<div>
						<span class="fm-bcard__ic"><?php echo $i( 'bolt' ); // phpcs:ignore ?></span>
						<h3>Problems fixed in China, not after refunds</h3>
						<p>If anything is off, you see it with photos and we fix it before it leaves the building.</p>
					</div>
					<ol class="fm-fixline" aria-label="Example of a fix">
						<li class="is-bad"><small>Found</small><b>Label misprint on 40 units</b></li>
						<li><small>Fixed</small><b>Re-labelled in <?php echo esc_html( $city ); ?></b></li>
						<li class="is-ok"><small>Shipped</small><b>Approved, 0 returns</b></li>
					</ol>
				</div>

				<div class="fm-bcard fm-bcard--cta fm-reveal">
					<p class="fm-bcard__big">Free</p>
					<p>On your first order, with your landed-cost quote.</p>
					<a class="fm-btn fm-btn--white" href="#quote" data-fm-cta="offer">Claim it <span class="fm-btn__dot"><?php echo $i( 'arrow' ); // phpcs:ignore ?></span></a>
				</div>
			</div>
		</div>
	</section>

	<!-- ================= 5. PROOF: bento ================= -->
	<section class="fm-section fm-proof" id="proof" aria-labelledby="fm-proof-title">
		<div class="fm-wrap">
			<div class="fm-head fm-head--split">
				<div>
					<p class="fm-kicker fm-reveal">Proof</p>
					<h2 id="fm-proof-title" class="fm-h2 fm-reveal">Faster from factory to front door.</h2>
				</div>
				<p class="fm-reveal">Numbers from our own operation, and the delivery promise we put in writing.</p>
			</div>

			<div class="fm-bento fm-bento--proof">
				<?php foreach ( $stats as $n => $s ) : ?>
					<div class="fm-bcard fm-stat fm-reveal">
						<dl>
							<dd><span data-fm-count="<?php echo esc_attr( $s[0] ); ?>" data-fm-dec="<?php echo false !== strpos( $s[0], '.' ) ? 1 : 0; ?>"><?php echo esc_html( $s[0] ); ?></span><em><?php echo esc_html( $s[1] ); ?></em></dd>
							<dt><?php echo esc_html( $s[2] ); ?></dt>
						</dl>
						<?php if ( 0 === $n ) : ?>
							<div class="fm-stat__bars" aria-hidden="true"><p><span>Industry average</span><i style="--w:40%"></i></p><p class="is-us"><span>Fulfillmen</span><i style="--w:100%"></i></p></div>
						<?php elseif ( 1 === $n ) : ?>
							<div class="fm-stat__bars" aria-hidden="true"><p><span>Typical carrier rate</span><i style="--w:100%"></i></p><p class="is-us"><span>Fulfillmen</span><i style="--w:80%"></i></p></div>
						<?php else : ?>
							<div class="fm-stat__ticks" aria-hidden="true"><?php echo str_repeat( '<i></i>', 20 ); // phpcs:ignore ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>

				<div class="fm-bcard fm-tracking fm-reveal">
					<div class="fm-tracking__head">
						<div><small>TRACKING</small><b>FM 2048 1193 CN</b></div>
						<span class="fm-st">Delivered</span>
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

				<div class="fm-bcard fm-vs fm-reveal">
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
							<div class="fm-test__stars" aria-label="5 out of 5"><?php echo str_repeat( $i( 'star' ), 5 ); // phpcs:ignore ?></div>
							<p class="fm-test__result"><?php echo esc_html( $t[2] ); ?></p>
							<blockquote>“<?php echo esc_html( $t[3] ); ?>”</blockquote>
							<figcaption><b><?php echo esc_html( $t[0] ); ?></b><span><?php echo esc_html( $t[1] ); ?></span></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- ================= 6. QUOTE FORM: split card ================= -->
	<section class="fm-section fm-quote" id="quote" aria-labelledby="fm-quote-title">
		<div class="fm-wrap">
			<div class="fm-quote__card">
				<div class="fm-quote__side">
					<svg class="fm-quote__map" data-fm-map viewBox="0 0 <?php echo (int) $map['w']; ?> <?php echo (int) $map['h']; ?>" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
						<use href="#fm-land" class="fm-map__land"/>
						<g class="fm-map__routes"><?php foreach ( $routes as $k => $d ) : ?><path id="fmq-<?php echo esc_attr( $k ); ?>" d="<?php echo esc_attr( $d ); ?>"/><?php endforeach; ?></g>
						<g class="fm-map__parcels"><?php foreach ( $routes as $k => $d ) : ?><circle r="4" data-route="fmq-<?php echo esc_attr( $k ); ?>"/><?php endforeach; ?></g>
						<g transform="translate(<?php echo esc_attr( $hub['x'] . ' ' . $hub['y'] ); ?>)" class="fm-map__hub"><circle class="fm-world__ping" r="8"/><circle r="6"/></g>
					</svg>
					<div class="fm-quote__copy">
						<p class="fm-kicker fm-kicker--light fm-reveal">Free landed-cost quote</p>
						<h2 id="fm-quote-title" class="fm-h2 fm-reveal">Know your real cost per unit in 24&nbsp;hours.</h2>
						<p class="fm-lead fm-reveal">Send your product or store link. We reply within 24 hours with full pricing, line by line.</p>

						<div class="fm-receipt fm-reveal" aria-label="What your quote includes">
							<div class="fm-receipt__h"><b>Your quote includes</b><span><?php echo $i( 'clock' ); // phpcs:ignore ?> Reply ≤ 24 h</span></div>
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
				</div>

				<div class="fm-formdoc">
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
		</div>
	</section>

	<!-- ================= 7. FAQ ================= -->
	<section class="fm-section fm-faq" id="faq" aria-labelledby="fm-faq-title">
		<div class="fm-wrap fm-faq__grid">
			<div class="fm-faq__side">
				<p class="fm-kicker fm-reveal">FAQ</p>
				<h2 id="fm-faq-title" class="fm-h2 fm-reveal">Questions sellers ask before they switch.</h2>
				<p class="fm-reveal">Something else? <a href="#quote">Ask in the quote form</a> and a real person replies within 24 hours.</p>
			</div>
			<div class="fm-acc">
				<?php foreach ( $faqs as $n => $f ) : ?>
					<details class="fm-acc__item fm-reveal">
						<summary><span class="fm-acc__no"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span><span class="fm-acc__q"><?php echo esc_html( $f[0] ); ?></span><span class="fm-acc__pm" aria-hidden="true"></span></summary>
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

	<!-- ================= 8. FINAL CTA ================= -->
	<section class="fm-final" aria-labelledby="fm-final-title">
		<div class="fm-wrap">
			<div class="fm-final__card fm-reveal">
				<?php if ( $o['img_own_brand'] ) : ?>
					<img class="fm-final__img" src="<?php echo esc_url( $o['img_own_brand'] ); ?>" alt="" loading="lazy" decoding="async" width="1400" height="820">
				<?php endif; ?>
				<div class="fm-final__in">
					<h2 id="fm-final-title" class="fm-h2 fm-h2--xl">Find out what it really costs to sell from China.</h2>
					<p class="fm-lead">Send a product link. Get an itemised landed-cost quote within 24 hours.</p>
					<div class="fm-ctas">
						<a class="fm-btn fm-btn--white fm-magnetic" href="#quote" data-fm-cta="final">Get my free landed-cost quote <span class="fm-btn__dot"><?php echo $i( 'arrow' ); // phpcs:ignore ?></span></a>
					</div>
					<ul class="fm-ticks fm-ticks--row">
						<li><?php echo $i( 'check' ); // phpcs:ignore ?> Free photo inspection on first order</li>
						<li><?php echo $i( 'check' ); // phpcs:ignore ?> No minimums</li>
						<li><?php echo $i( 'check' ); // phpcs:ignore ?> No lock-in</li>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<div class="fm-sticky" data-fm-sticky aria-hidden="true">
		<a class="fm-btn fm-btn--primary fm-btn--block" href="#quote" data-fm-cta="sticky" tabindex="-1">Get my free landed-cost quote</a>
	</div>
</div>
