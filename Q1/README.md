# 📝 Registration Form with Validation

A simple and responsive **user registration form** built using **HTML, CSS, and JavaScript**.
This project demonstrates **client-side form validation** with a clean and minimal UI design.

---

## 🚀 Features

* Responsive and centered form layout
* Input validation using JavaScript
* Real-time error messages
* Password strength validation
* Password confirmation check
* Success message on valid submission
* Clean and minimal UI

---

## 📂 Project Structure

```
wad_lab/
│
└──Q1/
    │
    └── q1.html   # Main HTML file (includes CSS & JavaScript)
```

---

## 🛠️ Technologies Used

* HTML5
* CSS3
* JavaScript (Vanilla JS)

---

## 📋 Validation Rules

### 1. Name

* Cannot be empty

### 2. Email

* Must follow standard email format
  Example: `user@example.com`

### 3. Password

Must contain:

* At least 8 characters
* One uppercase letter
* One lowercase letter
* One number
* One special character (@$!%*?&)

### 4. Confirm Password

* Must match the password field

---

## ▶️ How to Run

1. Download or copy the code
2. Save it as `index.html`
3. Open the file in any web browser

---

## 💡 How It Works

* When the user clicks **"Create Account"**, JavaScript:

  * Prevents form submission
  * Checks all validation rules
  * Displays error messages if inputs are invalid
  * Shows a success message if all inputs are correct

---

## 🎯 Output

* Displays error messages below each field if invalid
* Shows **"Registration Successful!"** when all validations pass
* Resets the form after successful submission

---

## 📌 Future Improvements

* Add backend integration (PHP / Node.js)
* Store user data in database
* Add show/hide password toggle
* Improve email validation (advanced regex)
* Add animations and better UI styling

---

## 👨‍💻 Author

* Developed as a simple lab/project demonstration

---

## 📄 License

This project is free to use for educational purposes.

---
