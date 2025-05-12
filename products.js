/**
 * Product Management Module
 * Handles all product-related operations including CRUD functions
 * Uses Cloudinary for image uploads
 */
// Function to load the Products content

let rolee  = ""


function show() {
  const addProductBtn = document.getElementById('addProductBtn');
  if (rolee === 'admin') {
    addProductBtn.style.display = 'inline-block';
  } else {
    addProductBtn.style.display = 'none';
  }
}



function loadProductsContent() {

  const token = localStorage.getItem('token');
  if (!token) {
      console.error("Token not found in localStorage.");
      return;
  }

      const base64Url = token.split('.')[1];
      const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
      const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
          return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
      }).join(''));

      const decoded = JSON.parse(jsonPayload);
      userRole = decoded?.role || "";
      rolee = userRole
  document.getElementById("mainContent").innerHTML = `
        <div style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Products</h1>
                ${rolee === "admin" ? `
                  <button id="addProductBtn" style="padding: 10px 16px; background-color: #2ecc71; color: white; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center;">
                      <i class="fas fa-plus" style="margin-right: 8px;"></i> Add Product
                  </button>
              ` : ''}

            </div>

            <!-- Search Bar -->
            

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
      <div id="productModal">
    <style>
        #productModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        #productModal .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 700px;
            width: 80%;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close-btn {
            font-size: 24px;
            cursor: pointer;
        }

        form input[type="text"],
        form input[type="number"],
        form select,
        form textarea,
        form input[type="file"] {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            box-sizing: border-box;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .price-input {
            display: flex;
            align-items: center;
        }

        .price-input span {
            padding: 8px 12px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 4px 0 0 4px;
        }

        .price-input input {
            flex-grow: 1;
            padding: 8px;
            border-radius: 0 4px 4px 0;
            border: 1px solid #ddd;
        }

        #imagePreviewContainer {
            margin-top: 10px;
            text-align: center;
            display: none;
        }

        #imagePreview {
            max-height: 200px;
            max-width: 100%;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .modal-actions button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #cancelProductBtn {
            background-color: #95a5a6;
            color: white;
        }

        #saveProductBtn {
            background-color: #2ecc71;
            color: white;
        }
    </style>

    <div class="modal-content">
        <div class="modal-header">
            <h2 id="productModalTitle">Add New Product</h2>
            <span id="closeProductModal" class="close-btn">&times;</span>
        </div>
        <form id="productForm" enctype="multipart/form-data">
            <input type="hidden" id="product_id" name="id">
            <input type="hidden" id="_method" name="_method" value="POST">

            <div class="form-group">
                <label>Product Name*</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label>Category*</label>
                <select id="category" name="category_id" required>
                    <option value="">-- Select Category --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>

            <div class="form-row">
                <div>
                    <label>Quantity*</label>
                    <input type="number" id="quantity" name="quantity" min="0" required>
                </div>
                <div>
                    <label>Unit Price*</label>
                    <div class="price-input">
                        <span>$</span>
                        <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Product Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <div id="imagePreviewContainer">
                    <img id="imagePreview" src="" alt="Image Preview">
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" id="cancelProductBtn">Cancel</button>
                <button type="submit" id="saveProductBtn">Save Product</button>
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


  initProductsManagement();

  // Initialize products management
  //loadCategories();
  //fetch()
  show()
}


function loadCategories() {
  const categorySelect = document.getElementById("category");
  categorySelect.innerHTML = '<option value="">-- Select Category --</option>';

  fetch("categories_action.php", {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  })
    .then((response) => {
      // Check if the response is ok (status in the range 200-299)
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      // Try parsing as JSON
      return response.json();
    })
    .then((data) => {
      console.log("Received categories:", data);

      // More robust checking for valid category data
      if (data && Array.isArray(data) && data.length > 0) {
        data.forEach(category => {
          // Ensure category has an id and name
          if (category && category.id && category.name) {
            const option = document.createElement("option");
            option.value = category.id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
          }
        });

        // Check if any options were added
        if (categorySelect.options.length <= 1) {
          console.warn("No valid categories found in the response");
        }
      } else {
        console.warn("No categories data received or data is not in expected format", data);
      }
    })
    .catch((error) => {
      console.error("Error loading categories:", error);

      // Optional: Add an error option to the select
      const errorOption = document.createElement("option");
      errorOption.textContent = "Error loading categories";
      errorOption.value = "";
      categorySelect.appendChild(errorOption);
    });
}

// Initialize all products related functionality
function initProductsManagement() {
  loadAllProducts();

  // Add product button event
  document
    .getElementById("addProductBtn")
    .addEventListener("click", openAddProductModal);

  // Close modal events
  document
    .getElementById("closeProductModal")
    .addEventListener("click", closeProductModal);
  document
    .getElementById("cancelProductBtn")
    .addEventListener("click", closeProductModal);

  // Form submission
  document
    .getElementById("productForm")
    .addEventListener("submit", handleProductSubmit);

  // Image preview
  document
    .getElementById("image")
    .addEventListener("change", handleImagePreview);

  // Search functionality
  document
    .getElementById("searchProductBtn")
    .addEventListener("click", function () {
      const searchTerm = document.getElementById("searchProduct").value;
      loadAllProducts(searchTerm);
    });

  document
    .getElementById("searchProduct")
    .addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        const searchTerm = document.getElementById("searchProduct").value;
        loadAllProducts(searchTerm);
      }
    });

  // Delete confirmation modal events
  document
    .getElementById("cancelDeleteBtn")
    .addEventListener("click", function () {
      document.getElementById("deleteConfirmModal").style.display = "none";
    });

    document.getElementById('closeOrderModal').addEventListener('click', closeOrderModal);
  document.getElementById('cancelOrderBtn').addEventListener('click', closeOrderModal);

  // Quantity input event for real-time total calculation
  document.getElementById('orderQuantity').addEventListener('input', calculateOrderTotal);

  // Order submission
  document.getElementById('submitOrderBtn').addEventListener('click', submitOrder);
}

// Load all products from the server
function loadAllProducts(search = "") {
  const productsContainer = document.getElementById("productsContainer");
  productsContainer.innerHTML = `
        <div class="loading-products" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Loading products...</p>
        </div>
    `;

  // Fetch products from the server
  fetch(`products_action.php?search=${encodeURIComponent(search)}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data.length > 0) {
        displayProducts(data.data);
      } else {
        productsContainer.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <i class="fas fa-box-open fa-3x" style="color: #95a5a6; margin-bottom: 15px;"></i>
                        <p>No products found. ${
                          search
                            ? "Try a different search term."
                            : "Add your first product!"
                        }</p>
                    </div>
                `;
      }
    })
    .catch((error) => {
      console.error("Error fetching products:", error);
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
  const productsContainer = document.getElementById("productsContainer");
  productsContainer.innerHTML = "";

  products.forEach((product) => {
    const productCard = document.createElement("div");
    productCard.style =
      "background-color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.3s;";
    productCard.onmouseenter = function () {
      this.style.transform = "translateY(-5px)";
    };
    productCard.onmouseleave = function () {
      this.style.transform = "translateY(0)";
    };

    productCard.innerHTML = `
            <div style="height: 200px; overflow: hidden; position: relative; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                ${
                  product.image_url
                    ? `<img src="${product.image_url}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover;">`
                    : `<i class="fas fa-image fa-3x" style="color: #bdc3c7;"></i>`
                }
            </div>
            <div style="padding: 15px;">
                <h3 style="margin-top: 0; margin-bottom: 10px; font-size: 18px;">${
                  product.name
                }</h3>
                <p style="margin-top: 0; margin-bottom: 10px; color: #7f8c8d; height: 40px; overflow: hidden;">${
                  product.description || "No description"
                }</p>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                    <span style="font-weight: bold; color: #2c3e50;">$${parseFloat(
                      product.unit_price
                    ).toFixed(2)}</span>
                    <span style="color: ${
                      parseInt(product.quantity) > 0 ? "#27ae60" : "#e74c3c"
                    };">
                        ${
                          parseInt(product.quantity) > 0
                            ? `In Stock (${product.quantity})`
                            : "Out of Stock"
                        }
                    </span>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 15px; gap: 10px;">
                     ${rolee == "admin" ?  `   <button class="edit-product-btn" data-id="${
                      product.id
                    }" style="padding: 6px 12px; background-color: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-edit"></i> Edit
                    </button> ` : ''}

                     <button class="order-product-btn" data-id="${product.id}" style="padding: 6px 12px; background-color: #9b59b6; color: white; border: none; border-radius: 4px; cursor: pointer;">
            <i class="fas fa-cart-plus"></i> Order
        </button>
                  ${rolee == "admin" ?  ` <button class="delete-product-btn" data-id="${
                      product.id
                    }" style="padding: 6px 12px; background-color: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-trash"></i> Delete
                    </button>` : ''}
                </div>
            </div>
        `;
// Add event listeners to order buttons
document.querySelectorAll(".order-product-btn").forEach((button) => {
  button.addEventListener("click", function() {
      const productId = this.getAttribute("data-id");
      handleProductOrder(productId);
  });
});
    productsContainer.appendChild(productCard);
  });

  // Add event listeners to edit and delete buttons
  document.querySelectorAll(".edit-product-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const productId = this.getAttribute("data-id");
      openEditProductModal(productId);
    });
  });

  document.querySelectorAll(".delete-product-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const productId = this.getAttribute("data-id");
      openDeleteConfirmation(productId);
    });
  });

}

// Open modal for adding a new product
function openAddProductModal() {
 // fetch()
 loadCategories()
  resetProductForm();
  document.getElementById("productModalTitle").textContent = "Add New Product";
  document.getElementById("_method").value = "POST";
  document.getElementById("productModal").style.display = "block";
}

// Open modal for editing a product
function openEditProductModal(productId) {
  resetProductForm();
  document.getElementById("productModalTitle").textContent = "Edit Product";
  document.getElementById("_method").value = "PUT"; // Set _method to PUT for edit
  document.getElementById("product_id").value = productId;

  // Fetch product details
  fetch(`products_action.php?id=${productId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data) {
        const product = data.data;
        document.getElementById("name").value = product.name;
        document.getElementById("description").value =
          product.description || "";
        document.getElementById("quantity").value = product.quantity;
        document.getElementById("unit_price").value = product.unit_price;

        if (product.image_url) {
          document.getElementById("imagePreviewContainer").style.display =
            "block";
          document.getElementById("imagePreview").src = product.image_url;
        }

        document.getElementById("productModal").style.display = "block";
      } else {
        alert("Failed to load product details.");
      }
    })
    .catch((error) => {
      console.error("Error fetching product details:", error);
      alert("Failed to load product details. Please try again.");
    });
}

