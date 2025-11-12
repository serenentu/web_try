// BV scripts: product data + filters + sorting 


(function(){
  // If a page doesn't have a products section, skip attaching logic
  const grid = document.getElementById("productsGrid");
  if (!grid) return;

  // Provide a fallback dataset if none exists
  // IMPORTANT: IDs must match database product IDs exactly
  const defaultProducts = [
    { id: 1,  name: "Velvet Matte Lipstick",        category: "lips", price: 19.90, image: "product pictures/Matte-Lipstick.png" },
    { id: 2,  name: "Hydra Glow Foundation",        category: "face", price: 34.50, image: "product pictures/Foundation.png.webp" },
    { id: 3,  name: "Precision Liquid Liner",       category: "eyes", price: 14.00, image: "product pictures/Liquid-Liner.jpg.webp" },
    { id: 4,  name: "Cream Blush Stick",            category: "face", price: 22.00, image: "product pictures/Blush-Stick.jpg.webp" },
    { id: 5,  name: "Plumping Lip Gloss",           category: "lips", price: 16.50, image: "product pictures/Lip-Gloss.png.webp" },
    { id: 6,  name: "Eyeshadow Palette",            category: "eyes", price: 28.00, image: "product pictures/Eyeshadow-Palette.jpg.webp" },
    { id: 7,  name: "Mineral Loose Powder",         category: "face", price: 18.90, image: "product pictures/Loose-Setting-Powder.jpg.webp" },
    { id: 8,  name: "Dewy Primer",                  category: "face", price: 29.90, image: "product pictures/Dewy-Primer.png.webp" },
    { id: 9,  name: "Brow Gel",                     category: "eyes", price: 12.50, image: "product pictures/Brow-Gel.png.webp" },
    { id: 10, name: "Lip Care Balm",                category: "lips", price:  9.90, image: "product pictures/Lip-Care-Balm.jpg.webp" },
    { id: 11, name: "Liquid Luminizer",             category: "face", price: 25.00, image: "product pictures/Liquid-Luminizer.jpg.webp" }
  ];
  // If window.catalog?.products exists, use it; else default
  const products = (window.catalog && Array.isArray(window.catalog.products) && window.catalog.products.length)
    ? window.catalog.products : defaultProducts;

  // State
  const activeCategories = new Set(["lips","face","eyes"]);
  let sortMode = "price-asc";
  let searchTerm = "";

  // Elements
  const empty  = document.getElementById("bv-empty");
  const countEl= document.getElementById("bv-count");
  const sortEl = document.getElementById("bv-sort");
  const searchInput = document.getElementById("SearchInput");
  const chipEls= Array.from(document.querySelectorAll(".bv-chip"));

  const currency = new Intl.NumberFormat(undefined, { style: "currency", currency: guessCurrency() });

  function guessCurrency() {
    try {
      const region = (Intl.DateTimeFormat().resolvedOptions().locale || "en-US").split("-")[1] || "US";
      const map = { SG: "SGD", US: "USD", GB: "GBP", EU: "EUR", MY: "MYR", AU: "AUD", CA: "CAD" };
      return map[region] || "USD";
    } catch { return "USD"; }
  }

  function escapeHtml(str) {
    return str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]));
  }

  function renderCard(p) {
    const safeName = escapeHtml(p.name);
    const price = currency.format(p.price);
    const catLabel = p.category.charAt(0).toUpperCase() + p.category.slice(1);
    return `
      <article class="bv-card" data-id="${p.id}" data-category="${p.category}">
        <div class="bv-thumb">${p.image ? `<img src="${p.image}" alt="${safeName}">` : ""}</div>
        <div class="bv-body">
          <div class="bv-title">${safeName}</div>
          <div class="bv-meta">
            <span class="bv-badge" aria-label="Category">${catLabel}</span>
            <span class="bv-price" aria-label="Price">${price}</span>
          </div>
        </div>
        <div class="bv-actions">
          <button type="button" aria-label="Add ${safeName} to cart">Add to Cart</button>
        </div>
      </article>
    `;
  }

  function apply() {
    grid.setAttribute("aria-busy","true");
    const normalizedSearch = searchTerm.trim().toLowerCase();
    const filtered = products.filter((p) => {
      if (!activeCategories.has(p.category)) return false;
      if (!normalizedSearch) return true;
      return p.name.toLowerCase().includes(normalizedSearch);
    });
    const sorted = [...filtered].sort((a,b) => {
      switch (sortMode) {
        case "price-asc":  return a.price - b.price;
        case "price-desc": return b.price - a.price;
        case "alpha-asc":  return a.name.localeCompare(b.name);
        case "alpha-desc": return b.name.localeCompare(a.name);
        default: return 0;
      }
    });

    grid.innerHTML = sorted.map(renderCard).join("");
    empty && (empty.hidden = sorted.length !== 0);
    countEl && (countEl.textContent = `${sorted.length} product${sorted.length === 1 ? "" : "s"}`);

    // Hook up add-to-cart
    grid.querySelectorAll(".bv-card").forEach((el) => {
      const btn = el.querySelector(".bv-actions button");
      // Get product ID from the card's data-id attribute (more reliable than array index)
      const productId = el.getAttribute('data-id');
      if (!productId) return; // Skip if no data-id found
      
      // Remove any existing event listeners by cloning the button
      const newBtn = btn.cloneNode(true);
      btn.parentNode.replaceChild(newBtn, btn);
      
      newBtn.addEventListener("click", () => {
        // Create a form and submit to add_to_cart.php
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'add_to_cart.php';
        
        const productIdInput = document.createElement('input');
        productIdInput.type = 'hidden';
        productIdInput.name = 'product_id';
        productIdInput.value = productId;
        
        const quantityInput = document.createElement('input');
        quantityInput.type = 'hidden';
        quantityInput.name = 'quantity';
        quantityInput.value = 1;
        
        form.appendChild(productIdInput);
        form.appendChild(quantityInput);
        document.body.appendChild(form);
        
        // Show loading feedback
        newBtn.disabled = true;
        const originalText = newBtn.textContent;
        newBtn.textContent = 'Adding...';
        
        // Submit form (this will redirect to cart.php after adding)
        form.submit();
      });
    });

    grid.removeAttribute("aria-busy");
  }

  // Events
  chipEls.forEach(btn => {
    btn.addEventListener("click", () => {
      const cat = btn.dataset.cat;
      const on  = btn.getAttribute("aria-pressed") === "true";
      if (on) { activeCategories.delete(cat); } else { activeCategories.add(cat); }
      btn.setAttribute("aria-pressed", String(!on));
      apply();
    });
  });

  sortEl && sortEl.addEventListener("change", () => {
    sortMode = sortEl.value;
    apply();
  });

  if (searchInput) {
    const handleSearch = (value) => {
      searchTerm = value;
      apply();
    };

    searchInput.addEventListener("input", (event) => {
      handleSearch(event.target.value.toLowerCase());
    });

    searchInput.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
        handleSearch(event.target.value.toLowerCase());
      }
    });
  }

  // Init
  apply();

  // Expose
  window.catalog = Object.assign({}, window.catalog || {}, { products, apply });
})();

// Define addToCart function for productfilters.js (legacy support)
// Note: Add to cart is now handled directly in the click handler above
// This is kept for backward compatibility if other scripts call it
if (typeof window.addToCart !== "function") {
  window.addToCart = function(product) {
    // Create a form and submit to add_to_cart.php
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'add_to_cart.php';
    
    const productIdInput = document.createElement('input');
    productIdInput.type = 'hidden';
    productIdInput.name = 'product_id';
    productIdInput.value = product.id;
    
    const quantityInput = document.createElement('input');
    quantityInput.type = 'hidden';
    quantityInput.name = 'quantity';
    quantityInput.value = 1;
    
    form.appendChild(productIdInput);
    form.appendChild(quantityInput);
    document.body.appendChild(form);
    form.submit();
  };
}
