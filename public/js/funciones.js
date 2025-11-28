// ===============================
// FUNCIONES GENERALES
// ===============================

document.addEventListener("DOMContentLoaded", function () {
  // Inicializar carrusel de productos destacados (solo en home)
  if (document.querySelector(".featured-carousel")) {
    initFeaturedProductsCarousel();
  }

  // Inicializar FAQ accordion (solo en home)
  if (document.querySelector(".faq-accordion")) {
    initFAQAccordion();
  }
});

// ===============================
// CARRUSEL DE PRODUCTOS DESTACADOS
// ===============================

function initFeaturedProductsCarousel() {
  const carousel = document.querySelector(".featured-carousel");
  if (!carousel) return;

  const track = carousel.querySelector(".featured-carousel__track");
  const slides = carousel.querySelectorAll(".featured-carousel__slide");
  const prevBtn = carousel.querySelector(".featured-carousel__btn--left");
  const nextBtn = carousel.querySelector(".featured-carousel__btn--right");

  if (!track || slides.length === 0) return;

  track.style.width = `${slides.length * 100}%`;
  slides.forEach((slide) => {
    slide.style.width = `${100 / slides.length}%`;
  });

  let currentIndex = 0;

  function updateCarousel() {
    const translateX = -((currentIndex * 100) / slides.length);
    track.style.transform = `translateX(${translateX}%)`;
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % slides.length;
    updateCarousel();
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    updateCarousel();
  }

  if (prevBtn) prevBtn.addEventListener("click", prevSlide);
  if (nextBtn) nextBtn.addEventListener("click", nextSlide);

  updateCarousel();
}

// ===============================
// FAQ ACCORDION
// ===============================

function initFAQAccordion() {
  const faqItems = document.querySelectorAll(".faq-item");

  faqItems.forEach((item) => {
    const question = item.querySelector(".faq-question");
    const answer = item.querySelector(".faq-answer");
    const icon = item.querySelector(".faq-icon");

    if (!question || !answer || !icon) return;

    question.addEventListener("click", function () {
      const isActive = item.classList.contains("active");

      // Cerrar otros items
      faqItems.forEach((otherItem) => {
        if (otherItem !== item) {
          otherItem.classList.remove("active");
          const otherAnswer = otherItem.querySelector(".faq-answer");
          if (otherAnswer) otherAnswer.classList.remove("active");
        }
      });

      // Toggle del item actual
      if (isActive) {
        item.classList.remove("active");
        answer.classList.remove("active");
      } else {
        item.classList.add("active");
        answer.classList.add("active");
      }
    });
  });
}

// ===============================
// NOTIFICACIONES
// ===============================

function showNotification(message, type = "success") {
  const existingNotification = document.querySelector(".notification");
  if (existingNotification) {
    existingNotification.remove();
  }

  const notification = document.createElement("div");
  notification.className = `notification notification--${type}`;
  notification.textContent = message;
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.animation = "slideOutRight 0.4s ease";
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 400);
  }, 3000);
}

// Agregar animación de salida
const style = document.createElement("style");
style.textContent = `
  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);
