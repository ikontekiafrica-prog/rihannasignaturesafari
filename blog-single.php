<?php
declare(strict_types=1);

require_once __DIR__ . '/portal/includes/config.php';
require_once __DIR__ . '/portal/includes/functions.php';

$pdo = getDbConnection();
$slug = trim((string)($_GET['slug'] ?? ''));

$article = null;
if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT * FROM `posts` WHERE `slug` = ? AND `status` = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
    if (!$article) {
        http_response_code(404);
    }
}

if (!$article) {
    $fallbackStmt = $pdo->query("SELECT * FROM `posts` WHERE `status` = 'published' ORDER BY `created_at` DESC LIMIT 1");
    $article = $fallbackStmt->fetch();
}

if (!$article) {
    header('Location: /blog.html');
    exit;
}

// Increment view counter cleanly
$pdo->prepare("UPDATE `posts` SET `views` = `views` + 1 WHERE `id` = ?")->execute([(int)$article['id']]);

// Fetch 4 related published chronicles
$relatedStmt = $pdo->prepare("SELECT `id`, `title`, `slug`, `category`, `featured_image`, `read_time`, `created_at` FROM `posts` WHERE `status` = 'published' AND `id` != ? ORDER BY `created_at` DESC LIMIT 4");
$relatedStmt->execute([(int)$article['id']]);
$relatedArticles = $relatedStmt->fetchAll();

// Build clean keywords (each max 3 words)
$keywordList = [];
if (!empty($article['focus_keyword'])) {
    $keywordList[] = trim((string)$article['focus_keyword']);
}
$tagsArray = [];
if (!empty($article['tags'])) {
    foreach (explode(',', (string)$article['tags']) as $tag) {
        $t = trim($tag);
        if ($t !== '') {
            $tagsArray[] = $t;
            if (!in_array($t, $keywordList, true)) {
                $keywordList[] = $t;
            }
        }
    }
}
$metaKeywords = implode(', ', $keywordList);

$canonicalUrl = 'https://rihannasignaturesafari.com/journal/' . rawurlencode((string)$article['slug']);
$imgPath = ltrim((string)$article['featured_image'], '/');
$ogImage = str_starts_with($imgPath, 'http') ? $imgPath : ('https://rihannasignaturesafari.com/' . $imgPath);

$pubIso = date('c', strtotime((string)$article['created_at']) ?: time());
$modIso = date('c', strtotime((string)($article['updated_at'] ?: $article['created_at'])) ?: time());
$formattedDate = date('F j, Y', strtotime((string)$article['created_at']) ?: time());
$wordCount = str_word_count(strip_tags((string)$article['content']));

