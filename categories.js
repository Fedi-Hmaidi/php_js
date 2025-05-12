// categories.js

// Global variables for pagination
let currentPage = 1;
let itemsPerPage = 5;
let allCategories = [];
let filteredCategories = [];
// Load categories into the dashboard
function loadCategoriesContent() {
    document.getElementById("mainContent").innerHTML = `
        <div class="categories-container" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Categories</h1>
                <button id="addCategoryBtn" style="background-color: #4CAF50; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">
                    <i class="fa fa-plus" style="margin-right: 5px;"></i> Add Category
                </button>
            </div>

            <div class="table-controls" style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <div class="search-container" style="width: 40%;">
                    <input type="text" id="categorySearch" placeholder="Search categories..."
                           style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                </div>
                <div class="items-per-page">
                    <label for="itemsPerPageSelect">Items per page:</label>
                    <select id="itemsPerPageSelect" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <div id="categoriesTableContainer" style="margin-bottom: 20px; overflow-x: auto;">
                <!-- Table will be inserted here -->
                <p>Loading categories...</p>
            </div>

            <div class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="pagination-info">
                    Showing <span id="showingFrom">0</span> to <span id="showingTo">0</span> of <span id="totalItems">0</span> categories
                </div>
                <div class="pagination-buttons" style="display: flex; gap: 5px;">
                    <button id="firstPageBtn" style="padding: 5px 10px; border-radius: 4px; border: 1px solid #ddd; cursor: pointer;">⟨⟨</button>
                    <button id="prevPageBtn" style="padding: 5px 10px; border-radius: 4px; border: 1px solid #ddd; cursor: pointer;">⟨</button>
                    <div id="pageNumbers" style="display: flex; gap: 5px;">
                        <!-- Page numbers will be generated here -->
                    </div>
                    <button id="nextPageBtn" style="padding: 5px 10px; border-radius: 4px; border: 1px solid #ddd; cursor: pointer;">⟩</button>
                    <button id="lastPageBtn" style="padding: 5px 10px; border-radius: 4px; border: 1px solid #ddd; cursor: pointer;">⟩⟩</button>
                </div>
            </div>
        </div>

        <!-- Modal for adding a new category -->
        <div id="categoryModal" class="modal" style="display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
            <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Add New Category</h2>
                    <span class="close" onclick="closeCategoryModal()" style="color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="categoryName" style="display: block; margin-bottom: 5px; font-weight: bold;">Name:</label>
                    <input type="text" id="categoryName" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label for="categoryDescription" style="display: block; margin-bottom: 5px; font-weight: bold;">Description:</label>
                    <textarea id="categoryDescription" style="width: 100%; height: 100px; padding: 8px; border-radius: 4px; border: 1px solid #ddd;"></textarea>
                </div>
                <div style="text-align: right;">
                    <button onclick="closeCategoryModal()" style="padding: 8px 16px; margin-right: 10px; border-radius: 4px; border: 1px solid #ddd; background-color: #f8f8f8; cursor: pointer;">Cancel</button>
                    <button onclick="submitCategory()" style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Submit</button>
                </div>
            </div>
        </div>
    `;

    // Attach event listeners after DOM elements are created
    document.getElementById('addCategoryBtn').addEventListener('click', openCategoryModal);
    document.getElementById('categorySearch').addEventListener('input', handleSearch);
    document.getElementById('itemsPerPageSelect').addEventListener('change', handleItemsPerPageChange);
    document.getElementById('firstPageBtn').addEventListener('click', () => goToPage(1));
    document.getElementById('prevPageBtn').addEventListener('click', () => goToPage(currentPage - 1));
    document.getElementById('nextPageBtn').addEventListener('click', () => goToPage(currentPage + 1));
    document.getElementById('lastPageBtn').addEventListener('click', () => goToPage(getTotalPages()));

    // Fetch categories data
    fetchCategories();
}

// Fetch categories from the server
 function fetchCategories() {
    fetch("categories_action.php")
        .then((response) => response.json())
        .then((data) => {
            if (Array.isArray(data)) {
                allCategories = data;
                filteredCategories = [...allCategories];
                currentPage = 1;
                renderTable();
                updatePagination();
            } else if (data.message) {
                document.getElementById("categoriesTableContainer").innerHTML = `<p>${data.message}</p>`;
            } else {
                document.getElementById("categoriesTableContainer").innerHTML = `<p>Unexpected response.</p>`;
            }
        })
        .catch((error) => {
            document.getElementById("categoriesTableContainer").innerHTML = "<p>Error loading categories.</p>";
            console.error("Error:", error);
        });
}

