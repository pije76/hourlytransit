/*-----------------------------------------------------------
    * Template Name    : Hottlo
    * Author           : KreativDev
    * File Description : This file contains the javaScript functions for the actual template, this
                        is the file you need to edit to change the functionality of the template.
    *------------------------------------------------------------
*/

!(function ($) {
    "use strict";

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /*============================================
        Mobile menu
    ============================================*/
    var mobileMenu = function () {
        // Variables
        var body = $("body"),
            mainNavbar = $(".main-navbar"),
            mobileNavbar = $(".mobile-menu"),
            cloneInto = $(".mobile-menu-wrapper"),
            cloneItem = $(".mobile-item"),
            menuToggler = $(".menu-toggler"),
            offCanvasMenu = $("#offcanvasMenu"),
            backdrop,
            _initializeBackDrop = function () {
                backdrop = document.createElement('div');
                backdrop.className = 'menu-backdrop';
                backdrop.onclick = function hideOffCanvas() {
                    menuToggler.removeClass("active"),
                        body.removeClass("mobile-menu-active"),
                        backdrop.remove();
                };
                document.body.appendChild(backdrop);
            };

        menuToggler.on("click", function () {
            $(this).toggleClass("active");
            body.toggleClass("mobile-menu-active");
            _initializeBackDrop();
            if (!body.hasClass("mobile-menu-active")) {
                $('.menu-backdrop').remove();
            }
        })

        mainNavbar.find(cloneItem).clone(!0).appendTo(cloneInto);

        if (offCanvasMenu) {
            body.find(offCanvasMenu).clone(!0).appendTo(cloneInto);
        }

        mobileNavbar.find("li").each(function (index) {
            var toggleBtn = $(this).children(".toggle")
            toggleBtn.on("click", function (e) {
                $(this)
                    .parent("li")
                    .children("ul")
                    .stop(true, true)
                    .slideToggle(350);
                $(this).parent("li").toggleClass("show");
            })
        })

        // check browser width in real-time
        var checkBreakpoint = function () {
            var winWidth = window.innerWidth;
            if (winWidth <= 1199) {
                mainNavbar.hide();
                mobileNavbar.show()
            } else {
                mainNavbar.show();
                mobileNavbar.hide();
                $('.menu-backdrop').remove();
            }
        }
        checkBreakpoint();

        $(window).on('resize', function () {
            checkBreakpoint();
        });
    }
    mobileMenu();

    var getHeaderHeight = function () {
        var headerNext = $(".header-next");
        var header = headerNext.prev(".header-area");
        var headerHeight = header.height();

        headerNext.css({
            "margin-top": headerHeight + "px"
        });
    }
    getHeaderHeight();

    $(window).on('resize', function () {
        getHeaderHeight();
    });

    // getHeaderHeight2 use listing map
    var getHeaderHeight2 = function () {
        var headerNext2 = $(".header-next2");
        var header = headerNext2.prev(".header-area");
        var headerHeight = header.height();

        // Add 20px to the header height
        var newMarginTop = headerHeight + 20; 

        headerNext2.css({
            "margin-top": newMarginTop + "px"
        });
    }
    getHeaderHeight2();

    $(window).on('resize', function () {
        getHeaderHeight2();
    });

    // sidebar height
    if ($('.sidebar-scroll').length > 0) {
        function setSidebarHeight() {
            const viewportHeight = window.innerHeight;
            const sidebar = document.querySelector('.sidebar-scroll');
            sidebar.style.height = `${viewportHeight}px`;
        }
        setSidebarHeight();
        window.addEventListener('resize', setSidebarHeight);
    }
    

    /*============================================
        Navlink active class
    ============================================*/
    var a = $("#mainMenu .nav-link"),
        c = window.location;
    for (var i = 0; i < a.length; i++) {
        const el = a[i];

        if (el.href == c) {
            el.classList.add("active");
        }
    }

    /*============================================
        Sticky header
    ============================================*/
    
    $(window).on("scroll", function () {
        var header = $(".header-area");
        // If window scroll down .is-sticky class will added to header
        if ($(window).scrollTop() >= 100) {
            header.addClass("is-sticky");
        } else {
            header.removeClass("is-sticky");
        }
    });


    /*============================================
        Password icon toggle
    ============================================*/
    $(".show-password-field").on("click", function () {
        var showIcon = $(this).children(".show-icon");
        var passwordField = $(this).prev("input");
        showIcon.toggleClass("show");
        if (passwordField.attr("type") == "password") {
            passwordField.attr("type", "text")
        } else {
            passwordField.attr("type", "password");
        }
    })


    /*============================================
        Quantity button
    ============================================*/
    $(document).on('click', '.quantity-down', function () {
        var numcourse = Number($(this).next().val());
        if (numcourse > 0) $(this).next().val(numcourse - 1);
    });
    $(document).on('click', '.quantity-up', function () {
        var numcourse = Number($(this).prev().val());
        $(this).prev().val(numcourse + 1);
    })


    /*============================================
        Image to background image
    ============================================*/
    var bgImage = $(".bg-img")
    bgImage.each(function () {
        var el = $(this),
            src = el.attr("data-bg-img");

        el.css({
            "background-image": "url(" + src + ")",
            "background-repeat": "no-repeat"
        });
    });

    /*============================================
        Toggle List
    ============================================*/

    $("[data-toggle-list]").each(function () {
        var list = $(this).children();
        var listShow = parseInt($(this).data("toggle-show"), 10);
        var listShowBtn = $(this).next("[data-toggle-btn]");

        var showMoreText = show_more;
        var showLessText = show_less;

        if (list.length > listShow) {
            listShowBtn.show();
            list.slice(listShow).hide();

            listShowBtn.on("click", function () {
                var isExpanded = listShowBtn.text() === showLessText;
                list.slice(listShow).slideToggle(300);
                listShowBtn.text(isExpanded ? showMoreText : showLessText);
            });
        } else {
            listShowBtn.hide();
        }
    });

    /*============================================
        Sidebar toggle
    ============================================*/
    $(".list-dropdown .category-toggle").on("click", function (t) {
        var i = $(this).closest("li"),
            o = i.find("ul").eq(0);

        if (i.hasClass("open")) {
            o.slideUp(300, function () {
                i.removeClass("open")
            })
        } else {
            o.slideDown(300, function () {
                i.addClass("open")
            })
        }
        t.stopPropagation(), t.preventDefault()
    })

    /*============================================
        Read more toggle button
    ============================================*/
    $(".read-more-btn").on("click", function () {
        $(this).prev().toggleClass('show');

        if ($(this).prev().hasClass("show")) {
            $(this).text(read_less);
        } else {
            $(this).text(read_more);
        }
    })

    /*============================================
        Tabs mouse hover animation
    ============================================*/
    $("[data-hover='fancyHover']").mouseHover();


    /*============================================
        Sliders
    ============================================*/
    // Home Slider 1
    var homeSlider1 = new Swiper("#home-slider-1", {
        loop: true,
        speed: 2000,
        parallax: true,
        slidesPerView: 1,
        autoplay: true,
        effect: 'fade',

        pagination: {
            el: '#home-slider-1-pagination',
            clickable: true
        },

        on: {
            slideChange: function () {
                var doAnimations = function (elements) {
                    var animationEndEvents = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
                    elements.each(function () {
                        var animationDelay = $(this).data('delay');
                        var animationType = 'animate__animated ' + $(this).data('animation');
                        $(this).css({
                            'animation-delay': animationDelay,
                            '-webkit-animation-delay': animationDelay
                        });
                        $(this).addClass(animationType).one(animationEndEvents, function () {
                            $(this).removeClass(animationType);
                        });
                    });
                }
                var firstAnimatingElements = $('.swiper-slide').find('[data-animation]');
                doAnimations(firstAnimatingElements);
            },
        },
    });
    var homeImageSlider1 = new Swiper("#home-img-slider-1", {
        loop: true,
        speed: 1500,
        effect: 'fade',
        slidesPerView: 1
    });
    // Sync both slider
    homeImageSlider1.controller.control = homeSlider1;
    homeSlider1.controller.control = homeImageSlider1;

    // Testimonial Slider
    var testimonialSlider1 = new Swiper("#testimonial-slider-1", {
        speed: 800,
        spaceBetween: 25,
        loop: false,
        slidesPerView: 1,

        // Pagination
        pagination: {
            el: '#testimonial-slider-1-pagination',
            clickable: true
        }
    });
    var testimonialSlider2 = new Swiper("#testimonial-slider-2", {
        slidesPerView: 2,
        grid: {
            rows: 2,
        },

        // Navigation arrows
        navigation: {
            nextEl: "#testimonial-slider-2-next",
            prevEl: "#testimonial-slider-2-prev",
        },

        breakpoints: {
            320: {
                slidesPerView: 1,
                grid: {
                    rows: 0,
                },
            },
            992: {
                slidesPerView: 2,
                grid: {
                    rows: 2,
                },
            },
        }
    });

    // Product Slider
    $(".product-slider").each(function () {
        var id = $(this).attr("id");
        var sliderId = "#" + id;

        var swiper = new Swiper(sliderId, {
            speed: 800,
            spaceBetween: 25,
            loop: false,
            slidesPerView: 3,

            // Navigation arrows
            navigation: {
                nextEl: sliderId + "-next",
                prevEl: sliderId + "-prev",
            },

            // Pagination
            pagination: {
                el: sliderId + '-pagination',
                clickable: true
            },

            breakpoints: {
                320: {
                    slidesPerView: 1
                },
                992: {
                    slidesPerView: 2
                },
                1200: {
                    slidesPerView: 3
                },
            }
        })
    })

    // Category Slider all
    $(".category-slider").each(function () {
        var id = $(this).attr("id");
        var slidePerView = $(this).data("slides-per-view");
        var loops = $(this).data("swiper-loop");
        var space = $(this).data("swiper-space");
        var sliderId = "#" + id;
        loops = false;
        var swiper = new Swiper(sliderId, {
            loop: loops,
            spaceBetween: space,
            speed: 1000,
            autoplay: {
                delay: 3000,
            },
            slidesPerView: slidePerView,
            pagination: true,

            pagination: {
                el: sliderId + "-pagination",
                clickable: true,
            },

            // Navigation arrows
            navigation: {
                nextEl: sliderId + "-next",
                prevEl: sliderId + "-prev",
            },

            breakpoints: {
                300: {
                    slidesPerView: 1
                },
                480: {
                    slidesPerView: 2
                },
                576: {
                    slidesPerView: 2
                },
                992: {
                    slidesPerView: 3
                },
                1440: {
                    slidesPerView: slidePerView
                },
            }
        })
    })

    // Listing single slider
    var proSingleThumb = new Swiper(".slider-thumbnails", {
        loop: false,
        speed: 1000,
        // centeredSlides: true,
        spaceBetween: 20,
        slidesPerView: 3,
        breakpoints: {
            0: {
                slidesPerView: 2,
                spaceBetween: 15,
            },
            360: {
                slidesPerView: 3,
                spaceBetween: 15,
            },
            576: {
                slidesPerView: 4,
                spaceBetween: 15,
            },
            992: {
                slidesPerView: 3,
                spaceBetween: 20,
            },
        }
    });
    var proSingleSlider = new Swiper(".product-single-slider", {
        loop: false,
        speed: 1000,
        autoplay: {
            delay: 3000
        },
        watchSlidesProgress: true,
        thumbs: {
            swiper: proSingleThumb,
        },

        // Navigation arrows
        navigation: {
            nextEl: ".slider-btn-next",
            prevEl: ".slider-btn-prev",
        },
    });


    /*============================================
        Youtube popup
    ============================================*/
    $(".youtube-popup").magnificPopup({
        disableOn: 300,
        type: "iframe",
        mainClass: "mfp-fade",
        removalDelay: 160,
        preloader: false,
        fixedContentPos: false
    })


    /*============================================
        Gallery popup
    ============================================*/
    $(".gallery-popup").each(function () {
        $(this).magnificPopup({
            delegate: 'a',
            type: 'image',
            mainClass: 'mfp-fade',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [0, 1]
            },
            image: {
                tError: '<a href="%url%">The image #%curr%</a> could not be loaded.'
            },
            callbacks: {
                elementParse: function (item) {
                    // the class name
                    if (item.el.hasClass("video-link")) {
                        item.type = 'iframe';
                    } else {
                        item.type = 'image';
                    }
                }
            },
            removalDelay: 500, //delay removal by X to allow out-animation
            closeOnContentClick: true,
            midClick: true
        });
    })


    /*============================================
        Go to top
    ============================================*/
    $(window).on("scroll", function () {
        // If window scroll down .active class will added to go-top
        var goTop = $(".go-top");

        if ($(window).scrollTop() >= 200) {
            goTop.addClass("active");
        } else {
            goTop.removeClass("active")
        }
    })
    $(".go-top").on("click", function (e) {
        $("html, body").animate({
            scrollTop: 0,
        }, 0);
    });


    /*============================================
        Lazyload image
    ============================================*/
    var lazyLoad = function () {
        window.lazySizesConfig = window.lazySizesConfig || {};
        window.lazySizesConfig.loadMode = 2;
        lazySizesConfig.preloadAfterLoad = true;
    }


    /*============================================
        Odometer
    ============================================*/
    $(".counter").counterUp({
        delay: 10,
        time: 1000
    });


    /*============================================
        Nice select
    ============================================*/
    $(".niceselect").niceSelect();

    var selectList = $(".nice-select .list")
    $(".nice-select .list").each(function () {
        var list = $(this).children();
        if (list.length > 5) {
            $(this).css({
                "height": "160px",
                "overflow-y": "scroll"
            })
        }
    })


    /*============================================
        Select2
    ============================================*/
    $('.select2').select2();


    /*============================================
        Sidebar scroll
    ============================================*/
    $(document).ready(function () {
        $(".widget").each(function () {
            var child = $(this).find(".accordion-body.scroll-y");
            if (child.height() >= 245) {
                child.css({
                    "padding-inline-end": "10px",
                })
            }
        })
    })


    /*============================================
        Tooltip
    ============================================*/
    var tooltipTriggerList = [].slice.call($('[data-tooltip="tooltip"]'))

    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })


    /*============================================
        Footer date
    ============================================*/
    var date = new Date().getFullYear();
    $("#footerDate").text(date);


    /*============================================
        Image upload
    ============================================*/
    var fileReader = function (input) {
        var regEx = new RegExp(/\.(gif|jpe?g|tiff?|png|webp|bmp)$/i);
        var errorMsg = $("#errorMsg");

        if (input.files && input.files[0] && regEx.test(input.value)) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#imagePreview').css('background-image', 'url(' + e.target.result + ')');
                $('#imagePreview').hide();
                $('#imagePreview').fadeIn(650);
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            errorMsg.html("Please upload a valid file type")
        }
    }
    $("#imageUpload").on("change", function () {
        fileReader(this);
    });


    /*============================================
        Data tables
    ============================================*/
    var dataTable = function () {
        var dTable = $("#myTable");

        if (dTable.length) {
            dTable.DataTable({
                order: [] // Disable default ordering
            });
        }
    };



    /*============================================
        Document on ready
    ============================================*/
    $(document).ready(function () {
        lazyLoad(),
            dataTable()
    })


    /*============================================
        Date-range Picker
    ============================================*/
    // Check-in
    $('input[name="checkInDate"]').daterangepicker({
        "singleDatePicker": true,
        "timePicker": true,
        autoUpdateInput: false,
    });
    $('input[name="checkInDate"]').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY'));
    });
    $('input[name="checkInDate"]').on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
    });
    // Check-out
    $('input[name="checkOutDate"]').daterangepicker({
        "singleDatePicker": true,
        "timePicker": true,
        autoUpdateInput: false,
    });
    $('input[name="checkOutDate"]').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY'));
    });
    $('input[name="checkOutDate"]').on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
    });
    // Check-in-out
    $('input[name="checkInOut"]').daterangepicker({
        "timePicker": true,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        "startDate": "01/19/2024",
        "endDate": "01/31/2024"
    })


    if ($('.product-slider-style2-thumb').length > 0) {
        var swiper = new Swiper(".product-slider-style2-thumb", {
            loop: false,
            spaceBetween: 10,
            slidesPerView: 4,
            speed: 1000,
            freeMode: true,
            watchSlidesProgress: true,
            breakpoints: {
                0: {
                    slidesPerView: 2,
                },
                575: {
                    slidesPerView: 3,
                },
                768: {
                    slidesPerView: 3,

                },
                992: {
                    slidesPerView: 4,

                },
            },

        });
    }

    if ($('.product-slider-style2').length > 0) {
        var swiper2 = new Swiper(".product-slider-style2", {
            loop: false,
            spaceBetween: 16,
            speed: 1000,
            navigation: {
                nextEl: ".product-slider-button-next",
                prevEl: ".product-slider-button-prev",
            },
            thumbs: {
                swiper: swiper,
            },
        });
    }

})(jQuery);

