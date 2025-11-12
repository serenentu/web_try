// All JavaScript functionality is now embedded in this file
        // This makes the HTML file completely self-contained
        // Product data and functionality
        
        function cartToXml(cartItems) {
            const doc = document.implementation.createDocument('', '', null);
            const cartElement = doc.createElement('cart');

            cartItems.forEach(item => {
                const itemElement = doc.createElement('item');
                Object.entries(item).forEach(([key, value]) => {
                    if (value === undefined || value === null) {
                        return;
                    }
                    const child = doc.createElement(key);
                    child.textContent = String(value);
                    itemElement.appendChild(child);
                });
                cartElement.appendChild(itemElement);
            });

            doc.appendChild(cartElement);
            return new XMLSerializer().serializeToString(doc);
        }

        function xmlToCart(xmlString) {
            if (!xmlString || typeof xmlString !== 'string') {
                return [];
            }

            const trimmed = xmlString.trim();
            if (!trimmed) return [];

            const parser = new DOMParser();
            const xml = parser.parseFromString(xmlString, 'application/xml');
            if (xml.getElementsByTagName('parsererror').length > 0) {
                return [];
            }

            const items = [];
            xml.querySelectorAll('cart > item').forEach(itemNode => {
                const item = {};
                Array.from(itemNode.children).forEach(child => {
                    const key = child.nodeName;
                    const value = child.textContent;
                    switch (key) {
                        case 'id':
                        case 'quantity':
                            item[key] = parseInt(value, 10) || 0;
                            break;
                        case 'price':
                            item[key] = parseFloat(value) || 0;
                            break;
                        case 'rating':
                        case 'reviews':
                            item[key] = Number(value) || 0;
                            break;
                        default:
                            item[key] = value;
                    }
                });
                if (item.id !== undefined && item.name && item.price !== undefined && item.quantity !== undefined) {
                    items.push(item);
                }
            });
            return items;
        }

        function getCartFromStorage() {
            const stored = localStorage.getItem('cart');
            return xmlToCart(stored);
        }

        function setCartToStorage(cartItems) {
            const xmlString = cartToXml(cartItems);
            localStorage.setItem('cart', xmlString);
        }

        let cart = getCartFromStorage();
        
        function displayProducts() {
            const grid = document.getElementById('productsGrid');
            grid.innerHTML = '';
            products.forEach(product => {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.innerHTML = `
                    <div class="product-image"><span>${product.image}</span></div>
                    <div class="product-info">
                        <h3>${product.name}</h3>
                        <p class="product-brand">${product.brand}</p>
                        <p class="product-description">${product.description}</p>
                        <p class="product-price">$${product.price.toFixed(2)}</p>
                        <div class="product-rating">
                            <span class="stars">★★★★☆</span>
                            <span>${product.rating} (${product.reviews} reviews)</span>
                        </div>
                        <button class="add-to-cart" onclick="addToCart(${product.id})">Add to Cart</button>
                    </div>
                `;
                grid.appendChild(card);
            });
        }
        
        function addToCart(input) {
            let product = null;
            let quantityToAdd = 1;

            if (typeof input === 'number') {
                product = products.find(p => p.id === input);
            } else if (input && typeof input === 'object') {
                product = input;
                if (typeof input.quantity === 'number' && input.quantity > 0) {
                    quantityToAdd = input.quantity;
                }
            }

            if (!product || typeof product.id === 'undefined') {
                return;
            }

            const existingItem = cart.find(item => item.id === product.id);

            if (existingItem) {
                existingItem.quantity += quantityToAdd;
            } else {
                const safeQuantity = quantityToAdd > 0 ? quantityToAdd : 1;
                cart.push({
                    ...product,
                    quantity: safeQuantity
                });
            }

            setCartToStorage(cart);
            updateCartDisplay();
            showCartNotification();
        }
        
        function updateCartDisplay() {
            // Update cart count
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartCount').textContent = totalItems;
            
            // Update cart total
            const cartTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            document.getElementById('cartTotal').textContent = cartTotal.toFixed(2);
            
            // Update cart items
            const cartItems = document.getElementById('cartItems');
            cartItems.innerHTML = '';
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<div class="empty-cart">Your cart is empty</div>';
                return;
            }
            
            cart.forEach(item => {
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <div class="cart-item-image">
                        <span>${item.image}</span>
                    </div>
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">$${item.price.toFixed(2)}</div>
                        <div class="cart-item-controls">
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                            <span class="quantity">${item.quantity}</span>
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                            <button class="remove-item" onclick="removeFromCart(${item.id})">Remove</button>
                        </div>
                    </div>
                `;
                cartItems.appendChild(cartItem);
            });
        }
        
        function updateQuantity(id, change) {
            const item = cart.find(item => item.id === id);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    removeFromCart(id);
                } else {
                    setCartToStorage(cart);
                    updateCartDisplay();
                }
            }
        }
        
        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            setCartToStorage(cart);
            updateCartDisplay();
        }
        
        function showCartNotification() {
            const notification = document.createElement('div');
            notification.className = 'cart-notification';
            notification.textContent = 'Item added to cart!';
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: #4CAF50;
                color: white;
                padding: 1rem 2rem;
                border-radius: 8px;
                z-index: 1002;
                animation: slideIn 0.3s ease-out;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 2000);
        }
        
        function toggleCart() {
            document.getElementById('cartSidebar').classList.toggle('open');
            document.getElementById('cartOverlay').classList.toggle('active');
        }
        
        function filterByCategory(category) {
            document.getElementById('categoryFilter').value = category;
            document.querySelector('.products-section').scrollIntoView({ behavior: 'smooth' });
            filterProducts();
        }
        
        function filterProducts() {
            let filteredProducts = [...products];
            
            // Category filter
            const selectedCategory = document.getElementById('categoryFilter').value;
            if (selectedCategory !== 'all') {
                filteredProducts = filteredProducts.filter(product => product.category === selectedCategory);
            }
            
            // Price filter
            const selectedPrice = document.getElementById('priceFilter').value;
            if (selectedPrice !== 'all') {
                const [min, max] = selectedPrice.split('-').map(p => p === '+' ? Infinity : parseFloat(p));
                filteredProducts = filteredProducts.filter(product => {
                    if (max === Infinity) return product.price >= min;
                    return product.price >= min && product.price <= max;
                });
            }
            
            // Brand filter
            const selectedBrand = document.getElementById('brandFilter').value;
            if (selectedBrand !== 'all') {
                filteredProducts = filteredProducts.filter(product => product.brand === selectedBrand);
            }
            
            // Search filter
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            if (searchTerm) {
                filteredProducts = filteredProducts.filter(product => 
                    product.name.toLowerCase().includes(searchTerm) ||
                    product.brand.toLowerCase().includes(searchTerm) ||
                    product.description.toLowerCase().includes(searchTerm)
                );
            }
            
            // Sort products
            const sortBy = document.getElementById('sortFilter').value;
            switch (sortBy) {
                case 'name':
                    filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
                    break;
                case 'price-low':
                    filteredProducts.sort((a, b) => a.price - b.price);
                    break;
                case 'price-high':
                    filteredProducts.sort((a, b) => b.price - a.price);
                    break;
                case 'rating':
                    filteredProducts.sort((a, b) => b.rating - a.rating);
                    break;
            }
            
            displayFilteredProducts(filteredProducts);
        }
        
        function displayFilteredProducts(productsToShow) {
            const grid = document.getElementById('productsGrid');
            grid.innerHTML = '';
            
            if (productsToShow.length === 0) {
                grid.innerHTML = '<div class="no-products">No products found matching your criteria.</div>';
                return;
            }
            
            productsToShow.forEach(product => {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.innerHTML = `
                    <div class="product-image"><span>${product.image}</span></div>
                    <div class="product-info">
                        <h3>${product.name}</h3>
                        <p class="product-brand">${product.brand}</p>
                        <p class="product-description">${product.description}</p>
                        <p class="product-price">$${product.price.toFixed(2)}</p>
                        <div class="product-rating">
                            <span class="stars">★★★★☆</span>
                            <span>${product.rating} (${product.reviews} reviews)</span>
                        </div>
                        <button class="add-to-cart" onclick="addToCart(${product.id})">Add to Cart</button>
                    </div>
                `;
                grid.appendChild(card);
            });
        }
        
        function checkout() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
            
            if (confirm(`Proceed to checkout with ${itemCount} items for $${total.toFixed(2)}?`)) {
                alert('Thank you for your purchase at BeautyVibe! This is a demo website.');
                cart = [];
                setCartToStorage(cart);
                updateCartDisplay();
                toggleCart();
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            displayProducts();
            updateCartDisplay(); // Initialize cart display
            
            // Add event listeners for all filters
            document.getElementById('categoryFilter').addEventListener('change', filterProducts);
            document.getElementById('priceFilter').addEventListener('change', filterProducts);
            document.getElementById('brandFilter').addEventListener('change', filterProducts);
            document.getElementById('sortFilter').addEventListener('change', filterProducts);
            document.getElementById('searchInput').addEventListener('input', filterProducts);
        });


// ---------------- CART SYSTEM ---------------- //

// Load Cart (for cart.html)
function loadCart() {
  const cartData = getCartFromStorage();
  let cartItemsDiv = document.getElementById("cartItems");
  let cartTotal = document.getElementById("cartTotal");
  if (!cartItemsDiv || !cartTotal) return;

  cartItemsDiv.innerHTML = "";
  let total = 0;

  if (cartData.length === 0) {
    cartItemsDiv.innerHTML = "<p>Your cart is empty.</p>";
    cartTotal.textContent = "0.00";
    return;
  }

  cartData.forEach(item => {
    let itemDiv = document.createElement("div");
    itemDiv.className = "cart-item";
    itemDiv.innerHTML = `<p>${item.name} - $${item.price} x ${item.quantity}</p>`;
    cartItemsDiv.appendChild(itemDiv);
    total += item.price * item.quantity;
  });

  cartTotal.textContent = total.toFixed(2);
}

// Auto-load cart on cart.html
if (document.getElementById("cartItems")) {
  loadCart();
}
