/* checkout.js 
  Maneja la lógica del proceso de pago (checkout) en el frontend.
  Envía una solicitud para crear una orden y maneja la respuesta.
*/
document.addEventListener("DOMContentLoaded", function () {
  const checkoutBtn = document.getElementById("checkoutBtn");

  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", function () {
      fetch("/orden/crear", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert("Orden creada exitosamente!");
            window.location.href = "/orden/" + data.order_id;
          } else {
            if (data.require_login) {
              window.location.href = "/?login=1";
            } else {
              alert("Error: " + data.message);
            }
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Error al procesar la orden");
        });
    });
  }
});
