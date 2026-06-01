/**
* Template Name: FlexStart - v1.4.0
* Template URL: https://bootstrapmade.com/flexstart-bootstrap-startup-template/
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/
(function() {
  "use strict";

  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim()
    if (all) {
      return [...document.querySelectorAll(el)]
    } else {
      return document.querySelector(el)
    }
  }

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    if (all) {
      select(el, all).forEach(e => e.addEventListener(type, listener))
    } else {
      select(el, all).addEventListener(type, listener)
    }
  }

  /**
   * Easy on scroll event listener 
   */
  const onscroll = (el, listener) => {
    el.addEventListener('scroll', listener)
  }

  /**
   * Navbar links active state on scroll
   */
  let navbarlinks = select('#navbar .scrollto', true)
  const navbarlinksActive = () => {
    let position = window.scrollY + 200
    navbarlinks.forEach(navbarlink => {
      if (!navbarlink.hash) return
      let section = select(navbarlink.hash)
      if (!section) return
      if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
        navbarlink.classList.add('active')
      } else {
        navbarlink.classList.remove('active')
      }
    })
  }
  window.addEventListener('load', navbarlinksActive)
  onscroll(document, navbarlinksActive)

  /**
   * Scrolls to an element with header offset
   */
  const scrollto = (el) => {
    let header = select('#header')
    let offset = header.offsetHeight

    if (!header.classList.contains('header-scrolled')) {
      offset -= 10
    }

    let elementPos = select(el).offsetTop
    window.scrollTo({
      top: elementPos - offset,
      behavior: 'smooth'
    })
  }

  /**
   * Toggle .header-scrolled class to #header when page is scrolled
   */
  let selectHeader = select('#header')
  if (selectHeader) {
    const headerScrolled = () => {
      if (window.scrollY > 100) {
        selectHeader.classList.add('header-scrolled')
      } else {
        selectHeader.classList.remove('header-scrolled')
      }
    }
    window.addEventListener('load', headerScrolled)
    onscroll(document, headerScrolled)
  }

  /**
   * Back to top button
   */
  let backtotop = select('.back-to-top')
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) {
        backtotop.classList.add('active')
      } else {
        backtotop.classList.remove('active')
      }
    }
    window.addEventListener('load', toggleBacktotop)
    onscroll(document, toggleBacktotop)
  }

  /**
   * Mobile nav toggle
   */
  const navbar = select('#navbar')
  const mobileNavToggle = select('.mobile-nav-toggle')
  const navbarParent = navbar ? navbar.parentElement : null
  const navbarNextSibling = navbar ? navbar.nextElementSibling : null

  const restoreNavbarPosition = () => {
    if (!navbar || !navbarParent || navbar.parentElement === navbarParent) return

    navbarParent.insertBefore(navbar, navbarNextSibling)
  }

  const resetMobileDropdowns = () => {
    select('.navbar .dropdown > a', true).forEach(dropdownToggle => {
      dropdownToggle.setAttribute('aria-expanded', 'false')
      dropdownToggle.classList.remove('expanded')
      if (dropdownToggle.nextElementSibling) {
        dropdownToggle.nextElementSibling.classList.remove('dropdown-active')
      }
    })
  }

  const closeMobileNav = () => {
    if (!navbar || !navbar.classList.contains('navbar-mobile')) return

    navbar.classList.remove('navbar-mobile')
    document.body.classList.remove('mobile-nav-open')
    mobileNavToggle.classList.add('bi-list')
    mobileNavToggle.classList.remove('bi-x')
    mobileNavToggle.setAttribute('aria-expanded', 'false')
    mobileNavToggle.setAttribute('aria-label', 'Open navigation menu')
    resetMobileDropdowns()
    restoreNavbarPosition()
  }

  on('click', '.mobile-nav-toggle', function(e) {
    e.preventDefault()
    const isOpening = !navbar.classList.contains('navbar-mobile')

    if (isOpening) {
      document.body.appendChild(navbar)
      navbar.classList.add('navbar-mobile')
      document.body.classList.add('mobile-nav-open')
      this.classList.remove('bi-list')
      this.classList.add('bi-x')
      this.setAttribute('aria-expanded', 'true')
      this.setAttribute('aria-label', 'Close navigation menu')
    } else {
      closeMobileNav()
    }
  })

  /**
   * Mobile nav dropdowns activate
   */
  on('click', '.navbar .dropdown > a', function(e) {
    if (navbar.classList.contains('navbar-mobile')) {
      e.preventDefault()
      this.nextElementSibling.classList.toggle('dropdown-active')
      // Toggle aria-expanded for accessibility
      const isExpanded = this.getAttribute('aria-expanded') === 'true'
      this.setAttribute('aria-expanded', !isExpanded)
      this.classList.toggle('expanded')
    }
  }, true)

  // Add keyboard accessibility for dropdowns
  on('keydown', '.navbar .dropdown > a', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      if (navbar.classList.contains('navbar-mobile')) {
        e.preventDefault()
        this.nextElementSibling.classList.toggle('dropdown-active')
        const isExpanded = this.getAttribute('aria-expanded') === 'true'
        this.setAttribute('aria-expanded', !isExpanded)
        this.classList.toggle('expanded')
      }
    }
  }, true)

  /**
   * Scrool with ofset on links with a class name .scrollto
   */
  on('click', '.scrollto', function(e) {
    if (this.hash && select(this.hash)) {
      e.preventDefault()

      if (navbar.classList.contains('navbar-mobile')) {
        closeMobileNav()
      }
      scrollto(this.hash)
    }
  }, true)

  /**
   * Close the mobile drawer after following submenu and page links.
   */
  on('click', '.navbar a', function(e) {
    if (!navbar.classList.contains('navbar-mobile') || this.parentElement.classList.contains('dropdown')) return

    if (this.hash && select(this.hash)) {
      e.preventDefault()
      closeMobileNav()
      scrollto(this.hash)
      return
    }

    closeMobileNav()
  }, true)

  document.addEventListener('click', e => {
    if (navbar.classList.contains('navbar-mobile') && e.target === navbar) {
      closeMobileNav()
    }
  })

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      closeMobileNav()
    }
  })

  window.addEventListener('resize', () => {
    if (window.innerWidth > 991) {
      closeMobileNav()
    }
  })

  /**
   * Scroll with ofset on page load with hash links in the url
   */
  window.addEventListener('load', () => {
    if (window.location.hash) {
      if (select(window.location.hash)) {
        scrollto(window.location.hash)
      }
    }
  });

  /**
   * Clients Slider
   */
  new Swiper('.clients-slider', {
    speed: 400,
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false
    },
    slidesPerView: 'auto',
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true
    },
    breakpoints: {
      320: {
        slidesPerView: 2,
        spaceBetween: 40
      },
      480: {
        slidesPerView: 3,
        spaceBetween: 60
      },
      640: {
        slidesPerView: 4,
        spaceBetween: 80
      },
      992: {
        slidesPerView: 6,
        spaceBetween: 120
      }
    }
  });

  /**
   * Porfolio isotope and filter
   */
  window.addEventListener('load', () => {
    let portfolioContainer = select('.portfolio-container');
    if (portfolioContainer) {
      let portfolioIsotope = new Isotope(portfolioContainer, {
        itemSelector: '.portfolio-item',
        layoutMode: 'fitRows'
      });

      let portfolioFilters = select('#portfolio-flters li', true);

      on('click', '#portfolio-flters li', function(e) {
        e.preventDefault();
        portfolioFilters.forEach(function(el) {
          el.classList.remove('filter-active');
        });
        this.classList.add('filter-active');

        portfolioIsotope.arrange({
          filter: this.getAttribute('data-filter')
        });
        aos_init();
      }, true);
    }

  });

  /**
   * Initiate portfolio lightbox 
   */
  const portfolioLightbox = GLightbox({
    selector: '.portfokio-lightbox'
  });

  /**
   * Portfolio details slider
   */
  new Swiper('.portfolio-details-slider', {
    speed: 400,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false
    },
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true
    }
  });

  /**
   * Testimonials slider
   */
  new Swiper('.testimonials-slider', {
    speed: 600,
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false
    },
    slidesPerView: 'auto',
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true
    },
    breakpoints: {
      320: {
        slidesPerView: 1,
        spaceBetween: 40
      },

      1200: {
        slidesPerView: 3,
      }
    }
  });

  /**
   * Animation on scroll
   */
  function aos_init() {
    AOS.init({
      duration: 1000,
      easing: "ease-in-out",
      once: true,
      mirror: false
    });
  }
  window.addEventListener('load', () => {
    aos_init();
  });
  

})();
