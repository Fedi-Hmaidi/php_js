/**
 * Product Management Module
 * Handles all product-related operations including CRUD functions
 * Uses Cloudinary for image uploads
 */

// Function to load the Products content
function loadProductsContent() {
    document.getElementById('mainContent').innerHTML = `
        <div style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Products</h1>
                <button id="addProductBtn" style="padding: 10px 16px; background-color: #2ecc71; color: white; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center;">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i> Add Product
                </button>
            </div>

            <!-- Search Bar -->
            <div style="margin-bottom: 20px; max-width: 500px;">
                <div style="display: flex;">
                    <input type="text" id="searchProduct" placeholder="Search products..." style="padding: 8px; border-radius: 4px 0 0 4px; border: 1px solid #ddd; flex-grow: 1;">
                    <button id="searchProductBtn" style="padding: 8px 12px; background-color: #3498db; color: white; border: none; border-radius: 0 4px 4px 0; cursor: pointer;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Products List -->
            <div id="productsContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <!-- Products will be loaded here -->
                <div class="loading-products" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading products...</p>
                </div>
            </div>
        </div>

        <!-- Product Form Modal -->
        <div id="productModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
            <div style="background-color: #fefefe; margin: 10% auto; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); max-width: 700px; width: 80%;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 id="productModalTitle">Add New Product</h2>
                    <span id="closeProductModal" style="font-size: 24px; cursor: pointer;">&times;</span>
                </div>
                <form id="productForm" enctype="multipart/form-data">
                    <input type="hidden" id="product_id" name="id">
                    <input type="hidden" id="formAction" name="_method" value="POST">

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Product Name*</label>
                        <input type="text" id="name" name="name" required style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description</label>
                        <textarea id="description" name="description" rows="3" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Quantity*</label>
                            <input type="number" id="quantity" name="quantity" min="0" required style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Unit Price*</label>
                            <div style="display: flex; align-items: center;">
                                <span style="padding: 8px 12px; background-color: #f8f9fa; border: 1px solid #ddd; border-right: none; border-radius: 4px 0 0 4px;">$</span>
                                <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" required style="flex-grow: 1; padding: 8px; border-radius: 0 4px 4px 0; border: 1px solid #ddd;">
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Product Image</label>
                        <input type="file" id="image" name="image" accept="image/*" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                        <div id="imagePreviewContainer" style="margin-top: 10px; text-align: center; display: none;">
                            <img id="imagePreview" src="" alt="Image Preview" style="max-height: 200px; max-width: 100%;">
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" id="cancelProductBtn" style="padding: 8px 16px; background-color: #95a5a6; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                        <button type="submit" id="saveProductBtn" style="padding: 8px 16px; background-color: #2ecc71; color: white; border: none; border-radius: 4px; cursor: pointer;">Save Product</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteConfirmModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
            <div style="background-color: #fefefe; margin: 15% auto; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); max-width: 500px; width: 80%;">
                <h3>Confirm Delete</h3>
                <p>Are you sure you want to delete this product? This action cannot be undone.</p>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button id="cancelDeleteBtn" style="padding: 8px 16px; background-color: #95a5a6; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                    <button id="confirmDeleteBtn" style="padding: 8px 16px; background-color: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">Delete</button>
                </div>
            </div>
        </div>
    `;

    // Initialize products management
    initProductsManagement();
}

// Initialize all products related functionality
function initProductsManagement() {
    loadAllProducts();

    // Add product button event
    document.getElementById('addProductBtn').addEventListener('click', openAddProductModal);

    // Close modal events
    document.getElementById('closeProductModal').addEventListener('click', closeProductModal);
    document.getElementById('cancelProductBtn').addEventListener('click', closeProductModal);

    // Form submission
    document.getElementById('productForm').addEventListener('submit', handleProductSubmit);

    // Image preview
    document.getElementById('image').addEventListener('change', handleImagePreview);

    // Search functionality
    document.getElementById('searchProductBtn').addEventListener('click', function() {
        const searchTerm = document.getElementById('searchProduct').value;
        loadAllProducts(searchTerm);
    });

    document.getElementById('searchProduct').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const searchTerm = document.getElementById('searchProduct').value;
            loadAllProducts(searchTerm);
        }
    });

    // Delete confirmation modal events
    document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
        document.getElementById('deleteConfirmModal').style.display = 'none';
    });
}