$(window).on("load", function () {
    const delay = 350;

    /*============================================
    Preloader
    ============================================*/
    $("#preLoader").delay(delay).fadeOut('slow');

    /*============================================
        Aos animation
    ============================================*/
    var aosAnimation = function () {
        AOS.init({
            easing: "ease",
            duration: 1500,
            once: true,
            offset: 60,
            disable: 'mobile'
        });
    }
    if ($("#preLoader")) {
        setTimeout(() => {
            aosAnimation()
        }, delay);
    } else {
        aosAnimation();
    }
})

$(document).ready(function () {
    $(".limitExpire").on('click', function () {
        toastr['error']('Booking not available. Please contact support.');
    });
});
// add user email for subscription
$('.subscription-form').on('submit', function (event) {
    event.preventDefault();
    let formURL = $(this).attr('action');
    let formMethod = $(this).attr('method');

    let formData = new FormData($(this)[0]);

    $.ajax({
        url: formURL,
        method: formMethod,
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            $('input[name="email_id"]').val('');
            toastr[response.alert_type](response.message)
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "timeOut ": 10000,
                "extendedTimeOut": 10000,
                "positionClass": "toast-top-right",
            }
        },
        error: function (errorData) {
            toastr['error'](errorData.responseJSON.error.email_id[0]);
        }
    });
});