// Open delete confirmation modal
function openDeleteConfirmation(productId) {
  document.getElementById("deleteConfirmModal").style.display = "block";

  // Set up delete confirmation button
  document.getElementById("confirmDeleteBtn").onclick = function () {
    deleteProduct(productId);
  };
}

// Close product modal
function closeProductModal() {
  document.getElementById("productModal").style.display = "none";
}

// Reset product form
function resetProductForm() {
  document.getElementById("productForm").reset();
  document.getElementById("product_id").value = "";
  document.getElementById("imagePreviewContainer").style.display = "none";
  document.getElementById("imagePreview").src = "";
}

// Handle image preview
function handleImagePreview(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("imagePreviewContainer").style.display = "block";
      document.getElementById("imagePreview").src = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    document.getElementById("imagePreviewContainer").style.display = "none";
  }
}

// Handle product form submission
function handleProductSubmit(e) {
  e.preventDefault();

  const form = document.getElementById("productForm");
  const formData = new FormData(form);
  const method = formData.get("_method");

  // For debugging
  console.log("Submitting form with method:", method);
  console.log("Product ID:", formData.get("id"));

  // Show loading state
  const saveButton = document.getElementById("saveProductBtn");
  const originalText = saveButton.innerHTML;
  saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
  saveButton.disabled = true;

  // Send request to the server
  fetch("products_action.php", {
    method: "POST", // Always POST for FormData
    body: formData, // Includes _method for PUT/DELETE handling on server
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        closeProductModal();
        loadAllProducts(); // Reload products list

        // Show success message
        alert(data.message);
      } else {
        alert(data.message || "Failed to save product.");
      }
    })
    .catch((error) => {
      console.error("Error saving product:", error);
      alert("Failed to save product. Please try again.");
    })
    .finally(() => {
      // Reset button state
      saveButton.innerHTML = originalText;
      saveButton.disabled = false;
    });
}