$schemaArticle = [
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type'            => 'BlogPosting',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => $canonicalUrl
            ],
            'headline'      => (string)$article['title'],
            'description'   => (string)$article['excerpt'],
            'keywords'      => $metaKeywords,
            'articleSection'=> (string)$article['category'],
            'wordCount'     => $wordCount,
            'image'         => [$ogImage],
            'datePublished' => $pubIso,
            'dateModified'  => $modIso,
            'author'        => [
                '@type' => 'Person',
                'name'  => (string)($article['author'] ?: 'Redempotius Nalisis'),
                'url'   => 'https://rihannasignaturesafari.com/founder.html'
            ],
            'publisher'     => [
                '@type' => 'TravelAgency',
                'name'  => 'Rihanna Signature Safari',
                'url'   => 'https://rihannasignaturesafari.com/',
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => 'https://rihannasignaturesafari.com/images/logo.png'
                ]
            ]
        ],
        [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://rihannasignaturesafari.com/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Journal', 'item' => 'https://rihannasignaturesafari.com/blog.html'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => (string)$article['title'], 'item' => $canonicalUrl]
            ]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <base href="/">
    <title><?= e((string)$article['title']) ?> | Rihanna Signature Safari</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?= e((string)$article['excerpt']) ?>">
    <?php if ($metaKeywords !== ''): ?>
    <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <?php endif; ?>
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="<?= e((string)($article['author'] ?: 'Redempotius Nalisis')) ?>">
    <meta name="theme-color" content="#111c10">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <link rel="alternate" type="text/plain" title="LLMs.txt" href="https://rihannasignaturesafari.com/llms.txt">
    <link rel="shortcut icon" href="favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="favicon.png">

    <!-- Open Graph / Social -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="Rihanna Signature Safari">
    <meta property="og:title" content="<?= e((string)$article['title']) ?>">
    <meta property="og:description" content="<?= e((string)$article['excerpt']) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:image:alt" content="<?= e((string)$article['title']) ?>">
    <meta property="article:published_time" content="<?= e($pubIso) ?>">
    <meta property="article:modified_time" content="<?= e($modIso) ?>">
    <meta property="article:section" content="<?= e((string)$article['category']) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@Iamnalisis">
    <meta name="twitter:title" content="<?= e((string)$article['title']) ?>">
    <meta name="twitter:description" content="<?= e((string)$article['excerpt']) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <!-- Structured Data -->
    <script type="application/ld+json"><?= json_encode($schemaArticle, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

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

    <section class="hero-wrap hero-wrap-2" style="background-image: url('<?= e((string)$article['featured_image']) ?>');">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
          <div class="col-md-9 ftco-animate text-center">
            <h1 class="mb-3 bread" id="article-hero-title"><?= e((string)$article['title']) ?></h1>
            <p class="breadcrumbs">
              <span class="mr-2"><a href="index.html">Home <i class="fa-solid fa-chevron-right mx-1"></i></a></span>
              <span class="mr-2"><a href="blog.html">Journal <i class="fa-solid fa-chevron-right mx-1"></i></a></span>
              <span><?= e((string)$article['category']) ?></span>
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section ftco-no-pt ftco-no-pb">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 order-md-last ftco-animate py-md-5 mt-md-5" id="article-body-col">
            <div style="margin-bottom: 12px; font-size: 11.5px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: var(--safari-gold);">
              <?= e((string)$article['category']) ?> &bull; <?= e($formattedDate) ?> &bull; <?= e((string)$article['read_time']) ?>
            </div>
            <h2 class="mb-4" style="font-family: var(--font-heading); color: var(--safari-forest); font-size: 38px; line-height: 1.25;"><?= e((string)$article['title']) ?></h2>
            <div class="mb-4">
              <img src="<?= e((string)$article['featured_image']) ?>" alt="<?= e((string)$article['title']) ?>" class="img-fluid rounded" loading="lazy" decoding="async" style="border: 1px solid rgba(200, 150, 62, 0.25); width: 100%; max-height: 480px; object-fit: cover;">
            </div>

            <div class="article-rich-content" style="font-size: 16px; line-height: 1.8; color: var(--safari-text-body);">
              <?= (string)$article['content'] ?>
            </div>

            <!-- Selling Concierge CTA Box -->
            <div class="p-4 p-md-5 my-5 rounded" style="background: var(--safari-forest-dark); border: 1px solid rgba(200, 150, 62, 0.35); color: #ffffff;">
              <span style="font-size: 11px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: var(--safari-gold); display: block; margin-bottom: 8px;">Tailor-Made Safari Design</span>
              <h3 style="font-family: var(--font-heading); color: #ffffff; font-size: 26px; margin-bottom: 12px;">Plan This Expedition With Our Dar es Salaam &amp; Arusha Specialists</h3>
              <p style="color: rgba(255, 255, 255, 0.82); font-size: 15px; line-height: 1.7; margin-bottom: 20px;">Share your preferred travel month, party size, and comfort style. We send a custom day-by-day route with camp availability and transparent pricing within 24 hours.</p>
              <div class="d-flex flex-wrap" style="gap: 12px;">
                <a href="contact.html" class="btn btn-primary px-4 py-2">Request Custom Proposal</a>
                <a href="experiences.html" class="btn btn-outline-light px-4 py-2">Explore 35 Luxury Packages</a>
              </div>
            </div>

            <?php if (!empty($tagsArray)): ?>
            <div class="tag-widget post-tag-container mb-5 mt-4">
              <div class="tagcloud">
                <?php foreach ($tagsArray as $tag): ?>
                  <a href="blog.html" class="tag-cloud-link"><?= e($tag) ?></a>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>

            <div class="about-author d-flex align-items-center p-3 p-md-4 rounded" style="background: var(--safari-cream); border-left: 4px solid var(--safari-gold);">
              <div class="bio mr-3 mr-md-4" style="flex-shrink: 0;">
                <img src="images/logo.png" alt="Rihanna Signature Safari" class="img-fluid" style="width: 75px; height: auto; max-height: 55px; object-fit: contain;" loading="lazy" decoding="async">
              </div>
              <div class="desc">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--safari-gold); display: block; margin-bottom: 3px;">Article Author</span>
                <h4 style="font-family: var(--font-heading); color: var(--safari-forest); margin: 0 0 2px 0; font-size: 21px;"><?= e((string)($article['author'] ?: 'Redempotius Nalisis')) ?></h4>
                <span style="font-size: 13px; color: var(--safari-text-muted);"><?= e((string)($article['author_title'] ?: 'Founder & Master Safari Architect')) ?></span>
              </div>
            </div>
          </div>

          <div class="col-lg-4 sidebar ftco-animate py-md-5">
            <div class="sidebar-box pt-md-5">
              <div class="p-4 rounded text-center" style="background: var(--safari-forest-dark); border: 1px solid rgba(200, 150, 62, 0.3);">
                <img src="images/logo.png" alt="Rihanna Signature Safari" style="height: 65px; margin-bottom: 16px; background: #ffffff; padding: 6px 14px; border-radius: 2px; border: 1.5px solid rgba(200, 150, 62, 0.45); display: inline-block;" loading="lazy" decoding="async">
                <p style="color: rgba(255, 255, 255, 0.75); font-size: 14px;">Bespoke luxury safaris, Kilimanjaro treks, and Zanzibar retreats across Tanzania and Kenya.</p>
                <a href="contact.html" class="btn btn-primary btn-sm px-3 py-2" style="font-size: 11px;">Contact Our Safari Team</a>
              </div>
            </div>

            <div class="sidebar-box ftco-animate">
              <div class="categories p-4 rounded" style="background: var(--safari-cream); border: 1px solid rgba(200, 150, 62, 0.15);">
                <h3 style="font-family: var(--font-heading); color: var(--safari-forest); font-size: 20px;">Sanctuaries &amp; Safaris</h3>
                <ul class="list-unstyled mb-0">
                  <li class="py-2 border-bottom d-flex justify-content-between"><a href="experiences.html?country=Tanzania">Tanzania Wildlife Safari</a> <span class="badge badge-warning text-white">20 Packages</span></li>
                  <li class="py-2 border-bottom d-flex justify-content-between"><a href="destination-kilimanjaro.html">Kilimanjaro Mountain Trek</a> <span class="badge badge-warning text-white">Dossier</span></li>
                  <li class="py-2 border-bottom d-flex justify-content-between"><a href="destination-zanzibar.html">Zanzibar Beach Retreat</a> <span class="badge badge-warning text-white">Dossier</span></li>
                  <li class="py-2 border-bottom d-flex justify-content-between"><a href="experiences.html?country=Kenya">Kenya Wildlife Safari</a> <span class="badge badge-warning text-white">15 Packages</span></li>
                  <li class="py-2 d-flex justify-content-between"><a href="experiences.html">All 35 Luxury Safaris</a> <span class="badge badge-warning text-white">Browse All</span></li>
                </ul>
              </div>
            </div>

            <div class="sidebar-box ftco-animate" id="sidebar-recent-chronicles">
              <h3 style="font-family: var(--font-heading); color: var(--safari-forest); font-size: 20px; margin-bottom: 20px;">More Field Guides</h3>
              <?php foreach ($relatedArticles as $r):
                $rLink = 'journal/' . rawurlencode((string)$r['slug']);
                $rDate = date('M j, Y', strtotime((string)$r['created_at']) ?: time());
              ?>
              <div class="block-21 mb-4 d-flex">
                <a href="<?= e($rLink) ?>" class="blog-img mr-3 rounded" style="background-image: url(<?= e((string)$r['featured_image']) ?>); width: 80px; height: 80px; min-width: 80px; background-size: cover;"></a>
                <div class="text">
                  <h4 class="heading" style="font-size: 14px; line-height: 1.4;"><a href="<?= e($rLink) ?>" style="color: var(--safari-forest);"><?= e((string)$r['title']) ?></a></h4>
                  <div class="meta" style="font-size: 11.5px; color: var(--safari-gold);"><i class="fa-solid fa-calendar mr-1"></i> <?= e($rDate) ?></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
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
