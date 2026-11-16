<?php
/**
 * [fulfillmen_landing] — page flow follows the CRO brief §7:
 * Hero → Pick your path → How it works → Offer B → Proof → Quote form → FAQ → Final CTA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$o      = fm_lp_options();
$types  = fm_lp_lead_types();
$vols   = fm_lp_volumes();
$ph     = fm_lp_show_placeholders();
$stats  = fm_lp_rows( $o['stats'], 3 );
$tests  = fm_lp_rows( $o['testimonials'], 4 );
$media  = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $o['warehouse_media'] ) ) );
// phpcs:disable WordPress.Security.NonceVerification
$pre    = isset( $_GET['path'] ) ? sanitize_key( wp_unslash( $_GET['path'] ) ) : ( isset( $_GET['lead_type'] ) ? sanitize_key( wp_unslash( $_GET['lead_type'] ) ) : '' );
$pre    = isset( $types[ $pre ] ) ? $pre : '';
$fm_err = isset( $_GET['fm_error'] );
// phpcs:enable
$proof  = $o['delivery_proof'];
$wa     = $o['whatsapp'];

$paths = array(
	'fba'          => array( 'amazon', 'Amazon FBA sellers', 'We prep, label and ship straight to Amazon warehouses.', array( 'FNSKU labels & poly bags', 'Carton & pallet prep', 'Direct to FBA centres' ) ),
	'dropshipping' => array( 'drop', 'Dropshippers', 'Source from China and ship each order directly to your customer.', array( 'Factory sourcing', 'Auto order sync', 'Tracked to the door' ) ),
	'own_brand'    => array( 'brand', 'Brands with their own stock', 'Store your inventory with us. We pick, pack and ship every order.', array( 'Warehousing in Huizhou', 'Branded packaging', 'Pick & pack per order' ) ),
);

$steps = array(
	array( 'search', 'Source', 'We find and vet factories, negotiate your price and place the order.' ),
	array( 'camera', 'Inspect', 'Every batch is checked against your spec and photographed before it moves.' ),
	array( 'box', 'Store', 'Stock sits in our Huizhou warehouse, live in your dashboard.' ),
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

	<!-- 1. HERO -->
	<section class="fm-hero" aria-labelledby="fm-hero-title">
		<div class="fm-hero__bg" aria-hidden="true">
			<span class="fm-blob fm-blob--a"></span><span class="fm-blob fm-blob--b"></span>
			<span class="fm-grid"></span>
		</div>
		<div class="fm-wrap fm-hero__in">
			<div class="fm-hero__copy">
				<p class="fm-eyebrow fm-rise" style="--d:0"><span class="fm-dot"></span> Your China team: sourcing → doorstep</p>
				<h1 id="fm-hero-title" class="fm-hero__title fm-rise" style="--d:1">
					Source, store and ship from China, with <span class="fm-hl">one team</span>, <span class="fm-hl">one dashboard</span> and <span class="fm-hl fm-hl--u">no surprise costs.</span>
				</h1>
				<p class="fm-hero__sub fm-rise" style="--d:2">Sourcing, quality checks, warehousing, Amazon FBA prep, dropshipping and global shipping, all handled by Fulfillmen's own China-based team.</p>
				<div class="fm-hero__ctas fm-rise" style="--d:3">
					<a class="fm-btn fm-btn--lg fm-magnetic" href="#quote" data-fm-cta="hero">Get my free landed-cost quote <?php echo fm_lp_icon( 'arrow' ); // phpcs:ignore ?></a>
					<a class="fm-link" href="#how">See how it works</a>
				</div>
				<p class="fm-micro fm-rise" style="--d:4"><?php echo fm_lp_icon( 'clock' ); // phpcs:ignore ?> Itemised quote within 24 hours. No commitment.</p>
			</div>

			<div class="fm-hero__visual fm-rise" style="--d:2" aria-hidden="true">
				<div class="fm-map">
					<svg viewBox="0 0 560 420" class="fm-map__svg" role="presentation">
						<defs>
							<radialGradient id="fmGlow" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="#00A89C" stop-opacity=".55"/><stop offset="1" stop-color="#00A89C" stop-opacity="0"/></radialGradient>
							<linearGradient id="fmArc" x1="0" x2="1"><stop offset="0" stop-color="#00A89C"/><stop offset="1" stop-color="#7FE3DA"/></linearGradient>
						</defs>
						<circle cx="300" cy="230" r="150" fill="url(#fmGlow)" opacity=".35"/>
						<g class="fm-map__routes" fill="none" stroke="url(#fmArc)" stroke-width="2" stroke-linecap="round">
							<path id="fmR1" d="M300 230 C 230 120, 140 90, 70 120"/>
							<path id="fmR2" d="M300 230 C 260 120, 220 70, 190 60"/>
							<path id="fmR3" d="M300 230 C 380 120, 450 90, 500 110"/>
							<path id="fmR4" d="M300 230 C 400 260, 460 320, 480 350"/>
							<path id="fmR5" d="M300 230 C 220 290, 140 320, 90 330"/>
						</g>
						<g class="fm-map__parcels">
							<rect class="fm-parcel" data-route="fmR1" width="12" height="12" rx="3"/>
							<rect class="fm-parcel" data-route="fmR2" width="12" height="12" rx="3"/>
							<rect class="fm-parcel" data-route="fmR3" width="12" height="12" rx="3"/>
							<rect class="fm-parcel" data-route="fmR4" width="12" height="12" rx="3"/>
							<rect class="fm-parcel" data-route="fmR5" width="12" height="12" rx="3"/>
						</g>
						<g class="fm-map__nodes">
							<g transform="translate(70 120)"><circle r="6"/><text x="0" y="-14">EU</text></g>
							<g transform="translate(190 60)"><circle r="6"/><text x="0" y="-14">UK</text></g>
							<g transform="translate(500 110)"><circle r="6"/><text x="0" y="-14">USA</text></g>
							<g transform="translate(480 350)"><circle r="6"/><text x="0" y="22">AU</text></g>
							<g transform="translate(90 330)"><circle r="6"/><text x="0" y="22">UAE</text></g>
						</g>
						<g class="fm-map__origin" transform="translate(300 230)">
							<circle class="fm-pulse" r="14"/><circle class="fm-pulse fm-pulse--2" r="14"/>
							<circle r="9" class="fm-origin-dot"/>
							<text x="0" y="34">Huizhou, CN</text>
						</g>
					</svg>

					<div class="fm-float fm-float--qc">
						<span class="fm-float__ico"><?php echo fm_lp_icon( 'camera' ); // phpcs:ignore ?></span>
						<div><b>QC passed</b><small>12 photos · approved</small></div>
						<span class="fm-tick"><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?></span>
					</div>
					<div class="fm-float fm-float--cost">
						<div class="fm-float__head"><small>Landed cost / unit</small><em>Example</em></div>
						<ul>
							<li><span>Product</span><span>$3.10</span></li>
							<li><span>QC + prep</span><span>$0.20</span></li>
							<li><span>Shipping</span><span>$1.52</span></li>
						</ul>
						<div class="fm-float__total"><span>Total</span><b data-fm-count="4.82" data-fm-prefix="$" data-fm-dec="2">$4.82</b></div>
					</div>
					<div class="fm-float fm-float--track">
						<span class="fm-float__ico"><?php echo fm_lp_icon( 'plane' ); // phpcs:ignore ?></span>
						<div><b>In transit · tracked</b><small>Delivered in 7–15 days</small></div>
						<span class="fm-bar"><i></i></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Trust strip -->
		<div class="fm-wrap">
			<ul class="fm-trust fm-rise" style="--d:5" aria-label="Why sellers trust Fulfillmen">
				<li><?php echo fm_lp_icon( 'clock' ); // phpcs:ignore ?> <span><?php echo esc_html( $proof ); ?></span></li>
				<li class="fm-trust__badges"><span class="fm-badge">Amazon</span><span class="fm-badge">Shopify</span><span class="fm-badge">eBay</span></li>
				<?php if ( ! empty( $stats[2] ) ) : ?>
					<li><?php echo fm_lp_icon( 'shield' ); // phpcs:ignore ?> <span><?php echo esc_html( $stats[2][0] . $stats[2][1] . ' ' . strtolower( $stats[2][2] ) ); ?></span></li>
				<?php endif; ?>
				<li><?php echo fm_lp_icon( 'pin' ); // phpcs:ignore ?> <span>Own warehouse in <?php echo esc_html( preg_replace( '/,\s*China$/i', '', $o['address'] ) ); ?></span></li>
			</ul>
		</div>
	</section>

	<!-- 2. PICK YOUR PATH -->
	<section class="fm-section fm-paths" id="path" aria-labelledby="fm-path-title">
		<div class="fm-wrap">
			<div class="fm-head fm-reveal">
				<p class="fm-kicker">Pick your path</p>
				<h2 id="fm-path-title">How do you sell?</h2>
				<p>Choose one and we'll tailor your quote to your business model.</p>
			</div>
			<div class="fm-paths__grid">
				<?php
				foreach ( $paths as $key => $p ) :
					?>
					<a class="fm-path fm-reveal<?php echo $pre === $key ? ' is-selected' : ''; ?>" href="?path=<?php echo esc_attr( $key ); ?>#quote" data-fm-path="<?php echo esc_attr( $key ); ?>">
						<span class="fm-path__glow" aria-hidden="true"></span>
						<span class="fm-path__ico"><?php echo fm_lp_icon( $p[0] ); // phpcs:ignore ?></span>
						<span class="fm-path__check" aria-hidden="true"><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?></span>
						<h3><?php echo esc_html( $p[1] ); ?></h3>
						<p><?php echo esc_html( $p[2] ); ?></p>
						<ul>
							<?php foreach ( $p[3] as $li ) : ?>
								<li><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?> <?php echo esc_html( $li ); ?></li>
							<?php endforeach; ?>
						</ul>
						<span class="fm-path__cta">Get my quote <?php echo fm_lp_icon( 'arrow' ); // phpcs:ignore ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- 3. HOW IT WORKS -->
	<section class="fm-section fm-how" id="how" aria-labelledby="fm-how-title">
		<div class="fm-wrap">
			<div class="fm-head fm-reveal">
				<p class="fm-kicker">How it works</p>
				<h2 id="fm-how-title">Four steps. One team. Zero handoffs.</h2>
				<p>No agent here and 3PL there. The same people source, check, store and ship your products.</p>
			</div>
			<div class="fm-steps-wrap">
			<svg class="fm-steps__line" viewBox="0 0 1000 8" preserveAspectRatio="none" aria-hidden="true"><path d="M0 4 H1000"/></svg>
			<ol class="fm-steps">
				<?php foreach ( $steps as $i => $s ) : ?>
					<li class="fm-step">
						<span class="fm-step__ico"><?php echo fm_lp_icon( $s[0] ); // phpcs:ignore ?><em><?php echo (int) $i + 1; ?></em></span>
						<h3><?php echo esc_html( $s[1] ); ?></h3>
						<p><?php echo esc_html( $s[2] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			</div>
			<div class="fm-center fm-reveal"><a class="fm-btn" href="#quote" data-fm-cta="how">Get my free landed-cost quote</a></div>
		</div>
	</section>

	<!-- 4. OFFER B: FREE PHOTO INSPECTION -->
	<section class="fm-section fm-offer" aria-labelledby="fm-offer-title">
		<div class="fm-wrap fm-offer__in">
			<div class="fm-inspect fm-reveal" aria-hidden="true">
				<div class="fm-inspect__frame">
					<span class="fm-corner fm-corner--tl"></span><span class="fm-corner fm-corner--tr"></span><span class="fm-corner fm-corner--bl"></span><span class="fm-corner fm-corner--br"></span>
					<svg viewBox="0 0 200 160" class="fm-inspect__product">
						<rect x="40" y="50" width="120" height="90" rx="6" fill="#E6F6F4" stroke="#00A89C" stroke-width="2"/>
						<path d="M40 70 L100 90 L160 70" fill="none" stroke="#00A89C" stroke-width="2"/>
						<path d="M100 90 V140" stroke="#00A89C" stroke-width="2"/>
						<rect x="58" y="100" width="28" height="20" rx="3" fill="#00A89C" opacity=".25"/>
						<path d="M40 50 L70 30 H130 L160 50" fill="none" stroke="#00A89C" stroke-width="2"/>
					</svg>
					<span class="fm-inspect__scan"></span>
					<span class="fm-inspect__flash"></span>
					<span class="fm-inspect__rec">● PHOTO 07/12</span>
				</div>
				<ul class="fm-inspect__list">
					<li><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?> Colour matches sample</li>
					<li><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?> Size &amp; dimensions</li>
					<li><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?> Logo &amp; print quality</li>
					<li><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?> Packaging &amp; labels</li>
				</ul>
			</div>
			<div class="fm-offer__copy fm-reveal">
				<p class="fm-kicker fm-kicker--pill">Free on your first order</p>
				<h2 id="fm-offer-title">See it before your customers do.</h2>
				<p class="fm-lead">Before anything ships, we photograph your products so you know exactly what your customers will receive.</p>
				<ul class="fm-ticks">
					<li><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?> Real photos from our warehouse floor, not supplier renders</li>
					<li><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?> You approve before dispatch</li>
					<li><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?> Problems are fixed in China, not after refunds</li>
				</ul>
				<a class="fm-btn" href="#quote" data-fm-cta="offer">Claim it with my free quote</a>
			</div>
		</div>
	</section>

	<!-- 5. PROOF -->
	<section class="fm-section fm-proof" id="proof" aria-labelledby="fm-proof-title">
		<div class="fm-wrap">
			<div class="fm-head fm-head--light fm-reveal">
				<p class="fm-kicker">Proof, not promises</p>
				<h2 id="fm-proof-title">Faster from factory to front door</h2>
			</div>

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

			<div class="fm-delivery fm-reveal" aria-label="<?php echo esc_attr( $proof ); ?>">
				<div class="fm-delivery__head">
					<span class="fm-kicker fm-kicker--pill">B2C special lines</span>
					<p><b>7–15 days</b> door-to-door, fully tracked</p>
				</div>
				<div class="fm-track">
					<div class="fm-track__rail"><i class="fm-track__fill"></i><span class="fm-track__truck"><?php echo fm_lp_icon( 'box' ); // phpcs:ignore ?></span></div>
					<ol class="fm-track__stops">
						<li><b>Dispatched</b><small>Huizhou warehouse</small></li>
						<li><b>Line haul &amp; customs</b><small>Tracking updates at each milestone</small></li>
						<li><b>Delivered</b><small>Day 7–15</small></li>
					</ol>
				</div>
			</div>

			<?php if ( $media || $ph ) : ?>
				<div class="fm-gallery">
					<?php if ( $media ) : ?>
						<?php
						foreach ( array_slice( $media, 0, 6 ) as $m ) :
							$is_vid = (bool) preg_match( '/\.(mp4|webm)(\?|$)/i', $m );
							?>
							<figure class="fm-gallery__item fm-reveal">
								<?php if ( $is_vid ) : ?>
									<video data-fm-lazy-video muted loop playsinline preload="none" aria-label="Fulfillmen warehouse video"><source data-src="<?php echo esc_url( $m ); ?>" type="video/mp4"></video>
								<?php else : ?>
									<img src="<?php echo esc_url( $m ); ?>" alt="Inside the Fulfillmen warehouse" loading="lazy" decoding="async" width="600" height="450">
								<?php endif; ?>
							</figure>
						<?php endforeach; ?>
					<?php else : ?>
						<?php for ( $i = 0; $i < 3; $i++ ) : ?>
							<figure class="fm-gallery__item fm-placeholder fm-reveal"><span>Warehouse photo / video<br><small>Add URLs in Settings → Fulfillmen Landing (visible to admins only)</small></span></figure>
						<?php endfor; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="fm-compare fm-reveal">
				<h3>Why one team beats an agent plus a separate 3PL</h3>
				<div class="fm-compare__table" role="table" aria-label="Fulfillmen compared with an agent plus 3PL">
					<div class="fm-compare__row fm-compare__row--head" role="row"><span role="columnheader"></span><span role="columnheader">Agent + 3PL</span><span role="columnheader" class="is-us">Fulfillmen</span></div>
					<?php
					$rows = array(
						array( 'Who you deal with', '2–3 companies, separate invoices', 'One team, one invoice' ),
						array( 'Quality check', 'Often extra or skipped', 'Every batch, with photos' ),
						array( 'Stock visibility', 'Emails and spreadsheets', 'One live dashboard' ),
						array( 'Pricing', 'Costs appear at each handoff', 'Full landed cost upfront' ),
						array( 'Commitments', 'Minimums and contracts', 'No minimums, no lock-in' ),
					);
					foreach ( $rows as $r ) :
						?>
						<div class="fm-compare__row" role="row">
							<span role="rowheader"><?php echo esc_html( $r[0] ); ?></span>
							<span role="cell" class="is-them"><?php echo fm_lp_icon( 'x' ); // phpcs:ignore ?> <?php echo esc_html( $r[1] ); ?></span>
							<span role="cell" class="is-us"><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?> <?php echo esc_html( $r[2] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $tests || $ph ) : ?>
				<div class="fm-tests">
					<?php if ( $tests ) : ?>
						<?php foreach ( array_slice( $tests, 0, 5 ) as $t ) : ?>
							<figure class="fm-test fm-reveal">
								<div class="fm-test__stars" aria-label="5 out of 5"><?php echo str_repeat( fm_lp_icon( 'star' ), 5 ); // phpcs:ignore ?></div>
								<p class="fm-test__result"><?php echo esc_html( $t[2] ); ?></p>
								<blockquote>“<?php echo esc_html( $t[3] ); ?>”</blockquote>
								<figcaption><b><?php echo esc_html( $t[0] ); ?></b><span><?php echo esc_html( $t[1] ); ?></span></figcaption>
							</figure>
						<?php endforeach; ?>
					<?php else : ?>
						<?php for ( $i = 0; $i < 3; $i++ ) : ?>
							<figure class="fm-test fm-placeholder fm-reveal"><span>Real testimonial: name, store type, result<br><small>Add in Settings → Fulfillmen Landing (visible to admins only)</small></span></figure>
						<?php endfor; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- 6. QUOTE FORM -->
	<section class="fm-section fm-quote" id="quote" aria-labelledby="fm-quote-title">
		<div class="fm-wrap fm-quote__in">
			<div class="fm-quote__aside fm-reveal">
				<p class="fm-kicker">Free landed-cost quote</p>
				<h2 id="fm-quote-title">Know your real cost per unit in 24 hours.</h2>
				<p class="fm-lead">Send your product or store link. We reply within 24 hours with full pricing, line by line.</p>
				<div class="fm-receipt" aria-label="What your quote includes">
					<div class="fm-receipt__head"><span>Your quote includes</span><span><?php echo fm_lp_icon( 'clock' ); // phpcs:ignore ?> 24h</span></div>
					<ul>
						<li><span>Product cost</span><i></i></li>
						<li><span>Quality check</span><i></i></li>
						<li><span>Prep &amp; labelling</span><i></i></li>
						<li><span>Warehousing</span><i></i></li>
						<li><span>Shipping to your customer or Amazon</span><i></i></li>
					</ul>
					<div class="fm-receipt__total"><span>Landed cost per unit</span><b>No surprises</b></div>
				</div>
				<ul class="fm-mini-trust">
					<li><?php echo fm_lp_icon( 'clock' ); // phpcs:ignore ?> <?php echo esc_html( $proof ); ?></li>
					<li><?php echo fm_lp_icon( 'camera' ); // phpcs:ignore ?> Free photo inspection on your first order</li>
					<li><?php echo fm_lp_icon( 'shield' ); // phpcs:ignore ?> No minimums, no lock-in</li>
					<li class="fm-trust__badges"><span class="fm-badge fm-badge--dark">Amazon</span><span class="fm-badge fm-badge--dark">Shopify</span><span class="fm-badge fm-badge--dark">eBay</span></li>
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
						<span data-fm-path-empty <?php echo $pre ? 'hidden' : ''; ?>>Tell us how you sell? <a href="#path">Pick your path</a> (optional)</span>
						<span data-fm-path-set <?php echo $pre ? '' : 'hidden'; ?>><?php echo fm_lp_icon( 'check' ); // phpcs:ignore ?> Quote for: <b data-fm-path-label><?php echo $pre ? esc_html( $types[ $pre ] ) : ''; ?></b> · <a href="#path">change</a></span>
					</p>

					<?php if ( $fm_err ) : ?>
						<p class="fm-form__alert" role="alert">Please check your details and try again.</p>
					<?php endif; ?>
					<p class="fm-form__alert" role="alert" data-fm-alert hidden></p>

					<div class="fm-field">
						<label for="fm-name">Your name</label>
						<input id="fm-name" name="name" type="text" autocomplete="name" required minlength="2" placeholder="Jane Smith">
						<small class="fm-err" data-err="name"></small>
					</div>
					<div class="fm-field">
						<label for="fm-email">Email</label>
						<input id="fm-email" name="email" type="email" autocomplete="email" inputmode="email" required placeholder="you@store.com">
						<small class="fm-err" data-err="email"></small>
					</div>
					<div class="fm-field">
						<label for="fm-phone">WhatsApp / phone</label>
						<input id="fm-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required placeholder="+1 555 010 2030">
						<small class="fm-err" data-err="phone"></small>
					</div>
					<div class="fm-field">
						<label for="fm-links">Store link or product link(s)</label>
						<textarea id="fm-links" name="links" rows="2" required placeholder="yourstore.com or 1688 / Alibaba / Amazon product links. Add destination countries if you like."></textarea>
						<small class="fm-err" data-err="links"></small>
					</div>
					<div class="fm-field">
						<label for="fm-volume">Monthly order volume</label>
						<select id="fm-volume" name="volume" required>
							<option value="">Select…</option>
							<?php foreach ( $vols as $k => $label ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<small class="fm-err" data-err="volume"></small>
					</div>

					<button class="fm-btn fm-btn--lg fm-btn--block fm-magnetic" type="submit" data-fm-submit>
						<span class="fm-btn__label">Get my free quote</span>
						<span class="fm-btn__spin" aria-hidden="true"></span>
					</button>
					<p class="fm-form__under">Reply within 24 hours. No commitment.</p>
					<p class="fm-form__legal">We'll only use your details to prepare your quote. <a href="<?php echo esc_url( $o['privacy_url'] ); ?>">Privacy policy</a></p>
					<?php if ( $wa ) : ?>
						<p class="fm-form__wa"><a href="https://wa.me/<?php echo esc_attr( $wa ); ?>" target="_blank" rel="noopener" data-fm-cta="whatsapp"><?php echo fm_lp_icon( 'chat' ); // phpcs:ignore ?> Prefer WhatsApp? Message our team</a></p>
					<?php endif; ?>
				</form>
			</div>
		</div>
	</section>

	<!-- 7. FAQ -->
	<section class="fm-section fm-faq" id="faq" aria-labelledby="fm-faq-title">
		<div class="fm-wrap fm-faq__in">
			<div class="fm-head fm-head--left fm-reveal">
				<p class="fm-kicker">FAQ</p>
				<h2 id="fm-faq-title">Questions sellers ask before they switch</h2>
				<p>Still unsure? <a href="#quote">Ask us in the quote form</a> and a real person replies within 24 hours.</p>
			</div>
			<div class="fm-acc">
				<?php foreach ( $faqs as $i => $f ) : ?>
					<details class="fm-acc__item fm-reveal" <?php echo 0 === $i ? 'open' : ''; ?>>
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

	<!-- 8. FINAL CTA -->
	<section class="fm-final" aria-labelledby="fm-final-title">
		<div class="fm-wrap fm-final__in fm-reveal">
			<h2 id="fm-final-title">Find out what it really costs to sell from China.</h2>
			<p>Send a product link. Get an itemised landed-cost quote within 24 hours.</p>
			<a class="fm-btn fm-btn--lg fm-btn--light fm-magnetic" href="#quote" data-fm-cta="final">Get my free landed-cost quote <?php echo fm_lp_icon( 'arrow' ); // phpcs:ignore ?></a>
			<p class="fm-micro">Free photo inspection on your first order · No minimums · No lock-in</p>
		</div>
	</section>

	<footer class="fm-foot">
		<div class="fm-wrap fm-foot__in">
			<p><?php echo fm_lp_icon( 'pin' ); // phpcs:ignore ?> Fulfillmen · <?php echo esc_html( $o['address'] ); ?></p>
			<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Fulfillmen. All rights reserved. · <a href="<?php echo esc_url( $o['privacy_url'] ); ?>">Privacy</a></p>
		</div>
	</footer>

	<!-- Sticky mobile CTA (brief §8) -->
	<div class="fm-sticky" data-fm-sticky aria-hidden="true">
		<a class="fm-btn fm-btn--block" href="#quote" data-fm-cta="sticky" tabindex="-1">Get my free landed-cost quote</a>
	</div>
</div>
