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
            background-color: rgb(196, 196, 196);
            color: black;
            padding: 20px;
            text-align: center;
            font-size: 25px;
        }

        /* Top row */
        .top-row {
            display: flex;
            align-items: center;
            border: 1px solid #b3b3b3;
            background: #f3f3f3;
            min-height: 15px;
        }

        .col-left {
            flex: 2;
            border-right: 1px solid #b3b3b3;
            padding-right: 10px;
            min-height: 35px;
        }

        .col-right {
            flex: 2;
            text-align: right;
            display: flex;
            align-items: center;
            justify-content: right;
            border-left: 1px solid #b3b3b3;
            padding-left: 10px;
            min-height: 35px;
        }

        .col-right button {
            margin-left: 7px;
            margin-right: 7px;
        }

        .col-center {
            flex: 3;
            text-align: center;
            font-size: 15px;
        }

        /* Chat section */
        .middle-sec {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f3f3f3;

        }

        .chatCol-left {
            flex: 3;
            border: 1px solid #b3b3b3;
            min-height: 400px;
        }

        .chatCol-right {
            flex: 3;
            border: 1px solid #b3b3b3;
            min-height: 400px;
        }

        .chatCol-center {
            flex: 1;
            text-align: center;
            border: 1px solid #b3b3b3;
            min-height: 400px;
        }
    </style>
</head>

<body>

    <header>Chat room via PHP web sockets</header>

    <div class="top-row">
        <div class="col-left">
            <p></p>
        </div>

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
        <div class="top-row"><br></div>
        <div class="middle-sec">

            <div class="chatCol-left">
                <div style="padding: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3>Available Rooms</h3>
                        <button id="addRoomBtn"><b>+</b></button>
                    </div>

                    <div style="display: flex; font-weight: bold; padding: 5px 0; border-bottom: 1px solid #ccc;">
                        <div style="flex: 3;">Room Name</div>
                        <div style="flex: 1; text-align: center;">Lock</div>
                        <div style="flex: 2; text-align: right;">Join</div>
                    </div>

                    <div id="roomList" style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                        <!-- Sample data -->
                        <div style="display: flex; padding: 5px 0; border-bottom: 1px solid #eee;">
                            <div style="flex: 3;">Room A</div>
                            <div style="flex: 1; text-align: center;"><img src="resources/unlock.png" width="16"></div>
                            <div style="flex: 2; text-align: right;"><button>Join</button></div>
                        </div>

                        <div style="display: flex; padding: 5px 0; border-bottom: 1px solid #eee;">
                            <div style="flex: 3;">Room B</div>
                            <div style="flex: 1; text-align: center;"><img src="resources/lock.png" width="16"></div>
                            <div style="flex: 2; text-align: right;"><button>Join</button></div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="chatCol-center">
                <p></p>
            </div>


            <div class="chatCol-right">
                <div style="margin-top: 15px; padding: 10px; height: 100%; display: flex; flex-direction: column;">

                    <!-- Header for current room name -->
                    <h3 style="margin: 0 0 10px;">Room: <span id="currentRoomName"></span></h3>

                    <!-- Scrollable message area -->
                    <div id="messageArea"
                        style="flex: 2; overflow-y: auto; max-height: 250px; border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                        <!-- Sample messages -->
                        <div style="padding: 5px; background: #f9f9f9;">Jane777: Hello, everyone. This is Jane.</div>
                        <div style="padding: 5px; background: #e0e0e0;">me: Hi Jane.</div>
                        <div style="padding: 5px; background: #f9f9f9;">Jake: Welcome Jane</div>
                        <div style="padding: 5px; background: #e0e0e0;">Jane777: Who is going to the game tonight?</div>                        <div style="padding: 5px; background: #f9f9f9;">Jane777: Hello, everyone. This is Jane.</div>
                        <div style="padding: 5px; background: #e0e0e0;">me: Hi Jane.</div>
                        <div style="padding: 5px; background: #f9f9f9;">Jake: Welcome Jane</div>
                        <div style="padding: 5px; background: #e0e0e0;">Jane777: Who is going to the game tonight?</div>                        <div style="padding: 5px; background: #f9f9f9;">Jane777: Hello, everyone. This is Jane.</div>
                        <div style="padding: 5px; background: #e0e0e0;">me: Hi Jane.</div>
                        <div style="padding: 5px; background: #f9f9f9;">Jake: Welcome Jane</div>
                        <div style="padding: 5px; background: #e0e0e0;">Jane777: Who is going to the game tonight?</div>                        <div style="padding: 5px; background: #f9f9f9;">Jane777: Hello, everyone. This is Jane.</div>
                        <div style="padding: 5px; background: #e0e0e0;">me: Hi Jane.</div>
                        <div style="padding: 5px; background: #f9f9f9;">Jake: Welcome Jane</div>
                        <div style="padding: 5px; background: #e0e0e0;">Jane777: Who is going to the game tonight?</div>
                    </div>

                    <!-- Message input -->
                     <div style="display: flex; gap: 10px;">
                    <input type="text" id="messageInput" placeholder="Type your message..."
                        style="flex: 1; padding: 8px; margin-bottom: 10px;">

                    <!-- Send button -->
                    <button id="sendBtn" style="align-self: flex-end;">Send</button>
</div>
                </div>
            </div>
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