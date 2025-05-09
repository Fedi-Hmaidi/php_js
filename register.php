<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #74ebd5, #9face6);
      display: flex;
      height: 100vh;
      justify-content: center;
      align-items: center;
      margin: 0;
    }

    .form-container {
      background: #fff;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    h2 {
      margin-bottom: 25px;
      color: #333;
    }

    input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      transition: border 0.3s ease;
    }

    input:focus {
      border-color: #007bff;
      outline: none;
    }

    button {
      width: 100%;
      padding: 12px;
      background: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
      margin-top: 10px;
    }

    button:hover {
      background: #0056b3;
    }

    .message {
      margin-top: 15px;
      font-size: 14px;
    }

    .error {
      color: red;
      font-size: 13px;
      margin-bottom: 5px;
      text-align: left;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Create an Account</h2>
    <form id="registerForm" novalidate>
      <input type="text" name="username" placeholder="Username" required />
      <div class="error" id="usernameError"></div>

      <input type="email" name="email" placeholder="Email" required />
      <div class="error" id="emailError"></div>

      <input type="text" name="phone_number" placeholder="Phone Number" required />
      <div class="error" id="phoneError"></div>

      <input type="text" name="address" placeholder="Address" required />
      <div class="error" id="addressError"></div>

      <button type="submit">Register</button>
    </form>
    <div class="message" id="message"></div>
  </div>

  <script>
    document.getElementById("registerForm").addEventListener("submit", function(e) {
      e.preventDefault();
      const form = e.target;
      const username = form.username.value.trim();
      const email = form.email.value.trim();
      const phone = form.phone_number.value.trim();
      const address = form.address.value.trim();

      // Clear previous errors and message
      ["usernameError","emailError","phoneError","addressError"].forEach(id => { document.getElementById(id).textContent = ''; });
      const msg = document.getElementById("message"); msg.textContent = '';

      let isValid = true;
      if (!username) { document.getElementById("usernameError").textContent = "Username is required"; isValid = false; }
      if (!email) {
        document.getElementById("emailError").textContent = "Email is required";
        isValid = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById("emailError").textContent = "Invalid email format";
        isValid = false;
      }
      if (!phone) { document.getElementById("phoneError").textContent = "Phone number is required"; isValid = false; }
      if (!address) { document.getElementById("addressError").textContent = "Address is required"; isValid = false; }
      if (!isValid) return;

      const formData = new FormData(form);
      fetch("register_action.php", {
        method: "POST",
        body: formData,
      })
      .then(res => res.json())
      .then(data => {
        msg.style.color = data.success ? "green" : "red";
        msg.textContent = data.success
          ? "🎉 Activation link sent! Check your email to set your password."
          : data.message;

        if (data.success) form.remove();
      });
    });
  </script>
</body>
</html>
