/**
 * Utilidades para verificación de stock en tiempo real
 * Se puede usar en cualquier página que muestre productos
 */

class StockChecker {
  /**
   * Verifica el stock de un producto
   */
  static async check(productId, quantity = 1) {
    try {
      const response = await fetch("/admin/verificar-stock", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "product_id=" + productId + "&quantity=" + quantity,
      });

      return await response.json();
    } catch (error) {
      console.error("Error checking stock:", error);
      return { success: false, message: "Error al verificar stock" };
    }
  }

  /**
   * Verifica stock y muestra un badge con el resultado
   */
  static async displayStockBadge(productId, targetElement) {
    const data = await this.check(productId, 1);

    if (data.success) {
      let badgeClass = "bg-success";
      let text = "En Stock (" + data.current_stock + ")";

      if (data.current_stock <= 0) {
        badgeClass = "bg-danger";
        text = "Agotado";
      } else if (data.current_stock <= 5) {
        badgeClass = "bg-warning";
        text = "Stock Bajo (" + data.current_stock + ")";
      }

      targetElement.innerHTML =
        '<span class="badge ' + badgeClass + '">' + text + "</span>";
    }
  }

  /**
   * Deshabilita un botón si no hay stock
   */
  static async disableIfNoStock(productId, buttonElement) {
    const data = await this.check(productId, 1);

    if (data.success && !data.has_stock) {
      buttonElement.disabled = true;
      buttonElement.textContent = "Agotado";
      buttonElement.classList.add("btn-danger");
    }
  }
}
