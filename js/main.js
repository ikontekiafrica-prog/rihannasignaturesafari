 AOS.init({
 	duration: 400,
 	easing: 'ease-out',
 	once: true
 });

(function($) {

	"use strict";

	var isMobile = {
		Android: function() {
			return navigator.userAgent.match(/Android/i);
		},
			BlackBerry: function() {
			return navigator.userAgent.match(/BlackBerry/i);
		},
			iOS: function() {
			return navigator.userAgent.match(/iPhone|iPad|iPod/i);
		},
			Opera: function() {
			return navigator.userAgent.match(/Opera Mini/i);
		},
			Windows: function() {
			return navigator.userAgent.match(/IEMobile/i);
		},
			any: function() {
			return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
		}
	};


	$(window).stellar({
    responsive: true,
    parallaxBackgrounds: true,
    parallaxElements: true,
    horizontalScrolling: false,
    hideDistantElements: false,
    scrollProperty: 'scroll'
  });


	var fullHeight = function() {
		$('.hero-wrap:not(.hero-wrap-2).js-fullheight').css('height', $(window).height());
		$('.hero-wrap:not(.hero-wrap-2) .slider-text.js-fullheight').css('height', $(window).height());
		$(window).resize(function(){
			$('.hero-wrap:not(.hero-wrap-2).js-fullheight').css('height', $(window).height());
			$('.hero-wrap:not(.hero-wrap-2) .slider-text.js-fullheight').css('height', $(window).height());
		});
	};
	fullHeight();

	// loader
	var loader = function() {
		setTimeout(function() { 
			if($('#ftco-loader').length > 0) {
				$('#ftco-loader').removeClass('show');
			}
		}, 1);
	};
	loader();

	// Scrollax
   $.Scrollax();

	var carousel = function() {
		$('.carousel-testimony').owlCarousel({
			center: true,
			loop: true,
			items:1,
			margin: 30,
			stagePadding: 0,
			nav: false,
			navText: ['<span class="ion-ios-arrow-back">', '<span class="ion-ios-arrow-forward">'],
			responsive:{
				0:{
					items: 1
				},
				600:{
					items: 2
				},
				1000:{
					items: 3
				}
			}
		});

	};
	carousel();

	$('nav .dropdown').hover(function(){
		var $this = $(this);
		// 	 timer;
		// clearTimeout(timer);
		$this.addClass('show');
		$this.find('> a').attr('aria-expanded', true);
		// $this.find('.dropdown-menu').addClass('animated-fast fadeInUp show');
		$this.find('.dropdown-menu').addClass('show');
	}, function(){
		var $this = $(this);
			// timer;
		// timer = setTimeout(function(){
			$this.removeClass('show');
			$this.find('> a').attr('aria-expanded', false);
			// $this.find('.dropdown-menu').removeClass('animated-fast fadeInUp show');
			$this.find('.dropdown-menu').removeClass('show');
		// }, 100);
	});


	$('#dropdown04').on('show.bs.dropdown', function () {
	  console.log('show');
	});

	// scroll
	var scrollWindow = function() {
		$(window).scroll(function(){
			var $w = $(this),
					st = $w.scrollTop(),
					navbar = $('.ftco_navbar'),
					sd = $('.js-scroll-wrap');

			if (st > 150) {
				if ( !navbar.hasClass('scrolled') ) {
					navbar.addClass('scrolled');	
				}
			} 
			if (st < 150) {
				if ( navbar.hasClass('scrolled') ) {
					navbar.removeClass('scrolled sleep');
				}
			} 
			if ( st > 350 ) {
				if ( !navbar.hasClass('awake') ) {
					navbar.addClass('awake');	
				}
				
				if(sd.length > 0) {
					sd.addClass('sleep');
				}
			}
			if ( st < 350 ) {
				if ( navbar.hasClass('awake') ) {
					navbar.removeClass('awake');
					navbar.addClass('sleep');
				}
				if(sd.length > 0) {
					sd.removeClass('sleep');
				}
			}
		});
	};
	scrollWindow();

	var isMobile = {
		Android: function() {
			return navigator.userAgent.match(/Android/i);
		},
			BlackBerry: function() {
			return navigator.userAgent.match(/BlackBerry/i);
		},
			iOS: function() {
			return navigator.userAgent.match(/iPhone|iPad|iPod/i);
		},
			Opera: function() {
			return navigator.userAgent.match(/Opera Mini/i);
		},
			Windows: function() {
			return navigator.userAgent.match(/IEMobile/i);
		},
			any: function() {
			return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
		}
	};

	var counter = function() {
		
		$('#section-counter, .hero-wrap, .ftco-counter').waypoint( function( direction ) {

			if( direction === 'down' && !$(this.element).hasClass('ftco-animated') ) {

				var comma_separator_number_step = $.animateNumber.numberStepFactories.separator(',')
				$('.number').each(function(){
					var $this = $(this),
						num = $this.data('number');
					$this.animateNumber(
					  {
					    number: num,
					    numberStep: comma_separator_number_step
					  }, 1800
					);
				});
				
			}

		} , { offset: '95%' } );

	}
	counter();


	var contentWayPoint = function() {
		var i = 0;
		$('.ftco-animate').waypoint( function( direction ) {

			if( direction === 'down' && !$(this.element).hasClass('ftco-animated') ) {
				
				i++;

				$(this.element).addClass('item-animate');
				setTimeout(function(){

					$('body .ftco-animate.item-animate').each(function(k){
						var el = $(this);
						setTimeout( function () {
							var effect = el.data('animate-effect');
							if ( effect === 'fadeIn') {
								el.addClass('fadeIn ftco-animated');
							} else if ( effect === 'fadeInLeft') {
								el.addClass('fadeInLeft ftco-animated');
							} else if ( effect === 'fadeInRight') {
								el.addClass('fadeInRight ftco-animated');
							} else {
								el.addClass('fadeInUp ftco-animated');
							}
							el.removeClass('item-animate');
						},  k * 30, 'easeInOutExpo' );
					});
					
				}, 0);
				
			}

		} , { offset: '95%' } );
	};
	contentWayPoint();


	// navigation
	var OnePageNav = function() {
		$(".smoothscroll[href^='#'], #ftco-nav ul li a[href^='#']").on('click', function(e) {
		 	e.preventDefault();

		 	var hash = this.hash,
		 			navToggler = $('.navbar-toggler');
		 	$('html, body').animate({
		    scrollTop: $(hash).offset().top
		  }, 700, 'easeInOutExpo', function(){
		    window.location.hash = hash;
		  });


		  if ( navToggler.is(':visible') ) {
		  	navToggler.click();
		  }
		});
		$('body').on('activate.bs.scrollspy', function () {})
	};
	OnePageNav();


	// magnific popup
	$('.image-popup').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
    closeBtnInside: false,
    fixedContentPos: true,
    mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
     gallery: {
      enabled: true,
      navigateByImgClick: true,
      preload: [0,1] // Will preload 0 - before current, and 1 after the current image
    },
    image: {
      verticalFit: true
    },
    zoom: {
      enabled: true,
      duration: 300 // don't foget to change the duration also in CSS
    }
  });

  $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
    disableOn: 700,
    type: 'iframe',
    mainClass: 'mfp-fade',
    removalDelay: 160,
    preloader: false,

    fixedContentPos: false
  });


  // Mobile navbar toggle without Bootstrap dependency
  $('.navbar-toggler').on('click', function(e) {
    e.preventDefault();
    $('#ftco-nav').toggleClass('show');
  });

  // Close mobile nav when clicking a main nav link
  $('#ftco-nav .nav-link').on('click', function() {
    if ($(window).width() < 992) {
      $('#ftco-nav').removeClass('show');
    }
  });

  // Nav dropdown side toggle buttons (Destinations and Experiences)
  document.addEventListener('click', function(e) {
    const toggleBtn = e.target.closest('.nav-dropdown-toggle');
    if (toggleBtn) {
      e.preventDefault();
      e.stopPropagation();
      const parent = toggleBtn.closest('.nav-dropdown-item');
      if (!parent) return;
      const isOpen = parent.classList.contains('open');

      // Close all other dropdowns
      document.querySelectorAll('.nav-dropdown-item').forEach(function(item) {
        if (item !== parent) {
          item.classList.remove('open');
          const btn = item.querySelector('.nav-dropdown-toggle');
          if (btn) btn.setAttribute('aria-expanded', 'false');
        }
      });

      if (isOpen) {
        parent.classList.remove('open');
        toggleBtn.setAttribute('aria-expanded', 'false');
      } else {
        parent.classList.add('open');
        toggleBtn.setAttribute('aria-expanded', 'true');
      }
      return;
    }

    // If clicked inside dropdown menu link
    const menuLink = e.target.closest('.nav-dropdown-menu a');
    if (menuLink) {
      document.querySelectorAll('.nav-dropdown-item').forEach(function(item) {
        item.classList.remove('open');
        const btn = item.querySelector('.nav-dropdown-toggle');
        if (btn) btn.setAttribute('aria-expanded', 'false');
      });
      if (window.innerWidth < 992) {
        const nav = document.getElementById('ftco-nav');
        if (nav) nav.classList.remove('show');
      }
      return;
    }

    // If clicked outside
    if (!e.target.closest('.nav-dropdown-item')) {
      document.querySelectorAll('.nav-dropdown-item').forEach(function(item) {
        item.classList.remove('open');
        const btn = item.querySelector('.nav-dropdown-toggle');
        if (btn) btn.setAttribute('aria-expanded', 'false');
      });
    }
  });

  // Close nav dropdowns on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
      document.querySelectorAll('.nav-dropdown-item').forEach(function(item) {
        item.classList.remove('open');
        const btn = item.querySelector('.nav-dropdown-toggle');
        if (btn) btn.setAttribute('aria-expanded', 'false');
      });
    }
  });

  // ==========================================
  // RIHANNA SIGNATURE SAFARI - COOKIE CONSENT
  // ==========================================
  function initCookieConsent() {
    const consentKey = 'rihanna_cookie_consent';
    const currentConsent = localStorage.getItem(consentKey);

    // If on cookies.html, initialize interactive preference manager
    const prefStatusEl = document.getElementById('cookie-status-display');
    if (prefStatusEl) {
      if (currentConsent === 'all') {
        prefStatusEl.innerHTML = '<span class="badge px-3 py-2" style="background:#233522;color:#c8963e;border:1px solid #c8963e;font-size:12px;font-weight:700;"><i class="fa-solid fa-circle-check mr-1"></i> All Cookies Accepted (Essential, Analytics &amp; Functional)</span>';
      } else if (currentConsent === 'essential') {
        prefStatusEl.innerHTML = '<span class="badge px-3 py-2" style="background:#152214;color:#ffffff;border:1px solid rgba(255,255,255,0.25);font-size:12px;font-weight:600;"><i class="fa-solid fa-shield mr-1"></i> Essential Cookies Only</span>';
      } else {
        prefStatusEl.innerHTML = '<span class="badge px-3 py-2" style="background:#f7edd7;color:#a27426;border:1px solid #c8963e;font-size:12px;font-weight:700;"><i class="fa-solid fa-clock mr-1"></i> No Preference Selected (Defaulting to Essential)</span>';
      }
    }

    // Attach event listeners to page preference buttons if on cookies.html
    const btnAcceptPage = document.getElementById('btn-cookie-accept-page');
    if (btnAcceptPage && !btnAcceptPage._hasCookieListener) {
      btnAcceptPage._hasCookieListener = true;
      btnAcceptPage.addEventListener('click', function() {
        localStorage.setItem(consentKey, 'all');
        initCookieConsent();
      });
    }
    const btnEssentialPage = document.getElementById('btn-cookie-essential-page');
    if (btnEssentialPage && !btnEssentialPage._hasCookieListener) {
      btnEssentialPage._hasCookieListener = true;
      btnEssentialPage.addEventListener('click', function() {
        localStorage.setItem(consentKey, 'essential');
        initCookieConsent();
      });
    }
    const btnResetPage = document.getElementById('btn-cookie-reset-page');
    if (btnResetPage && !btnResetPage._hasCookieListener) {
      btnResetPage._hasCookieListener = true;
      btnResetPage.addEventListener('click', function() {
        localStorage.removeItem(consentKey);
        initCookieConsent();
      });
    }

    if (currentConsent || prefStatusEl) {
      return; // Already consented or currently on dedicated cookies.html preferences center
    }

    function showBanner() {
      let banner = document.getElementById('safari-cookie-banner');
      if (!banner) {
        banner = document.createElement('div');
        banner.id = 'safari-cookie-banner';
        banner.className = 'safari-cookie-banner';
        banner.setAttribute('role', 'dialog');
        banner.setAttribute('aria-label', 'Cookie Consent Notice');
        banner.innerHTML = 
          '<div class="safari-cookie-content">' +
            '<div class="safari-cookie-icon"><i class="fa-solid fa-cookie-bite"></i></div>' +
            '<div class="safari-cookie-text">' +
              '<h4>Expedition Transparency &amp; Cookie Notice</h4>' +
              '<p>Rihanna Signature Safari uses essential cookies to ensure seamless navigation, preserve expedition inquiries, and provide an ultra-luxury digital experience. Review our <a href="cookies.html">Cookie Policy</a> and <a href="privacy.html">Privacy Policy</a>.</p>' +
            '</div>' +
          '</div>' +
          '<div class="safari-cookie-actions">' +
            '<button type="button" class="btn-cookie-decline" id="btn-cookie-essential">Essential Only</button>' +
            '<button type="button" class="btn-cookie-accept" id="btn-cookie-accept">Accept All</button>' +
          '</div>';
        document.body.appendChild(banner);

        const btnAccept = document.getElementById('btn-cookie-accept');
        if (btnAccept) {
          btnAccept.addEventListener('click', function() {
            localStorage.setItem(consentKey, 'all');
            banner.classList.remove('show');
            setTimeout(function() { if (banner.parentNode) banner.parentNode.removeChild(banner); }, 450);
            if (prefStatusEl) initCookieConsent();
          });
        }

        const btnEssential = document.getElementById('btn-cookie-essential');
        if (btnEssential) {
          btnEssential.addEventListener('click', function() {
            localStorage.setItem(consentKey, 'essential');
            banner.classList.remove('show');
            setTimeout(function() { if (banner.parentNode) banner.parentNode.removeChild(banner); }, 450);
            if (prefStatusEl) initCookieConsent();
          });
        }
      }

      setTimeout(function() {
        banner.classList.add('show');
      }, 700);
    }

    showBanner();
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCookieConsent);
  } else {
    initCookieConsent();
  }

})(jQuery);

