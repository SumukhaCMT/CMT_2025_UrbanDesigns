<!-- Javascript -->

<script src="js/jquery-3.7.1.min.js"></script>
<script src="js/jquery-migrate-3.4.1.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/tether.min.js"></script>
<script src="js/jquery.easing.js"></script>
<script src="js/jquery-waypoints.js"></script>
<script src="js/jquery-validate.js"></script>
<script src="js/jquery.prettyPhoto.js"></script>
<script src="js/slick.min.js"></script>
<script src="js/numinate.min.js"></script>
<script src="js/imagesloaded.min.js"></script>
<script src="js/jquery-isotope.js"></script>
<script src="js/jquery.event.move.js"></script>
<script src="js/jquery.twentytwenty.js"></script>
<script src="js/circle-progress.min.js"></script>
<script src="js/main.js"></script>

<!-- Revolution Slider -->
<script src="revolution/js/slider.js"></script>
<script src='revolution/js/revolution.tools.min.js'></script>
<script src='revolution/js/rs6.min.js'></script>
<!-- Javascript end-->

<script>
    // Image gallery functionality
    function changeMainImage(clickedImg) {
        const mainImage = document.getElementById('main-product-image');
        const thumbnails = document.querySelectorAll('.thumbnail-images img');

        // Update main image
        mainImage.src = clickedImg.src;
        mainImage.alt = clickedImg.alt;

        // Update active thumbnail
        thumbnails.forEach(thumb => thumb.classList.remove('active'));
        clickedImg.classList.add('active');
    }

    // Quantity controls
    function increaseQuantity() {
        const quantityInput = document.getElementById('quantity');
        quantityInput.value = parseInt(quantityInput.value) + 1;
    }

    function decreaseQuantity() {
        const quantityInput = document.getElementById('quantity');
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    }

    // Product tabs functionality
    document.addEventListener('DOMContentLoaded', function () {
        const tabLinks = document.querySelectorAll('.tabs .tab a');
        const tabContents = document.querySelectorAll('.tab-content-area');

        tabLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                // Remove active class from all tabs and content
                document.querySelectorAll('.tabs .tab').forEach(tab => {
                    tab.classList.remove('active');
                });
                tabContents.forEach(content => {
                    content.classList.remove('active');
                });

                // Add active class to clicked tab
                this.parentElement.classList.add('active');

                // Show corresponding content
                const targetTab = this.getAttribute('href');
                const targetContent = document.querySelector(targetTab);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    });

    // Mobile Sticky Header JavaScript
    (function () {
        'use strict';

        // Configuration
        const config = {
            headerSelector: '.ttm-stickable-header',
            fixedClass: 'fixed-header',
            bodyClass: 'fixed-header-active',
            offset: 100, // Pixels scrolled before header becomes sticky
            mobileBreakpoint: 1199 // Max width for mobile sticky header
        };

        // DOM elements
        let header = null;
        let headerHeight = 0;
        let isSticky = false;
        let isMobile = false;

        // Initialize
        function init() {
            header = document.querySelector(config.headerSelector);
            if (!header) return;

            // Check if we're on mobile
            checkMobileState();

            // Set initial header height
            updateHeaderHeight();

            // Bind events
            bindEvents();

            // Initial check
            handleScroll();
        }

        // Check if current screen size is mobile
        function checkMobileState() {
            isMobile = window.innerWidth <= config.mobileBreakpoint;
        }

        // Update header height based on current state
        function updateHeaderHeight() {
            if (!header) return;
            headerHeight = header.offsetHeight;
        }

        // Bind scroll and resize events
        function bindEvents() {
            let ticking = false;

            // Throttled scroll handler
            window.addEventListener('scroll', function () {
                if (!ticking) {
                    requestAnimationFrame(function () {
                        handleScroll();
                        ticking = false;
                    });
                    ticking = true;
                }
            });

            // Resize handler
            window.addEventListener('resize', debounce(function () {
                checkMobileState();
                updateHeaderHeight();
                handleScroll();
            }, 250));
        }

        // Main scroll handler
        function handleScroll() {
            if (!header || !isMobile) {
                // If not mobile, remove sticky behavior
                if (isSticky) {
                    removeStickyHeader();
                }
                return;
            }

            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const shouldBeSticky = scrollTop > config.offset;

            if (shouldBeSticky && !isSticky) {
                addStickyHeader();
            } else if (!shouldBeSticky && isSticky) {
                removeStickyHeader();
            }
        }

        // Add sticky header
        function addStickyHeader() {
            if (!header) return;

            header.classList.add(config.fixedClass);
            document.body.classList.add(config.bodyClass);
            isSticky = true;

            // Trigger custom event
            window.dispatchEvent(new CustomEvent('stickyHeaderActivated', {
                detail: { header: header }
            }));
        }

        // Remove sticky header
        function removeStickyHeader() {
            if (!header) return;

            header.classList.remove(config.fixedClass);
            document.body.classList.remove(config.bodyClass);
            isSticky = false;

            // Trigger custom event
            window.dispatchEvent(new CustomEvent('stickyHeaderDeactivated', {
                detail: { header: header }
            }));
        }

        // Debounce utility function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

        // Public API (optional)
        window.MobileStickyHeader = {
            init: init,
            activate: addStickyHeader,
            deactivate: removeStickyHeader,
            isActive: function () { return isSticky; },
            isMobileView: function () { return isMobile; }
        };

    })();

    // Optional: Advanced features for specific use cases
    (function () {
        'use strict';

        // Handle header color changes based on scroll position
        function handleHeaderColorChange() {
            const header = document.querySelector('.ttm-stickable-header');
            if (!header) return;

            window.addEventListener('scroll', debounce(function () {
                const scrollTop = window.pageYOffset;
                const isScrolled = scrollTop > 50;

                if (isScrolled) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            }, 16));
        }

        // Handle mobile menu toggle with sticky header
        function handleMobileMenuToggle() {
            const menuToggle = document.querySelector('.menubar');
            const mobileMenu = document.querySelector('.site-navigation');

            if (!menuToggle || !mobileMenu) return;

            menuToggle.addEventListener('click', function () {
                const isOpen = mobileMenu.classList.contains('open');

                if (isOpen) {
                    mobileMenu.classList.remove('open');
                    menuToggle.classList.remove('active');
                } else {
                    mobileMenu.classList.add('open');
                    menuToggle.classList.add('active');
                }
            });

            // Close menu when clicking outside
            document.addEventListener('click', function (e) {
                if (!menuToggle.contains(e.target) && !mobileMenu.contains(e.target)) {
                    mobileMenu.classList.remove('open');
                    menuToggle.classList.remove('active');
                }
            });
        }

        // Smooth scroll for anchor links
        function handleSmoothScroll() {
            const links = document.querySelectorAll('a[href^="#"]');
            const header = document.querySelector('.ttm-stickable-header');

            links.forEach(link => {
                link.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    const target = document.querySelector(href);

                    if (target) {
                        e.preventDefault();
                        const headerHeight = header ? header.offsetHeight : 0;
                        const targetPosition = target.offsetTop - headerHeight - 20;

                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        }

        // Utility debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Initialize advanced features
        document.addEventListener('DOMContentLoaded', function () {
            handleHeaderColorChange();
            handleMobileMenuToggle();
            handleSmoothScroll();
        });

    })();


    // Make entire product card clickable - Add this to your products.php
    document.addEventListener('DOMContentLoaded', function () {
        // Get all product cards
        const productCards = document.querySelectorAll('.product-card');

        productCards.forEach(card => {
            card.addEventListener('click', function (e) {
                // Don't trigger if clicking on the buy button
                if (e.target.closest('.btn-primary')) {
                    return;
                }

                // Get the product link from the title
                const productLink = card.querySelector('.product-title a');
                if (productLink) {
                    const href = productLink.getAttribute('href');
                    // Navigate to product details page
                    window.location.href = href;
                }
            });

            // Add visual feedback
            card.addEventListener('mousedown', function () {
                card.style.transform = 'translateY(-1px) scale(0.98)';
            });

            card.addEventListener('mouseup', function () {
                card.style.transform = '';
            });

            card.addEventListener('mouseleave', function () {
                card.style.transform = '';
            });
        });
    });
</script>