<?php
session_start();
$isLoggedIn = isset($_SESSION['username']) && isset($_SESSION['screenName']);
$screenName = $_SESSION['screenName'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Chatroom</title>
    <style>
        body {
            margin-left: 50px;
            margin-right: 50px;
            margin-bottom: 50px;
            margin-top: 20px;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            display: none;
            justify-content: center;
            align-items: center;
        }

        .overlay-content {
            background: white;
            padding: 30px;
            width: 300px;
            border-radius: 10px;
            position: relative;
        }

        .overlay-content h2 {
            margin-top: 0;
        }

        .close-btn {
            position: absolute;
            top: 8px;
            right: 10px;
            cursor: pointer;
        }

        header {
            background-color:rgb(196, 196, 196);
            color: black;
            padding: 20px;
            text-align: center;
            font-size: 25px;
        }

        .top-row {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #b3b3b3;
            background: #f3f3f3;
        }

        .col-left {
            flex: 2;
            border-right: 1px solid #000;
            padding-right: 10px;
        }

        .col-right {
            flex: 2;
            text-align: right;
            border-left: 1px solid #000;
            padding-left: 10px;
        }

        .col-center {
            flex: 3;
            text-align: center;
            font-size: 15px;
        }

        .middle-sec {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* padding: 10px; */
            background: #f3f3f3;
            
        }

        .chatCol-left {
            flex: 3;
            border: 1px solid #b3b3b3;
            /* padding-right: 10px; */
            min-height: 400px;
        }

        .chatCol-right {
            flex: 3;
            border: 1px solid #b3b3b3;
            /* padding-left: 10px; */
            min-height: 400px;
        }

        .chatCol-center {
            flex: 1;
            text-align: center;
            border: 1px solid #b3b3b3;
            min-height: 400px;
        }

        .col-right button {
            margin-left: 7px;
        }
    </style>
</head>

<body>

    <header>Chat room via PHP web sockets</header>

    <div class="top-row">
        <div class="col-left"> <p></p></div>

        <div class="col-center">By: Oliver McMillen and Nawal Chrishty</div>

        <div class="col-right">
            <!-- If user is logged in, display logout button -->
            <?php if ($isLoggedIn): ?>
                <button id="logoutBtn">Logout</button>
            <?php else: ?>
                <button id="loginBtn">Login</button>
                <button id="signupBtn">Sign Up</button>
            <?php endif; ?>
            <button id="helpBtn">Help</button>
        </div>
    </div>

    <!-- If user is logged in... -->
    <?php if ($isLoggedIn): ?>

        <!-- Placeholder for available rooms and chat UI -->
         <div class="middle-sec">
            <div class="chatCol-left"><p></p></div>
            <div class="chatCol-center"><p></p></div>
            <div class="chatCol-right"><p></p></div>
        </div>


    <!-- If user is not logged in... -->
    <?php else: ?>

        <!-- Login Overlay -->
        <div class="overlay" id="loginOverlay">
            <div class="overlay-content">
                <span class="close-btn" id="closeLogin">[x]</span>
                <h2>Login</h2>
                <form id="loginForm">
                    <label>Username:<br>
                        <input type="text" name="username" required>
                    </label><br><br>
                    <label>Password:<br>
                        <input type="password" name="password" required>
                    </label><br><br>
                    <button type="submit">Login</button>
                </form>
                <div id="loginMsg" style="color: red; margin-top: 1rem;"></div>
            </div>
        </div>


        <!-- Signup Overlay -->
        <div class="overlay" id="signupOverlay">
            <div class="overlay-content">
                <span class="close-btn" id="closeSignup">[x]</span>
                <h2>Sign Up</h2>
                <form id="signupForm">
                    <label>Username:<br>
                        <input type="text" name="username" required>
                    </label><br><br>
                    <label>Password:<br>
                        <input type="password" name="password" required>
                    </label><br><br>
                    <label>Screen Name:<br>
                        <input type="text" name="screenName" required>
                    </label><br><br>
                    <button type="submit">Sign Up</button>
                </form>
                <div id="signupMsg" style="color: red; margin-top: 1rem;"></div>
            </div>
        </div>
    <?php endif; ?>


    <!-- Help Overlay - Displayed regardless of session status-->
    <div class="overlay" id="helpOverlay">
        <div class="overlay-content">
            <span class="close-btn" id="closeHelp">[x]</span>
            <h2>How the Chatroom Works</h2>
            <p>This chatroom allows you to create and join chatrooms using a WebSocket connection. You must sign up with
                a username and screen name. Once logged in, you'll see a list of chatrooms, or you can create one.
                Messages are only seen by users in the same chatroom. This help box can be styled as needed. Add at
                least 250 words of explanation to meet the assignment requirement.</p>
        </div>
    </div>

<script>
        document.getElementById("helpBtn").addEventListener("click", () => {
            document.getElementById("helpOverlay").style.display = "flex";
        });

        document.getElementById("closeHelp").addEventListener("click", () => {
            document.getElementById("helpOverlay").style.display = "none";
        });

        document.getElementById("logoutBtn")?.addEventListener("click", () => {
            fetch("/services/logout.php")
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                });
        });

        const show = (id) => document.getElementById(id).style.display = "flex";
        const hide = (id) => document.getElementById(id).style.display = "none";

        document.getElementById("loginBtn").addEventListener("click", () => show("loginOverlay"));
        document.getElementById("closeLogin").addEventListener("click", () => hide("loginOverlay"));
        document.getElementById("signupBtn").addEventListener("click", () => show("signupOverlay"));
        document.getElementById("closeSignup").addEventListener("click", () => hide("signupOverlay"));

        // Login overlay
        document.getElementById("loginForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            const form = e.target;
            const res = await fetch("/services/login.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    username: form.username.value.trim(),
                    password: form.password.value
                })
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                document.getElementById("loginMsg").textContent = data.error || "Login failed.";
            }
        });

        // Signup overlay
        document.getElementById("signupForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            const form = e.target;
            const res = await fetch("/services/signup.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    username: form.username.value.trim(),
                    password: form.password.value,
                    screenName: form.screenName.value.trim()
                })
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                document.getElementById("signupMsg").textContent = data.error || "Signup failed.";
            }
        });

    </script>
</body>

</html>