// Load all products from the server
function loadAllProducts(search = '') {
    const productsContainer = document.getElementById('productsContainer');
    productsContainer.innerHTML = `
        <div class="loading-products" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Loading products...</p>
        </div>
    `;

    // Fetch products from the server
    fetch(`products_action.php?search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                displayProducts(data.data);
            } else {
                productsContainer.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <i class="fas fa-box-open fa-3x" style="color: #95a5a6; margin-bottom: 15px;"></i>
                        <p>No products found. ${search ? 'Try a different search term.' : 'Add your first product!'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            productsContainer.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle fa-3x" style="margin-bottom: 15px;"></i>
                    <p>Failed to load products. Please try again.</p>
                </div>
            `;
        });
}

// Display products in the UI
function displayProducts(products) {
    const productsContainer = document.getElementById('productsContainer');
    productsContainer.innerHTML = '';

    products.forEach(product => {
        const productCard = document.createElement('div');
        productCard.style = 'background-color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.3s;';
        productCard.onmouseenter = function() { this.style.transform = 'translateY(-5px)'; };
        productCard.onmouseleave = function() { this.style.transform = 'translateY(0)'; };

        productCard.innerHTML = `
            <div style="height: 200px; overflow: hidden; position: relative; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                ${product.image_url ?
                    `<img src="${product.image_url}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover;">` :
                    `<i class="fas fa-image fa-3x" style="color: #bdc3c7;"></i>`}
            </div>
            <div style="padding: 15px;">
                <h3 style="margin-top: 0; margin-bottom: 10px; font-size: 18px;">${product.name}</h3>
                <p style="margin-top: 0; margin-bottom: 10px; color: #7f8c8d; height: 40px; overflow: hidden;">${product.description || 'No description'}</p>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                    <span style="font-weight: bold; color: #2c3e50;">$${parseFloat(product.unit_price).toFixed(2)}</span>
                    <span style="color: ${parseInt(product.quantity) > 0 ? '#27ae60' : '#e74c3c'};">
                        ${parseInt(product.quantity) > 0 ? `In Stock (${product.quantity})` : 'Out of Stock'}
                    </span>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 15px; gap: 10px;">
                    <button class="edit-product-btn" data-id="${product.id}" style="padding: 6px 12px; background-color: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="delete-product-btn" data-id="${product.id}" style="padding: 6px 12px; background-color: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;

        productsContainer.appendChild(productCard);
    });

    // Add event listeners to edit and delete buttons
    document.querySelectorAll('.edit-product-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            openEditProductModal(productId);
        });
    });

    document.querySelectorAll('.delete-product-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            openDeleteConfirmation(productId);
        });
    });
}

// Open modal for adding a new product
function openAddProductModal() {
    resetProductForm();
    document.getElementById('productModalTitle').textContent = 'Add New Product';
    document.getElementById('formAction').value = 'POST';
    document.getElementById('productModal').style.display = 'block';
}

// Open modal for editing a product
function openEditProductModal(productId) {
    resetProductForm();
    document.getElementById('productModalTitle').textContent = 'Edit Product';
    document.getElementById('formAction').value = 'PUT';
    document.getElementById('product_id').value = productId;

    // Fetch product details
    fetch(`products_action.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const product = data.data;
                document.getElementById('name').value = product.name;
                document.getElementById('description').value = product.description;
                document.getElementById('quantity').value = product.quantity;
                document.getElementById('unit_price').value = product.unit_price;

                if (product.image_url) {
                    document.getElementById('imagePreviewContainer').style.display = 'block';
                    document.getElementById('imagePreview').src = product.image_url;
                }

                document.getElementById('productModal').style.display = 'block';
            } else {
                alert('Failed to load product details.');
            }
        })
        .catch(error => {
            console.error('Error fetching product details:', error);
            alert('Failed to load product details. Please try again.');
        });
}

// Open delete confirmation modal
function openDeleteConfirmation(productId) {
    document.getElementById('deleteConfirmModal').style.display = 'block';

    // Set up delete confirmation button
    document.getElementById('confirmDeleteBtn').onclick = function() {
        deleteProduct(productId);
    };
}

