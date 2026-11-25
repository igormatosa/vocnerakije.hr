document.addEventListener('DOMContentLoaded', function () {
  const navToggle = document.querySelector('.nav-toggle');
  const mainNav = document.querySelector('.main-nav');

  navToggle?.addEventListener('click', function () {
    const open = mainNav.classList.toggle('is-open');
    navToggle.setAttribute('aria-expanded', String(open));
    navToggle.setAttribute('aria-label', open ? 'Zatvori izbornik' : 'Otvori izbornik');
  });

  mainNav?.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      mainNav.classList.remove('is-open');
      navToggle?.setAttribute('aria-expanded', 'false');
    });
  });

  const contactDialog = document.querySelector('.contact-options-dialog');
  if (contactDialog && typeof contactDialog.showModal === 'function') {
    const mobilePointer = window.matchMedia('(hover: none) and (pointer: coarse)');
    document.querySelectorAll('[data-contact-options-trigger]').forEach(function (trigger) {
      trigger.addEventListener('click', function (event) {
        if (mobilePointer.matches) return;
        event.preventDefault();
        contactDialog.showModal();
        contactDialog.querySelector('[data-contact-dialog-close]').focus();
      });
    });
    contactDialog.querySelector('[data-contact-dialog-close]').addEventListener('click', function () {
      contactDialog.close();
    });
    contactDialog.addEventListener('click', function (event) {
      if (event.target === contactDialog) contactDialog.close();
    });
  }

  const productFilters = document.querySelector('[data-product-filters]');
  if (productFilters) {
    const filterButtons = [...productFilters.querySelectorAll('[data-product-filter]')];
    const productCards = [...document.querySelectorAll('.catalog-grid [data-product-categories]')];

    const applyProductFilter = function (filter) {
      filterButtons.forEach(function (button) {
        const active = button.dataset.productFilter === filter;
        button.classList.toggle('active', active);
        button.setAttribute('aria-pressed', String(active));
      });

      productCards.forEach(function (card) {
        const categories = card.dataset.productCategories.split(/\s+/).filter(Boolean);
        const visible = filter === 'varieties'
          ? !categories.includes('special-offers')
          : categories.includes(filter);
        card.hidden = !visible;
      });
    };

    filterButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        applyProductFilter(button.dataset.productFilter);
      });
    });

    applyProductFilter(productFilters.dataset.initialFilter || 'varieties');
  }

  document.querySelectorAll('[data-current-year]').forEach(function (item) {
    item.textContent = new Date().getFullYear();
  });

  const heroSlider = document.querySelector('.hero-slider');
  if (heroSlider) {
    const slides = [...heroSlider.querySelectorAll('.hero-slide')];
    const dots = [...heroSlider.querySelectorAll('.hero-slide-dots button')];
    const count = heroSlider.querySelector('.hero-slide-count');
    const caption = heroSlider.querySelector('.hero-slide-caption');
    let currentSlide = 0;
    let timer;

    const showSlide = function (index) {
      if (!slides.length) return;
      currentSlide = (index + slides.length) % slides.length;
      slides.forEach(function (slide, slideIndex) {
        const active = slideIndex === currentSlide;
        slide.classList.toggle('active', active);
        slide.setAttribute('aria-hidden', String(!active));
      });
      dots.forEach(function (dot, dotIndex) {
        const active = dotIndex === currentSlide;
        dot.classList.toggle('active', active);
        dot.setAttribute('aria-current', String(active));
      });
      count.textContent = String(currentSlide + 1).padStart(2, '0') + ' / ' + String(slides.length).padStart(2, '0');
      caption.textContent = slides[currentSlide].dataset.caption || '';
    };
    const startTimer = function () {
      window.clearInterval(timer);
      if (slides.length > 1) timer = window.setInterval(function () { showSlide(currentSlide + 1); }, 5500);
    };

    heroSlider.querySelector('.previous')?.addEventListener('click', function () { showSlide(currentSlide - 1); startTimer(); });
    heroSlider.querySelector('.next')?.addEventListener('click', function () { showSlide(currentSlide + 1); startTimer(); });
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () { showSlide(Number(dot.dataset.slide)); startTimer(); });
    });
    heroSlider.addEventListener('mouseenter', function () { window.clearInterval(timer); });
    heroSlider.addEventListener('mouseleave', startTimer);
    heroSlider.addEventListener('focusin', function () { window.clearInterval(timer); });
    heroSlider.addEventListener('focusout', startTimer);
    showSlide(0);
    startTimer();
  }

  document.querySelectorAll('.product-gallery').forEach(function (gallery) {
    const mainButton = gallery.querySelector('.gallery-main');
    const mainImage = mainButton.querySelector('img');
    if (!mainImage) return;
    const thumbnails = [...gallery.querySelectorAll('[data-gallery-index]')];
    const images = thumbnails.length ? thumbnails.map(function (thumbnail) {
      return { src: thumbnail.dataset.fullSrc, alt: thumbnail.dataset.alt };
    }) : [{ src: mainImage.src, alt: mainImage.alt }];
    let galleryIndex = thumbnails.findIndex(function (thumbnail) { return thumbnail.classList.contains('active'); });
    if (galleryIndex < 0) galleryIndex = 0;

    const selectImage = function (index) {
      galleryIndex = (index + images.length) % images.length;
      mainImage.src = images[galleryIndex].src;
      mainImage.alt = images[galleryIndex].alt;
      thumbnails.forEach(function (thumbnail, thumbnailIndex) {
        const active = thumbnailIndex === galleryIndex;
        thumbnail.classList.toggle('active', active);
        thumbnail.setAttribute('aria-current', String(active));
      });
      const activeThumbnail = thumbnails[galleryIndex];
      const title = gallery.querySelector('.gallery-slide-title');
      const description = gallery.querySelector('.gallery-slide-description');
      if (title && activeThumbnail) title.textContent = activeThumbnail.dataset.slideTitle || activeThumbnail.querySelector('span').textContent;
      if (description && activeThumbnail) description.textContent = activeThumbnail.dataset.slideDescription || '';
    };

    thumbnails.forEach(function (thumbnail) {
      thumbnail.addEventListener('click', function () { selectImage(Number(thumbnail.dataset.galleryIndex)); });
    });

    const lightbox = document.createElement('div');
    lightbox.className = 'gallery-lightbox';
    lightbox.hidden = true;
    lightbox.setAttribute('role', 'dialog');
    lightbox.setAttribute('aria-modal', 'true');
    lightbox.setAttribute('aria-label', 'Povećana fotografija proizvoda');
    lightbox.innerHTML =
      '<button class="lightbox-close" type="button" aria-label="Zatvori fotografiju">×</button>' +
      '<button class="lightbox-arrow lightbox-previous" type="button" aria-label="Prethodna fotografija">‹</button>' +
      '<figure><img src="" alt="" /><figcaption></figcaption></figure>' +
      '<button class="lightbox-arrow lightbox-next" type="button" aria-label="Sljedeća fotografija">›</button>';
    document.body.append(lightbox);

    const lightboxImage = lightbox.querySelector('figure img');
    const lightboxCaption = lightbox.querySelector('figcaption');
    const updateLightbox = function () {
      lightboxImage.src = images[galleryIndex].src;
      lightboxImage.alt = images[galleryIndex].alt;
      lightboxCaption.textContent = (galleryIndex + 1) + ' / ' + images.length + ' — ' + images[galleryIndex].alt;
    };
    const closeLightbox = function () {
      lightbox.hidden = true;
      document.body.classList.remove('lightbox-open');
      mainButton.focus();
    };
    mainButton.addEventListener('click', function () {
      updateLightbox();
      lightbox.hidden = false;
      document.body.classList.add('lightbox-open');
      lightbox.querySelector('.lightbox-close').focus();
    });
    lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
    lightbox.querySelector('.lightbox-previous').addEventListener('click', function () { selectImage(galleryIndex - 1); updateLightbox(); });
    lightbox.querySelector('.lightbox-next').addEventListener('click', function () { selectImage(galleryIndex + 1); updateLightbox(); });
    lightbox.addEventListener('click', function (event) { if (event.target === lightbox) closeLightbox(); });
    document.addEventListener('keydown', function (event) {
      if (lightbox.hidden) return;
      if (event.key === 'Escape') closeLightbox();
      if (event.key === 'ArrowLeft') { selectImage(galleryIndex - 1); updateLightbox(); }
      if (event.key === 'ArrowRight') { selectImage(galleryIndex + 1); updateLightbox(); }
    });
  });
});
