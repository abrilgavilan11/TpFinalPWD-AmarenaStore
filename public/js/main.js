document.addEventListener('DOMContentLoaded', function () {
  // === MENÚ DE USUARIO ===
  const userMenuButton = document.querySelector('.navbar__user-link');
  const userMenu = document.querySelector('.navbar__user-menu');

  if (userMenuButton && userMenu) {
    userMenuButton.addEventListener('click', function (event) {
      // Previene que el enlace navegue si es un <a> con href="#"
      event.preventDefault();
      // Alterna la visibilidad del menú
      userMenu.style.display =
        userMenu.style.display === 'block' ? 'none' : 'block';
      // Detiene la propagación para que el click no llegue al 'document'
      event.stopPropagation();
    });

    // Cierra el menú si se hace clic fuera de él
    document.addEventListener('click', function (event) {
      // Comprueba si el menú está visible y si el clic fue fuera del botón y del menú
      if (
        userMenu.style.display === 'block' &&
        !userMenuButton.contains(event.target)
      ) {
        userMenu.style.display = 'none';
      }
    });
  }

  // === MENÚ DESPLEGABLE DE CATÁLOGO ===
  const catalogDropdown = document.querySelector('.navbar__dropdown');
  const dropdownToggle = document.querySelector('.dropdown-toggle');
  const dropdownMenu = document.querySelector('.dropdown-menu');

  if (catalogDropdown && dropdownToggle && dropdownMenu) {
    let isDropdownOpen = false;
    let dropdownTimeout;

    // Función para mostrar el dropdown
    function showDropdown() {
      clearTimeout(dropdownTimeout);
      isDropdownOpen = true;
      dropdownMenu.style.display = 'block';
      dropdownMenu.style.opacity = '0';
      dropdownMenu.style.transform = 'translateY(-10px)';

      // Animación suave
      setTimeout(() => {
        dropdownMenu.style.transition = 'all 0.3s ease-out';
        dropdownMenu.style.opacity = '1';
        dropdownMenu.style.transform = 'translateY(0)';
      }, 10);
    }

    // Función para ocultar el dropdown
    function hideDropdown() {
      dropdownTimeout = setTimeout(() => {
        isDropdownOpen = false;
        if (dropdownMenu.style.display === 'block') {
          dropdownMenu.style.opacity = '0';
          dropdownMenu.style.transform = 'translateY(-10px)';

          setTimeout(() => {
            if (!isDropdownOpen) {
              dropdownMenu.style.display = 'none';
            }
          }, 300);
        }
      }, 150); // Delay para permitir hover entre elementos
    }

    // Event listeners para hover
    catalogDropdown.addEventListener('mouseenter', showDropdown);
    catalogDropdown.addEventListener('mouseleave', hideDropdown);

    // Click en móviles para toggle
    dropdownToggle.addEventListener('click', function (event) {
      // Solo prevenir default si no está navegando
      if (
        event.target.tagName !== 'A' ||
        event.target.getAttribute('href') === '#'
      ) {
        event.preventDefault();

        if (window.innerWidth <= 768) {
          if (isDropdownOpen) {
            hideDropdown();
          } else {
            showDropdown();
          }
        }
      }
    });

    // Cerrar dropdown al hacer click fuera (móvil)
    document.addEventListener('click', function (event) {
      if (
        window.innerWidth <= 768 &&
        !catalogDropdown.contains(event.target) &&
        isDropdownOpen
      ) {
        hideDropdown();
      }
    });
  }

  // === ANIMACIONES DE HOVER PARA ITEMS DEL DROPDOWN ===
  const dropdownItems = document.querySelectorAll('.dropdown-item');

  dropdownItems.forEach((item) => {
    item.addEventListener('mouseenter', function () {
      this.style.transform = 'translateX(4px)';
    });

    item.addEventListener('mouseleave', function () {
      this.style.transform = 'translateX(0)';
    });
  });

  // === INDICADOR VISUAL DE CATEGORÍA ACTIVA ===
  // Obtener parámetros de la URL para marcar la categoría activa
  const urlParams = new URLSearchParams(window.location.search);
  const currentCategory = urlParams.get('category');

  if (currentCategory) {
    dropdownItems.forEach((item) => {
      const href = item.getAttribute('href');
      if (href && href.includes(`category=${currentCategory}`)) {
        item.style.backgroundColor = 'rgba(217, 106, 126, 0.15)';
        item.style.color = 'var(--color-primary-dark)';
        item.style.fontWeight = '700';
      }
    });
  }
});
