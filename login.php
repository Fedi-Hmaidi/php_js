<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .form-group label {
            font-weight: bold;
        }

        .btn-login {
            width: 100%;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            text-align: center;
        }

        .loader {
            display: none;
            color: green;
            font-weight: bold;
            text-align: center;
        }

        .login-footer {
            text-align: center;
            margin-top: 15px;
        }

        .login-footer a {
            color: #007bff;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="login-container">
        <h2>Login</h2>
        <form id="loginForm" method="POST" action="login_action.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-login">Login</button>
            <div id="loader" class="loader">🔄 Processing...</div>
            <p class="error" id="errorMsg"></p>
        </form>

        <div class="login-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const loader = document.getElementById('loader');
        const errorMsg = document.getElementById('errorMsg');

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent form submission to handle it with JS

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!username || !password) {
                errorMsg.textContent = "❗ Both fields are required.";
                return;
            }

            loader.style.display = 'block';
            errorMsg.textContent = '';

            // Create FormData object to send the data to PHP via AJAX
            const formData = new FormData(form);

            fetch('login_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none';
                if (data.success) {
                    // Store the token in localStorage
                    localStorage.setItem('token', data.token);

                    // Redirect to the dashboard
                    window.location.href = 'dashboard.php';
                } else {
                    errorMsg.textContent = data.message; // Show error message
                }
            })
            .catch(error => {
                loader.style.display = 'none';
                errorMsg.textContent = '❗ Something went wrong. Please try again later.';
            });
        });
    </script>

</body>
</html>
