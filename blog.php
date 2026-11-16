<?php
declare(strict_types=1);

require_once __DIR__ . '/portal/includes/config.php';
require_once __DIR__ . '/portal/includes/functions.php';

$pdo = getDbConnection();

$currentCat = trim((string)($_GET['category'] ?? 'all'));
if ($currentCat === '') {
    $currentCat = 'all';
}
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$itemsPerPage = 6;

// Load all published articles and categories
$allStmt = $pdo->query("SELECT `id`, `title`, `slug`, `category`, `featured_image`, `excerpt`, `author`, `author_title`, `read_time`, `focus_keyword`, `tags`, `views`, `created_at` FROM `posts` WHERE `status` = 'published' ORDER BY `created_at` DESC");
$allArticles = $allStmt->fetchAll();

$catsStmt = $pdo->query("SELECT `category`, COUNT(*) as `count` FROM `posts` WHERE `status` = 'published' GROUP BY `category` ORDER BY `count` DESC");
$categories = $catsStmt->fetchAll();

if ($currentCat !== 'all') {
    $filteredArticles = array_values(array_filter($allArticles, static function(array $a) use ($currentCat): bool {
        return $a['category'] === $currentCat;
    }));
} else {
    $filteredArticles = $allArticles;
}

$totalFiltered = count($filteredArticles);
$totalPages = max(1, (int)ceil($totalFiltered / $itemsPerPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $itemsPerPage;
$pageArticles = array_slice($filteredArticles, $offset, $itemsPerPage);

$canonicalUrl = 'https://rihannasignaturesafari.com/blog.html';
$metaTitle = 'East Africa Safari Journal & Planning Guides | Rihanna Signature Safari';
$metaDesc = 'Read 25 field-tested Tanzania and Kenya safari guides, pricing breakdowns, visa updates, migration calendars, packing checklists, and traveler Q&As.';
$metaKeywords = 'Tanzania Safari, Kenya Safari, Serengeti Migration, Ngorongoro Crater, Zanzibar Beach, Kilimanjaro Climb, Safari Packing, Bush Camp';

// Build ItemList JSON-LD for all published articles
$itemListElements = [];
foreach ($allArticles as $idx => $art) {
    $itemListElements[] = [
        '@type'    => 'ListItem',
        'position' => $idx + 1,
        'url'      => 'https://rihannasignaturesafari.com/journal/' . $art['slug'],
        'name'     => $art['title']
    ];
}
$schemaBlog = [
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type'       => 'Blog',
            '@id'         => 'https://rihannasignaturesafari.com/blog.html#blog',
            'url'         => $canonicalUrl,
            'name'        => 'The East Africa Safari & Adventure Journal',
            'description' => $metaDesc,
            'publisher'   => [
                '@type' => 'TravelAgency',
                'name'  => 'Rihanna Signature Safari',
                'url'   => 'https://rihannasignaturesafari.com/'
            ]
        ],
        [
            '@type'           => 'ItemList',
            'name'            => 'Safari Planning Guides and Chronicles',
            'numberOfItems'   => count($allArticles),
            'itemListElement' => $itemListElements
        ],
        [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://rihannasignaturesafari.com/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Journal', 'item' => $canonicalUrl]
            ]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?= e($metaTitle) ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="theme-color" content="#111c10">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <link rel="alternate" type="text/plain" title="LLMs.txt" href="https://rihannasignaturesafari.com/llms.txt">
    <link rel="shortcut icon" href="favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="favicon.png">

    <!-- Open Graph / Social -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Rihanna Signature Safari">
    <meta property="og:title" content="<?= e($metaTitle) ?>">
    <meta property="og:description" content="<?= e($metaDesc) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:image" content="https://rihannasignaturesafari.com/images/tanzania-serengeti-migration.jpg">
    <meta property="og:image:alt" content="Rihanna Signature Safari East Africa Journal">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@Iamnalisis">
    <meta name="twitter:title" content="<?= e($metaTitle) ?>">
    <meta name="twitter:description" content="<?= e($metaDesc) ?>">
    <meta name="twitter:image" content="https://rihannasignaturesafari.com/images/tanzania-serengeti-migration.jpg">

    <!-- Structured Data -->
    <script type="application/ld+json"><?= json_encode($schemaBlog, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,400&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,400&display=swap" rel="stylesheet"></noscript>

    <!-- Local CSS files (No Bootstrap) -->
    <link rel="stylesheet" href="css/vendor.min.css">
    <link rel="stylesheet" href="css/luxury-safari.min.css">
  </head>
  <body>
	  <nav class="navbar ftco_navbar ftco-navbar-light" id="ftco-navbar">
	    <div class="container">
	      <a class="navbar-brand" href="index.html">
	        <img src="images/logo.png" alt="Rihanna Signature Safari" class="brand-logo-img">
	      </a>
	      <button class="navbar-toggler" type="button" aria-label="Toggle navigation">
	        <i class="fa-solid fa-bars mr-1"></i> Menu
	      </button>

	      <div class="navbar-collapse" id="ftco-nav">
	        <ul class="navbar-nav ml-auto">
	          <li class="nav-item"><a href="index.html" class="nav-link">Home</a></li>
	          <li class="nav-item"><a href="about.html" class="nav-link">About</a></li>
	          <li class="nav-item nav-dropdown-item">
	            <div class="nav-link-wrap">
	              <a href="destination.html" class="nav-link">Destinations</a>
	              <button type="button" class="nav-dropdown-toggle" aria-expanded="false" aria-label="Toggle Destinations menu">
	                <i class="fa-solid fa-chevron-down"></i>
	              </button>
	            </div>
	            <div class="nav-dropdown-menu nav-mega-destinations" aria-label="Destinations submenu">
	              <div class="nav-dropdown-grid">
	                <div class="nav-dropdown-col">
	                  <div class="nav-dropdown-heading">
	                    <span class="nav-heading-tag">Tanzania</span>
	                    <span class="nav-heading-count">8 Sanctuaries</span>
	                  </div>
	                  <ul class="nav-dropdown-list">
	                    <li><a href="destination-serengeti.html"><i class="fa-solid fa-compass"></i>Serengeti National Park</a></li>
	                    <li><a href="destination-ngorongoro.html"><i class="fa-solid fa-compass"></i>Ngorongoro Crater</a></li>
	                    <li><a href="destination-kilimanjaro.html"><i class="fa-solid fa-compass"></i>Mount Kilimanjaro</a></li>
	                    <li><a href="destination-zanzibar.html"><i class="fa-solid fa-compass"></i>Zanzibar Archipelago</a></li>
	                    <li><a href="destination-tarangire.html"><i class="fa-solid fa-compass"></i>Tarangire National Park</a></li>
	                    <li><a href="destination-manyara.html"><i class="fa-solid fa-compass"></i>Lake Manyara National Park</a></li>
	                    <li><a href="destination-nyerere.html"><i class="fa-solid fa-compass"></i>Nyerere National Park (Selous)</a></li>
	                    <li><a href="destination-ruaha.html"><i class="fa-solid fa-compass"></i>Ruaha National Park</a></li>
	                  </ul>
	                  <a href="destination.html?country=Tanzania" class="nav-dropdown-sublink">All Tanzania Sanctuaries &rarr;</a>
	                </div>
	                <div class="nav-dropdown-col">
	                  <div class="nav-dropdown-heading">
	                    <span class="nav-heading-tag">Kenya</span>
	                    <span class="nav-heading-count">5 Reserves</span>
	                  </div>
	                  <ul class="nav-dropdown-list">
	                    <li><a href="destination-masai-mara.html"><i class="fa-solid fa-compass"></i>Maasai Mara National Reserve</a></li>
	                    <li><a href="destination-amboseli.html"><i class="fa-solid fa-compass"></i>Amboseli National Park</a></li>
	                    <li><a href="destination-samburu.html"><i class="fa-solid fa-compass"></i>Samburu National Reserve</a></li>
	                    <li><a href="destination-lewa.html"><i class="fa-solid fa-compass"></i>Lewa Wildlife Conservancy</a></li>
	                    <li><a href="destination-tsavo.html"><i class="fa-solid fa-compass"></i>Tsavo Wilderness</a></li>
	                  </ul>
	                  <a href="destination.html?country=Kenya" class="nav-dropdown-sublink">All Kenya Sanctuaries &rarr;</a>
	                </div>
	              </div>
	              <div class="nav-dropdown-footer">
	                <a href="destination.html" class="nav-dropdown-all-btn"><i class="fa-solid fa-earth-africa mr-2"></i>Explore All 13 East African Sanctuaries</a>
	              </div>
	            </div>
	          </li>
	          <li class="nav-item nav-dropdown-item">
	            <div class="nav-link-wrap">
	              <a href="experiences.html" class="nav-link">Experiences</a>
	              <button type="button" class="nav-dropdown-toggle" aria-expanded="false" aria-label="Toggle Experiences menu">
	                <i class="fa-solid fa-chevron-down"></i>
	              </button>
	            </div>
	            <div class="nav-dropdown-menu nav-mega-experiences" aria-label="Experiences submenu">
	              <div class="nav-dropdown-grid">
	                <div class="nav-dropdown-col">
	                  <div class="nav-dropdown-heading">
	                    <span class="nav-heading-tag">Signature Expeditions</span>
	                    <span class="nav-heading-count">Curated Journeys</span>
	                  </div>
	                  <ul class="nav-dropdown-list">
	                    <li><a href="experiences.html?category=Great Migration"><i class="fa-solid fa-paw"></i>Great Migration Safaris</a></li>
	                    <li><a href="experiences.html?category=Bush-to-Beach"><i class="fa-solid fa-umbrella-beach"></i>Bush-to-Beach &amp; Villas</a></li>
	                    <li><a href="experiences.html?category=Private Conservancies"><i class="fa-solid fa-shield-halved"></i>Private Conservancies</a></li>
	                    <li><a href="experiences.html?category=Photography"><i class="fa-solid fa-camera"></i>Wildlife Photography</a></li>
	                    <li><a href="experiences.html?category=Wilderness"><i class="fa-solid fa-tree"></i>Remote Southern Wilderness</a></li>
	                    <li><a href="experiences.html?category=Chimpanzee"><i class="fa-solid fa-binoculars"></i>Chimpanzee Trekking</a></li>
	                  </ul>
	                </div>
	                <div class="nav-dropdown-col">
	                  <div class="nav-dropdown-heading">
	                    <span class="nav-heading-tag">Curated Occasions</span>
	                    <span class="nav-heading-count">Private Styles</span>
	                  </div>
	                  <ul class="nav-dropdown-list">
	                    <li><a href="experiences.html?category=Romantic"><i class="fa-solid fa-heart"></i>Honeymoons &amp; Romance</a></li>
	                    <li><a href="experiences.html?category=Family"><i class="fa-solid fa-users"></i>Family Safari Luxury</a></li>
	                    <li><a href="experiences.html?category=Ultra-Luxe"><i class="fa-solid fa-crown"></i>Ultra-Luxe &amp; Private Islands</a></li>
	                    <li><a href="experiences.html?category=Conservation"><i class="fa-solid fa-leaf"></i>Conservation &amp; Rhinos</a></li>
	                    <li><a href="destination-kilimanjaro.html"><i class="fa-solid fa-mountain"></i>Kilimanjaro Summit Climbs</a></li>
	                  </ul>
	                </div>
	              </div>
	              <div class="nav-dropdown-footer">
	                <a href="experiences.html" class="nav-dropdown-all-btn"><i class="fa-solid fa-compass mr-2"></i>Explore All 35 Signature Safari Packages</a>
	              </div>
	            </div>
	          </li>
	          <li class="nav-item active"><a href="blog.html" class="nav-link">Journal</a></li>
	          <li class="nav-item"><a href="contact.html" class="nav-link">Contact</a></li>
	          <li class="nav-item cta"><a href="contact.html" class="nav-link">Book Now</a></li>
	        </ul>
	      </div>
	    </div>
	  </nav>
    <!-- END nav -->

    <section class="hero-wrap hero-wrap-2" style="background-image: url('images/tanzania-hero.jpg');">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
          <div class="col-md-9 ftco-animate text-center">
            <h1 class="mb-3 bread">The East Africa Safari &amp; Adventure Journal</h1>
            <p class="breadcrumbs"><span class="mr-2"><a href="index.html">Home <i class="fa-solid fa-chevron-right mx-1"></i></a></span> <span>Journal &amp; Insights <i class="fa-solid fa-chevron-right mx-1"></i></span></p>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section">
      <div class="container">
        <?php if (count($categories) > 1): ?>
        <div class="journal-filter-pills-wrap mb-4 pb-2" id="journal-category-nav">
          <div class="d-flex align-items-center flex-wrap" style="gap: 8px;" id="journal-category-buttons">
            <a href="blog.html" class="btn btn-sm <?= $currentCat === 'all' ? 'btn-outline-warning active' : 'btn-outline-secondary' ?> mr-2 mb-2" data-cat="all" style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-radius: 20px; padding: 6px 14px;">All Stories (<?= count($allArticles) ?>)</a>
            <?php foreach ($categories as $c): ?>
              <a href="blog.html?category=<?= urlencode((string)$c['category']) ?>" class="btn btn-sm <?= $currentCat === $c['category'] ? 'btn-outline-warning active' : 'btn-outline-secondary' ?> mr-2 mb-2" data-cat="<?= e((string)$c['category']) ?>" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; border-radius: 20px; padding: 6px 14px;"><?= e((string)$c['category']) ?> (<?= (int)$c['count'] ?>)</a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="row d-flex" id="journal-articles-grid">
          <?php foreach ($pageArticles as $art):
            $ts = strtotime((string)$art['created_at']) ?: time();
            $day = date('d', $ts);
            $yr  = date('Y', $ts);
            $mos = date('F', $ts);
            $link = 'journal/' . rawurlencode((string)$art['slug']);
          ?>
          <div class="col-md-4 d-flex mb-4">
            <div class="blog-entry justify-content-end w-100">
              <a href="<?= e($link) ?>" class="block-20" style="background-image: url('<?= e((string)$art['featured_image']) ?>'); position: relative;">
                <span style="position: absolute; top: 12px; left: 12px; background: rgba(11, 17, 11, 0.88); color: var(--safari-gold); font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.4px; padding: 4px 10px; border: 1px solid rgba(200, 150, 62, 0.4); border-radius: 2px;"><?= e((string)$art['category']) ?></span>
              </a>
              <div class="text mt-3 float-right d-block">
                <div class="d-flex align-items-center mb-4 topp">
                  <div class="one"><span class="day"><?= e($day) ?></span></div>
                  <div class="two"><span class="yr"><?= e($yr) ?></span><span class="mos"><?= e($mos) ?></span></div>
                </div>
                <h3 class="heading"><a href="<?= e($link) ?>"><?= e((string)$art['title']) ?></a></h3>
                <p style="font-size: 13.5px; color: var(--safari-text-muted); line-height: 1.6;"><?= e((string)$art['excerpt']) ?></p>
                <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top" style="font-size: 12px; color: var(--safari-text-muted);">
                  <span><i class="fa-solid fa-clock mr-1" style="color: var(--safari-gold);"></i><?= e((string)$art['read_time']) ?></span>
                  <a href="<?= e($link) ?>" style="color: var(--safari-gold); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; font-size: 11px;">Read Article &rarr;</a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="row mt-5" id="journal-pagination-wrap">
          <div class="col text-center">
            <div class="block-27" id="journal-pagination">
              <ul>
                <?php
                $catQuery = ($currentCat !== 'all') ? '&category=' . urlencode($currentCat) : '';
                if ($currentPage > 1): ?>
                  <li><a href="blog.html?page=<?= ($currentPage - 1) . $catQuery ?>" aria-label="Previous Page"><i class="fa-solid fa-chevron-left"></i></a></li>
                <?php else: ?>
                  <li class="disabled"><span><i class="fa-solid fa-chevron-left"></i></span></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <?php if ($i === $currentPage): ?>
                    <li class="active"><span><?= $i ?></span></li>
                  <?php else: ?>
                    <li><a href="blog.html?page=<?= $i . $catQuery ?>"><?= $i ?></a></li>
                  <?php endif; ?>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                  <li><a href="blog.html?page=<?= ($currentPage + 1) . $catQuery ?>" aria-label="Next Page"><i class="fa-solid fa-chevron-right"></i></a></li>
                <?php else: ?>
                  <li class="disabled"><span><i class="fa-solid fa-chevron-right"></i></span></li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <footer class="ftco-footer">
      <div class="container">
        <div class="row mb-5">
          <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
            <div class="ftco-footer-widget">
              <a href="index.html" class="footer-brand-badge">
                <img src="images/logo.png" alt="Rihanna Signature Safari" class="footer-logo-img" loading="lazy" decoding="async">
              </a>
              <div class="footer-tagline">Rihanna Signature Safari</div>
              <p class="footer-desc">Curating ultra-luxury, private fly-in expeditions across Tanzania's most iconic wildlife ecosystems. Uncompromising hospitality, private tented reserves, and master naturalist guides.</p>

              <ul class="ftco-footer-social">
                <li><a href="https://twitter.com/Iamnalisis" target="_blank" rel="noopener" aria-label="X / Twitter"><i class="fa-brands fa-x-twitter"></i></a></li>
                <li><a href="https://www.facebook.com/redemptus.respicius" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a></li>
                <li><a href="https://www.instagram.com/nalisis_tz?igsh=Nml0eHEwdHg3a2F3" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></li>
                <li><a href="https://www.linkedin.com/in/redempotius-nalisis-976bb532a/" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a></li>
                <li><a href="https://wa.me/255676063791" target="_blank" rel="noopener" aria-label="WhatsApp Concierge"><i class="fa-brands fa-whatsapp"></i></a></li>
              </ul>
            </div>
          </div>

          <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
            <div class="ftco-footer-widget">
              <h2 class="footer-title">Sanctuaries</h2>
              <ul class="footer-links-list">
                <li><a href="destination-serengeti.html"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>Serengeti National Park</a></li>
                <li><a href="destination-ngorongoro.html"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>Ngorongoro Caldera</a></li>
                <li><a href="destination-tarangire.html"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>Tarangire &amp; Manyara</a></li>
                <li><a href="destination-zanzibar.html"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>Zanzibar Archipelago</a></li>
                <li><a href="destination-masai-mara.html"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>Kenya Maasai Mara</a></li>
                <li><a href="destination-amboseli.html"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>Amboseli &amp; Tsavo</a></li>
              </ul>
            </div>
          </div>

          <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
            <div class="ftco-footer-widget">
              <h2 class="footer-title">About Us</h2>
              <ul class="footer-links-list">
                <li><a href="about.html"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>About Rihanna Signature Safari</a></li>
                <li><a href="founder.html"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>The Founder</a></li>
                <li><a href="experiences.html"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>Signature Safaris</a></li>
                <li><a href="destination.html"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>Wilderness Sanctuaries</a></li>
                <li><a href="about.html#founder-section"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>Conservation Ethos</a></li>
                <li><a href="contact.html"><i class="fa-solid fa-chevron-right footer-link-arrow"></i>Private Consultation</a></li>
              </ul>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
            <div class="ftco-footer-widget">
              <h2 class="footer-title">Contact Us</h2>
              <ul class="footer-contact-list">
                <li class="footer-contact-item">
                  <div class="footer-contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                  <div class="footer-contact-text">
                    <span class="footer-contact-label">Sanctuary HQ</span>
                    <span class="footer-contact-val">Masaki Peninsula, Dar es Salaam, Tanzania</span>
                  </div>
                </li>
                <li class="footer-contact-item">
                  <div class="footer-contact-icon"><i class="fa-solid fa-phone"></i></div>
                  <div class="footer-contact-text">
                    <span class="footer-contact-label">Direct Expedition Line</span>
                    <a href="tel:+255676063791" class="footer-contact-val footer-contact-link">+255 (0) 676 063 791</a>
                  </div>
                </li>
                <li class="footer-contact-item">
                  <div class="footer-contact-icon"><i class="fa-solid fa-envelope"></i></div>
                  <div class="footer-contact-text">
                    <span class="footer-contact-label">Direct Safari Inquiry</span>
                    <a href="mailto:info@rihannasignaturesafari.com" class="footer-contact-val footer-contact-link">info@rihannasignaturesafari.com</a>
                  </div>
                </li>
                <li class="footer-contact-item">
                  <div class="footer-contact-icon"><i class="fa-solid fa-clock"></i></div>
                  <div class="footer-contact-text">
                    <span class="footer-contact-label">Operational Hours</span>
                    <span class="footer-contact-val">24/7 Dedicated In-Field Dispatch &amp; Support</span>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="footer-bottom">
          <p class="footer-bottom-copy">Copyright &copy;<script>document.write(new Date().getFullYear());</script> Rihanna Signature Safari Ltd. All rights reserved. Ultra-Luxury Tanzania Wildlife Expeditions.</p>
          <ul class="footer-legal-links">
            <li><a href="about.html">About Us</a></li>
            <li><a href="founder.html">The Founder</a></li>
            <li><a href="terms.html">Terms &amp; Conditions</a></li>
            <li><a href="privacy.html">Privacy Policy</a></li>
            <li><a href="cookies.html">Cookie Policy</a></li>
            <li><a href="contact.html">Contact Us</a></li>
          </ul>
        </div>
      </div>
    </footer>

  <div id="ftco-loader" class="fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#c8963e"/></svg></div>

  <script src="js/vendor.min.js" defer></script>
  <script src="js/main.js" defer></script>
  </body>
</html>