// Delete a product
function deleteProduct(productId) {
  const formData = new FormData();
  formData.append("id", productId);
  formData.append("_method", "DELETE");

  // Show loading state
  const deleteButton = document.getElementById("confirmDeleteBtn");
  const originalText = deleteButton.innerHTML;
  deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
  deleteButton.disabled = true;

  fetch("products_action.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        document.getElementById("deleteConfirmModal").style.display = "none";
        loadAllProducts(); // Reload products list
        alert(data.message);
      } else {
        alert(data.message || "Failed to delete product.");
      }
    })
    .catch((error) => {
      console.error("Error deleting product:", error);
      alert("Failed to delete product. Please try again.");
    })
    .finally(() => {
      // Reset button state
      deleteButton.innerHTML = originalText;
      deleteButton.disabled = false;
    });
}

document.addEventListener("DOMContentLoaded", function () {
  const categorySelect = document.getElementById("category_id");

  // Loop through the categories and create <option> elements
  allCategories.forEach(function (category) {
    const option = document.createElement("option");
    option.value = category.id;
    option.textContent = category.name;
    categorySelect.appendChild(option);
  });
});


// Handle product ordering
function handleProductOrder(productId, element) {
  const quantityDialog = document.createElement('dialog');

  quantityDialog.innerHTML = `
    <style>
      dialog {
        border: none;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        font-family: Arial, sans-serif;
        width: 300px;
      }
      h3 {
        margin-top: 0;
        font-size: 18px;
        text-align: center;
      }
      input[type="number"] {
        width: 100%;
        padding: 8px;
        font-size: 16px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 4px;
      }
      .dialog-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 15px;
      }
      .dialog-buttons button {
        padding: 8px 16px;
        font-size: 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s ease;
      }
      #confirm-btn {
        background-color: #28a745;
        color: white;
      }
      #confirm-btn:hover {
        background-color: #218838;
      }
      #cancel-btn {
        background-color: #dc3545;
        color: white;
      }
      #cancel-btn:hover {
        background-color: #c82333;
      }
    </style>

    <form method="dialog">
      <h3>Enter Quantity</h3>
      <input id="quantity-input" type="number" min="1" value="1" />
      <div class="dialog-buttons">
        <button type="button" id="cancel-btn">Cancel</button>
        <button type="button" id="confirm-btn">Confirm</button>
      </div>
    </form>
  `;

  document.body.appendChild(quantityDialog);
  quantityDialog.showModal();

  const quantityInput = quantityDialog.querySelector('#quantity-input');

  quantityDialog.querySelector('#cancel-btn').addEventListener('click', () => {
    quantityDialog.close();
    document.body.removeChild(quantityDialog);
  });

  quantityDialog.querySelector('#confirm-btn').addEventListener('click', () => {
    const quantity = parseInt(quantityInput.value);
    quantityDialog.close();
    document.body.removeChild(quantityDialog);

    if (quantity && quantity > 0) {
      processOrder(productId, quantity);
    } else {
      alert('Please enter a valid quantity');
    }
  });
}

