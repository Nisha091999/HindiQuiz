# Hindi Quiz Web Application - README

This project is a simple Hindi Quiz Web Application built with PHP and hosted locally using **XAMPP**. It supports login, quiz category selection, randomized image-based quizzes, and result evaluation.

---

## 🧰 Tools Used

* **Visual Studio Code**: Code editor for development
* **PHP 8.4.8**: Backend scripting ([Download PHP](https://windows.php.net/downloads/releases/php-8.4.8-Win32-vs17-x64.zip))
* **XAMPP**: Local server to run Apache + PHP
* **HTML/CSS/JavaScript**: UI and interactions

---

## 📁 Project Folder Structure


HindiQuiz/
├── AppData/
│   └── Scores.txt
├── AppFiles/
│   └── images/
│       └── L2/
│           └── KaImages/
│               ├── image1.png
│               ├── image2.png
│               └── Answers.txt
├── assets/
│   └── style.css
├── index.php
├── login.php
├── menu.php
├── quiz.php
├── result.php
└── logout.php


---

## 🚀 How to Run the Project

### 1. Install Required Software

* Download and install **[XAMPP](https://www.apachefriends.org/index.html)**
* Download and install **[Visual Studio Code](https://code.visualstudio.com/)**
* Download and extract **[PHP 8.4.8 (Windows 64-bit)](https://windows.php.net/downloads/releases/php-8.4.8-Win32-vs17-x64.zip)** if you're running PHP manually (outside XAMPP)

### 2. Set Up the Project

1. Extract the downloaded PHP zip file and place it in a directory like:
   `C:\php-8.4.8`
2. Add PHP path to your **system environment variables**.
3. Open **XAMPP Control Panel** and start **Apache**.
4. Place the project folder `HindiQuiz` inside:
   `C:\xampp\htdocs\`
5. Open **Visual Studio Code**, go to `File > Open Folder`, and select:
   `C:\xampp\htdocs\HindiQuiz`

### 3. Open and Run the Project

1. In VS Code, open `index.php`.
2. Right-click on the file and select **"Reveal in File Explorer"** or **"Copy Path"**.
3. Open browser and go to:
  [http://localhost/HindiQuiz/index.php](http://localhost/HindiQuiz/index.php)


## 📌 Notes
- Quiz questions are randomized per user.
- Prevents revisiting quiz page after submission.
- Results are stored in `AppData/Scores.txt`