/*============================================
    Sidebar toggle
============================================*/
$(document).ready(function () {
    $(".category-toggle").on("click", function (event) {
        var li = $(this).closest("li");

        // Toggle the 'active' class for the clicked list item
        if (li.hasClass("active")) {
            li.removeClass("active");
        } else {
            li.addClass("active");
            li.siblings().removeClass("active");
        }

        event.stopPropagation();
        event.preventDefault();
    });
});




$('#roomCheckoutForm').on('submit', function (event) {
    event.preventDefault();

    let formURL = $(this).attr('action');
    let formMethod = $(this).attr('method');
    let formData = new FormData(this);

    $.ajax({
        url: formURL,
        method: formMethod,
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            window.location.href = response.redirect_url;
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                for (const key in errors) {
                    toastr['error'](errors[key][0]);
                }
            } else {
                toastr['error']('An unexpected error occurred.');
            }
        }
    });
});

$(document).ready(function () {
    $('#vendorContactForm').on('submit', function (e) {

        e.preventDefault(); // Prevent the default form submission
        $(".request-loader").addClass("show");
        $('.text-danger').text('');

        // Gather form data
        var formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'), // Form action URL
            type: 'POST', // Method type
            data: formData, // Form data
            success: function (response) {
                $('.request-loader').removeClass('show');
                location.reload();
            },
            error: function (xhr) {
                $('.request-loader').removeClass('show');
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    $.each(errors, function (key, value) {
                        $('#err_' + key).text(value[0]);
                    });
                }
            }
        });
    });
});