// Render the categories table with current page data
function renderTable() {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const currentPageData = filteredCategories.slice(startIndex, endIndex);

    let tableHtml = `
        <table style="width: 100%; border-collapse: collapse; border-radius: 8px; overflow: hidden; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #ddd;">ID</th>
                    <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #ddd;">Name</th>
                    <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #ddd;">Description</th>
                    <th style="padding: 12px 15px; text-align: center; border-bottom: 2px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    if (currentPageData.length === 0) {
        tableHtml += `
            <tr>
                <td colspan="4" style="padding: 12px 15px; text-align: center;">No categories found</td>
            </tr>
        `;
    } else {
        currentPageData.forEach((cat, index) => {
            const rowStyle = index % 2 === 0 ? 'background-color: #ffffff;' : 'background-color: #f8f8f8;';
            tableHtml += `
                <tr style="${rowStyle} transition: background-color 0.3s;" onmouseover="this.style.backgroundColor='#f1f1f1'" onmouseout="this.style.backgroundColor='${index % 2 === 0 ? '#ffffff' : '#f8f8f8'}'">
                    <td style="padding: 12px 15px; border-bottom: 1px solid #ddd;">${cat.id}</td>
                    <td style="padding: 12px 15px; border-bottom: 1px solid #ddd;">${cat.name}</td>
                    <td style="padding: 12px 15px; border-bottom: 1px solid #ddd;">${cat.description}</td>
                    <td style="padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: center;">
                        <button onclick="editCategory(${cat.id})" style="margin-right: 5px; padding: 6px 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Edit
                        </button>
                        <button onclick="deleteCategory(${cat.id})" style="padding: 6px 12px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Delete
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    tableHtml += `
            </tbody>
        </table>
    `;

    document.getElementById("categoriesTableContainer").innerHTML = tableHtml;

    // Update showing info
    const from = filteredCategories.length === 0 ? 0 : startIndex + 1;
    const to = Math.min(endIndex, filteredCategories.length);
    document.getElementById("showingFrom").textContent = from;
    document.getElementById("showingTo").textContent = to;
    document.getElementById("totalItems").textContent = filteredCategories.length;
}

// Handle search input
function handleSearch(event) {
    const searchTerm = event.target.value.toLowerCase();

    if (searchTerm === '') {
        filteredCategories = [...allCategories];
    } else {
        filteredCategories = allCategories.filter(cat =>
            cat.name.toLowerCase().includes(searchTerm) ||
            cat.description.toLowerCase().includes(searchTerm)
        );
    }

    currentPage = 1; // Reset to first page when searching
    renderTable();
    updatePagination();
}

// Handle items per page change
function handleItemsPerPageChange(event) {
    itemsPerPage = parseInt(event.target.value);
    currentPage = 1; // Reset to first page when changing items per page
    renderTable();
    updatePagination();
}

// Calculate total number of pages
function getTotalPages() {
    return Math.ceil(filteredCategories.length / itemsPerPage);
}

// Navigate to a specific page
function goToPage(page) {
    const totalPages = getTotalPages();
    if (page < 1) page = 1;
    if (page > totalPages) page = totalPages;

    if (currentPage !== page) {
        currentPage = page;
        renderTable();
        updatePagination();
    }
}

// Update pagination controls
function updatePagination() {
    const totalPages = getTotalPages();
    const pageNumbersContainer = document.getElementById('pageNumbers');
    pageNumbersContainer.innerHTML = '';

    // Determine range of page numbers to show
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);

    // Adjust if we're near the end
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }

    // Create page number buttons
    for (let i = startPage; i <= endPage; i++) {
        const pageButton = document.createElement('button');
        pageButton.textContent = i;
        pageButton.style.padding = '5px 10px';
        pageButton.style.borderRadius = '4px';
        pageButton.style.cursor = 'pointer';

        if (i === currentPage) {
            pageButton.style.backgroundColor = '#007bff';
            pageButton.style.color = 'white';
            pageButton.style.border = '1px solid #007bff';
        } else {
            pageButton.style.backgroundColor = 'white';
            pageButton.style.border = '1px solid #ddd';
        }

        pageButton.addEventListener('click', () => goToPage(i));
        pageNumbersContainer.appendChild(pageButton);
    }

    // Disable/enable navigation buttons
    document.getElementById('firstPageBtn').disabled = currentPage === 1;
    document.getElementById('prevPageBtn').disabled = currentPage === 1;
    document.getElementById('nextPageBtn').disabled = currentPage === totalPages;
    document.getElementById('lastPageBtn').disabled = currentPage === totalPages;

    // Update button styles based on disabled state
    ['firstPageBtn', 'prevPageBtn', 'nextPageBtn', 'lastPageBtn'].forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn.disabled) {
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
        } else {
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
        }
    });
}