// Separate function to handle the actual order processing
function processOrder(productId, quantity) {
  // Show loading state
  const orderButton = document.querySelector(`.order-product-btn[data-id="${productId}"]`);
  if (!orderButton) {
    console.error('Order button not found');
    alert('Error: Could not process order');
    return;
  }

  const originalText = orderButton.innerHTML;
  orderButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ordering...';
  orderButton.disabled = true;

  // Prepare order data
  const formData = new FormData();
  formData.append('product_id', productId);
  formData.append('quantity', quantity);

  // Add CSRF token if your application uses it
  // formData.append('csrf_token', document.getElementById('csrf_token').value);

  // Send order request
  fetch('orders_action.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (!response.ok) {
      throw new Error(`Server responded with status: ${response.status}`);
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      alert(`Order placed successfully! Total: $${data.total}`);
      if (typeof loadAllProducts === 'function') {
        loadAllProducts(); // Refresh product list
      }
    } else {
      alert(data.message || 'Failed to place order');
    }
  })
  .catch(error => {
    console.error('Order error:', error);
    alert('Failed to place order. Please try again.');
  })
  .finally(() => {
    orderButton.innerHTML = originalText;
    orderButton.disabled = false;
  });
}

let currentOrderProduct = null;

function openOrderModal(productId) {
  // Fetch product details
  fetch(`products_action.php?id=${productId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.data) {
        currentOrderProduct = data.data;

        // Populate modal with product info
        document.getElementById('orderProductName').textContent = currentOrderProduct.name;
        document.getElementById('orderProductDescription').textContent = currentOrderProduct.description || 'No description available';
        document.getElementById('orderProductPrice').textContent = `Price: $${parseFloat(currentOrderProduct.unit_price).toFixed(2)}`;
        document.getElementById('orderProductStock').textContent = `In Stock: ${currentOrderProduct.quantity}`;

        // Reset quantity and calculate initial total
        document.getElementById('orderQuantity').value = 1;
        document.getElementById('orderQuantity').max = currentOrderProduct.quantity;
        calculateOrderTotal();

        // Show modal
        document.getElementById('orderModal').style.display = 'block';
      } else {
        alert('Failed to load product details.');
      }
    })
    .catch(error => {
      console.error('Error fetching product details:', error);
      alert('Failed to load product details. Please try again.');
    });
}

function calculateOrderTotal() {
  if (!currentOrderProduct) return;

  const quantity = parseInt(document.getElementById('orderQuantity').value) || 0;
  const maxQuantity = currentOrderProduct.quantity;

  // Validate quantity
  if (quantity <= 0) {
    document.getElementById('orderQuantity').value = 1;
    return;
  }

  if (quantity > maxQuantity) {
    document.getElementById('orderQuantity').value = maxQuantity;
    alert(`Only ${maxQuantity} units available in stock.`);
    return;
  }

  // Calculate and display total
  const total = quantity * currentOrderProduct.unit_price;
  document.getElementById('orderTotalPrice').textContent = `$${total.toFixed(2)}`;
}

function submitOrder() {
  if (!currentOrderProduct) return;

  const quantity = parseInt(document.getElementById('orderQuantity').value);
  const productId = currentOrderProduct.id;

  if (!quantity || quantity <= 0) {
    alert('Please enter a valid quantity');
    return;
  }

  // Show loading state
  const submitBtn = document.getElementById('submitOrderBtn');
  const originalText = submitBtn.innerHTML;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
  submitBtn.disabled = true;

  // Prepare order data
  const formData = new FormData();
  formData.append('product_id', productId);
  formData.append('quantity', quantity);

  // Send order request
  fetch('orders_action.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      closeOrderModal();
      alert(`Order placed successfully!\nProduct: ${currentOrderProduct.name}\nQuantity: ${quantity}\nTotal: $${data.total}`);
      loadAllProducts(); // Refresh product list
    } else {
      alert(data.message || 'Failed to place order');
    }
  })
  .catch(error => {
    console.error('Order error:', error);
    alert('Failed to place order. Please try again.');
  })
  .finally(() => {
    submitBtn.innerHTML = originalText;
    submitBtn.disabled = false;
  });
}

function closeOrderModal() {
  document.getElementById('orderModal').style.display = 'none';
  currentOrderProduct = null;
}


  // Change the order button event from:
 /* button.addEventListener("click", function() {
    const productId = this.getAttribute("data-id");
    handleProductOrder(productId);
  });*/

  // To:
/*  button.addEventListener("click", function() {
    const productId = this.getAttribute("data-id");
    openOrderModal(productId);
  });*/