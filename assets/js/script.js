$(document).ready(function () {

  /* ===== Sticky Header ===== */
  $(window).on('scroll', function () {
    let scrollThreshold = 50;
    if ($(this).scrollTop() > scrollThreshold) {
      $('header').addClass('sticky');
    } else {
      $('header').removeClass('sticky');
    }
  });

  const isDesktop = () => window.innerWidth >= 992;

  /* =========================
     MOBILE MENU TOGGLE
  ========================= */
  $('#mobileToggle').on('click', function () {

    const isOpen = $('.custom-nav').toggleClass('open').hasClass('open');

    $(this).toggleClass('active', isOpen);
    $('#navOverlay').toggleClass('show', isOpen);

    if (!isOpen) {
      $('.custom-dropdown').css('max-height', '');
      $('.has-dropdown').removeClass('active');
    }

  });

  /* =========================
     DROPDOWN CLICK (MOBILE)
  ========================= */
  $('.dropdown-toggle-js').on('click', function (e) {

    if (isDesktop()) return;

    e.preventDefault();

    const parent = $(this).closest('.has-dropdown');
    const dropdown = parent.find('.custom-dropdown')[0];

    // close other dropdowns
    $('.has-dropdown').not(parent).each(function () {
      $(this).removeClass('active');
      $(this).find('.custom-dropdown').css('max-height', null);
    });

    parent.toggleClass('active');

    if (parent.hasClass('active')) {
      dropdown.style.maxHeight = dropdown.scrollHeight + "px";
      $('#navOverlay').addClass('show');
    } else {
      dropdown.style.maxHeight = null;
    }
  });

  /* =========================
     DROPDOWN HOVER (DESKTOP)
  ========================= */
  $('.has-dropdown').on('mouseenter', function () {
    if (!isDesktop()) return;

    $('.has-dropdown').removeClass('active');
    $('.custom-dropdown').css('max-height', null);

    $(this).addClass('active');
    $('#navOverlay').addClass('show');
  });

  $('.has-dropdown').on('mouseleave', function () {
    if (!isDesktop()) return;

    $(this).removeClass('active');

    if ($('.has-dropdown.active').length === 0) {
      $('#navOverlay').removeClass('show');
    }
  });

  /* =========================
     OVERLAY CLICK
  ========================= */
  $('#navOverlay').on('click', function () {
    $(this).removeClass('show');
    $('.custom-nav').removeClass('open');
    $('#mobileToggle').removeClass('active');
    $('.has-dropdown').removeClass('active');
    $('.custom-dropdown').css('max-height', '');
  });

  /* =========================
     RESIZE FIX
  ========================= */
  $(window).on('resize', function () {
    $('.has-dropdown').removeClass('active');
    $('.custom-dropdown').css('max-height', '');
    $('#navOverlay').removeClass('show');
  });

});

const navLinks = document.querySelectorAll(".nav-list.has-dropdown > .nav-link");

navLinks.forEach(link => {
  link.addEventListener("click", function (e) {

    // 👉 only mobile
    if (window.innerWidth > 992) return;

    const dropdown = this.nextElementSibling;

    // 👉 agar next element dropdown nahi hai
    if (!dropdown || !dropdown.classList.contains("custom-dropdown")) return;

    e.preventDefault();

    const isOpen =
      dropdown.style.maxHeight &&
      dropdown.style.maxHeight !== "0px";

    /* 🔒 close all other open dropdowns */
    document.querySelectorAll(".custom-dropdown").forEach(dd => {
      if (dd !== dropdown) {
        dd.style.maxHeight = null;
      }
    });

    /* 🔁 toggle current dropdown */
    if (isOpen) {
      dropdown.style.maxHeight = null;
    } else {
      dropdown.style.maxHeight = dropdown.scrollHeight + "px";
    }
  });
});

let scrollBtn = document.getElementById("scroll-top");

window.addEventListener("scroll", function () {
  if (!scrollBtn) return;
  if (window.scrollY > 300) {
    scrollBtn.classList.add("show");
  } else {
    scrollBtn.classList.remove("show");
  }
});

// Scroll to top on click
if (scrollBtn) {
  scrollBtn.addEventListener("click", function (e) {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: "smooth"
    });
  });
}
document.addEventListener("DOMContentLoaded", function () {

  const modalEl = document.getElementById("fraudModal");
  if (!modalEl) return;

  // Show modal ONLY if not closed before
  if (!sessionStorage.getItem("fraudModalClosed")) {

    setTimeout(() => {

      const modal = new bootstrap.Modal(modalEl, {
        backdrop: "static",
        keyboard: true
      });

      modal.show();

      // Accessibility: focus close button
      modalEl.addEventListener("shown.bs.modal", () => {
        modalEl.querySelector(".btn-close").focus();
      });

    }, 1200); // delay
  }

  // ✅ Set sessionStorage ONLY when modal is closed
  modalEl.addEventListener("hidden.bs.modal", () => {
    sessionStorage.setItem("fraudModalClosed", "true");
  });

});