// Edit category logic - enhanced with modal
function editCategory(id) {
    // Find the category to edit
    const category = allCategories.find(cat => cat.id === id);
    if (!category) return;

    // Create and show edit modal
    const editModalHtml = `
        <div id="editCategoryModal" class="modal" style="display: block; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
            <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Edit Category</h2>
                    <span class="close" onclick="closeEditModal()" style="color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="editCategoryName" style="display: block; margin-bottom: 5px; font-weight: bold;">Name:</label>
                    <input type="text" id="editCategoryName" value="${category.name}" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label for="editCategoryDescription" style="display: block; margin-bottom: 5px; font-weight: bold;">Description:</label>
                    <textarea id="editCategoryDescription" style="width: 100%; height: 100px; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">${category.description}</textarea>
                </div>
                <div style="text-align: right;">
                    <button onclick="closeEditModal()" style="padding: 8px 16px; margin-right: 10px; border-radius: 4px; border: 1px solid #ddd; background-color: #f8f8f8; cursor: pointer;">Cancel</button>
                    <button onclick="updateCategory(${id})" style="padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Update</button>
                </div>
            </div>
        </div>
    `;

    // Add modal to the body
    const modalDiv = document.createElement('div');
    modalDiv.id = 'tempEditModalContainer';
    modalDiv.innerHTML = editModalHtml;
    document.body.appendChild(modalDiv);

    // Define close function in global scope
    window.closeEditModal = function() {
        const modalContainer = document.getElementById('tempEditModalContainer');
        if (modalContainer) {
            document.body.removeChild(modalContainer);
        }
    };

    // Define update function in global scope
    window.updateCategory = function(id) {
        const name = document.getElementById('editCategoryName').value.trim();
        const description = document.getElementById('editCategoryDescription').value.trim();

        if (!name || !description) {
            alert("Please fill in all fields.");
            return;
        }

        fetch("categories_action.php", {
            method: "PUT",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${id}&name=${encodeURIComponent(name)}&description=${encodeURIComponent(description)}`,
        })
        .then((res) => res.json())
        .then((data) => {
            alert(data.message);
            if (data.success) {
                closeEditModal();
                fetchCategories(); // Reload all categories
            }
        })
        .catch((err) => {
            console.error("Edit error:", err);
            alert("An error occurred while updating the category.");
        });
    };
}

// Delete category logic - enhanced
function deleteCategory(id) {
    // Create a confirmation modal instead of using browser's confirm
    const confirmModalHtml = `
        <div id="confirmDeleteModal" class="modal" style="display: block; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
            <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 40%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                <h2 style="margin-top: 0;">Confirm Deletion</h2>
                <p>Are you sure you want to delete this category? This action cannot be undone.</p>
                <div style="text-align: right; margin-top: 20px;">
                    <button onclick="closeDeleteModal()" style="padding: 8px 16px; margin-right: 10px; border-radius: 4px; border: 1px solid #ddd; background-color: #f8f8f8; cursor: pointer;">Cancel</button>
                    <button onclick="confirmDelete(${id})" style="padding: 8px 16px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Delete</button>
                </div>
            </div>
        </div>
    `;

    // Add modal to the body
    const modalDiv = document.createElement('div');
    modalDiv.id = 'tempDeleteModalContainer';
    modalDiv.innerHTML = confirmModalHtml;
    document.body.appendChild(modalDiv);

    // Define close function in global scope
    window.closeDeleteModal = function() {
        const modalContainer = document.getElementById('tempDeleteModalContainer');
        if (modalContainer) {
            document.body.removeChild(modalContainer);
        }
    };

    // Define confirm delete function in global scope
    window.confirmDelete = function(id) {
        fetch("categories_action.php", {
            method: "DELETE",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${id}`,
        })
        .then((res) => res.json())
        .then((data) => {
            closeDeleteModal();
            alert(data.message);
            if (data.success) {
                fetchCategories(); // Reload all categories
            }
        })
        .catch((err) => {
            closeDeleteModal();
            console.error("Delete error:", err);
            alert("An error occurred while deleting the category.");
        });
    };
}

// Open modal function
function openCategoryModal() {
    document.getElementById('categoryModal').style.display = 'block';
}

// Close modal function
function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
    document.getElementById('categoryName').value = ''; // Clear input fields
    document.getElementById('categoryDescription').value = ''; // Clear textarea
}

// Submit category function
function submitCategory() {
    const name = document.getElementById('categoryName').value.trim();
    const description = document.getElementById('categoryDescription').value.trim();

    if (!name || !description) {
        alert("Please fill in all fields.");
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('description', description);

    fetch('categories_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            closeCategoryModal(); // Close modal after successful submission
            fetchCategories(); // Refresh categories list
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred.");
    });
}

// Add event listener when DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initial load of categories content
    loadCategoriesContent();
});


//export const test = allCategories
