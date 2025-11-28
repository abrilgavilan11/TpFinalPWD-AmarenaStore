document.addEventListener("DOMContentLoaded", function () {
  const productCards = document.querySelectorAll(".product-card");

  // --- Lógica para cada tarjeta de producto ---
  productCards.forEach((card) => {
    const sizeButtons = card.querySelectorAll(".size-btn");
    const colorButtons = card.querySelectorAll(".color-btn");
    const addButton = card.querySelector(".product-card__add-btn");

    let selectedSize = null;
    let selectedColor = null;

    // Event listeners para los botones de talle
    sizeButtons.forEach((btn) => {
      btn.addEventListener("click", (event) => {
        // Pasamos el objeto 'event'
        sizeButtons.forEach((b) => b.classList.remove("selected"));
        event.currentTarget.classList.add("selected");
        selectedSize = event.currentTarget.dataset.size;
      });
    });

    // Event listeners para los botones de color
    colorButtons.forEach((btn) => {
      btn.addEventListener("click", (event) => {
        // Pasamos el objeto 'event'
        colorButtons.forEach((b) => b.classList.remove("selected"));
        event.currentTarget.classList.add("selected");
        selectedColor = event.currentTarget.dataset.color;
      });
    });

    // Event listener para el botón "Agregar al Carrito"
    if (addButton) {
      addButton.addEventListener("click", (event) => {
        // Pasamos el objeto 'event'
        event.preventDefault(); // Prevenimos cualquier comportamiento por defecto

        // Validamos que se haya seleccionado talle y color
        if (!selectedSize || !selectedColor) {
          showNotification("Por favor, selecciona talle y color.", "warning");
          return;
        }

        const productId = addButton.dataset.productId;
        const productName = addButton.dataset.productName;

        // Preparamos los datos para enviar al servidor
        const formData = new FormData();
        formData.append("product_id", productId);
        formData.append("quantity", 1);
        formData.append("size", selectedSize);
        formData.append("color", selectedColor);

        // Hacemos la petición al servidor usando fetch
        fetch("/carrito/agregar", {
          method: "POST",
          body: formData,
        })
          .then((response) => {
            // Si la respuesta es 401 (Unauthorized), es porque el usuario no está logueado
            if (response.status === 401) {
              return response.json().then((data) => {
                // Lanzamos un error especial que podemos capturar después
                throw { require_login: true, message: data.message };
              });
            }
            if (!response.ok) {
              // Si hay otro tipo de error, también lo manejamos
              return response.json().then((data) => {
                throw new Error(data.message || "Ocurrió un error.");
              });
            }
            return response.json();
          })
          .then((data) => {
            // Si todo fue bien, mostramos una notificación de éxito
            if (data.success) {
              showNotification(`'${productName}' fue agregado al carrito.`);
            }
          })
          .catch((error) => {
            // Aquí capturamos los errores
            if (error.require_login) {
              // Si es el error de "login requerido"
              alert(error.message); // Mostramos el alert que pediste
              const loginModal = document.getElementById("login-modal");
              if (loginModal) {
                loginModal.style.display = "flex"; // Abrimos el modal de login
              }
            } else {
              // Para cualquier otro error
              showNotification(error.message, "error");
            }
          });
      });
    }
  });

  // --- Función para mostrar notificaciones ---
  function showNotification(message, type = "success") {
    const container = document.body;
    const notification = document.createElement("div");
    notification.className = `notification notification--${type}`;
    notification.textContent = message;

    container.appendChild(notification);

    // La notificación se elimina sola después de 3 segundos
    setTimeout(() => {
      notification.style.opacity = "0";
      setTimeout(() => notification.remove(), 500);
    }, 3000);
  }
});
