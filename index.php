<?php
session_start(); //Start the session for tracking user state

// Check if the user is logged in by looking for session variables
$isLoggedIn = isset($_SESSION['username']) && isset($_SESSION['screenName']);
$screenName = $_SESSION['screenName'] ?? ''; // Default to empty string if not set
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Chatroom</title>
    <style>
        /* ----------- Base Styling ----------- */

        /* Apply basic styles to the entire page */
        body {
            margin: 0;
            padding-top: 20px;
            background: #fff;
            font-family: sans-serif;
        }

        /* Center container with max width */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ----------- Overlay Styles (e.g., for modals) ----------- */
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
        /* ----------- Header Section ----------- */
        header {
            background-color: rgb(196, 196, 196);
            color: black;
            padding: 20px;
            text-align: center;
            font-size: 25px;
        }

         /* ----------- Top Row Layout (User Info & Buttons) ----------- */
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

        /* ----------- Middle Section Layout (Chat UI) ----------- */
        .middle-sec {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f3f3f3;
            max-height: 400px;

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
    <!-- Main container for the chatroom layout -->

    <div class="container">

        <!-- Page header -->
        <header>Chat room via PHP web sockets</header>

        <!-- Top row containing user info and action buttons -->
        <div class="top-row">
            <div class="col-left">
                <!-- Left column, currently empty -->
                <p></p>
            </div>
             <!-- Center column with author credits -->
            <div class="col-center">By: Oliver McMillen and Nawal Chishty</div>

            <!-- Right column: Login/Signup/Logout/Help buttons -->
            <div class="col-right">
                <!-- Conditional buttons based on login state -->
                <?php if ($isLoggedIn): ?>
                    <!-- Show logout if user is logged in -->
                    <button id="logoutBtn">Logout</button>
                <?php else: ?>
                    <!-- Otherwise, show login and signup buttons -->
                    <button id="loginBtn">Login</button>
                    <button id="signupBtn">Sign Up</button>
                <?php endif; ?>
                <!-- Help is always shown -->
                <button id="helpBtn">Help</button>
            </div>
        </div>

        <!-- Chat interface displayed only if user is logged in -->
        <?php if ($isLoggedIn): ?>
            
            <!-- Spacer row -->
            <div class="top-row"><br></div>

            <!-- Middle section: three-column layout -->
            <div class="middle-sec">
                
                <!-- Left column: Available chatrooms -->
                <div class="chatCol-left">
                    <div style="padding: 10px; max-height: 350px;">
                        <!-- Room list header and add button -->
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3>Available Rooms</h3>
                            <button id="addRoomBtn"><b>+</b></button>
                        </div>
                        <!-- Table-style header for room list -->
                        <div style="display: flex; font-weight: bold; padding: 5px 0; border-bottom: 1px solid #ccc;">
                            <div style="flex: 3;">Room Name</div>
                            <div style="flex: 1; text-align: center;">Status</div>
                            <div style="flex: 2; text-align: right;">Join</div>
                        </div>
                        <!-- Scrollable list of available rooms -->
                        <div id="roomList" style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                        </div>
                    </div>
                </div>

                 <!-- Center column: Currently unused -->
                <div class="chatCol-center">
                    <p></p>
                </div>

                 <!-- Right column: Chat interface -->
                <div class="chatCol-right">
                    <div
                        style="margin-top: 15px; padding: 10px; height: 100%; min-height: 350px; max-height: 350px; display: flex; flex-direction: column;">

                        <!-- Displays the name of the current room -->
                        <h3 style="margin: 0 0 10px;"><span id="currentRoomName"></span></h3>

                        <!-- Message display area -->
                        <div id="messageArea"
                            style="flex: 2; overflow-y: auto; max-height: 250px; min-height: 100px; max-height: 400px; border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; margin-top: 10px;">
                        </div>

                        <!-- Message input and send button -->
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="messageInput" placeholder="Type your message..."
                                style="flex: 1; padding: 8px; margin-bottom: 10px;">

                            <!-- Send button -->
                            <button id="sendBtn" style="align-self: flex-end;">Send</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Login Modal Overlay -->
        <div class="overlay" id="loginOverlay">
            <div class="overlay-content">
                <span class="close-btn" id="closeLogin">[x]</span>
                <h2>Login</h2>
                <!-- Login form with username and password -->
                <form id="loginForm">
                    <label>Username:<br>
                        <input type="text" name="username" required>
                    </label><br><br>
                    <label>Password:<br>
                        <input type="password" name="password" required>
                    </label><br><br>
                    <button type="submit">Login</button>
                </form>
                <!-- Area to display login error messages -->
                <div id="loginMsg" style="color: red; margin-top: 1rem;"></div>
            </div>
        </div>


         <!-- Signup Modal Overlay -->
        <div class="overlay" id="signupOverlay">
            <div class="overlay-content">
                <span class="close-btn" id="closeSignup">[x]</span>
                <h2>Sign Up</h2>
                <!-- Signup form with fields for username, password, and screen name -->
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
                <!-- Area to display signup error messages -->
                <div id="signupMsg" style="color: red; margin-top: 1rem;"></div>
            </div>
        </div>

         <!-- Create Room Modal Overlay -->
        <div class="overlay" id="createRoomOverlay">
            <div class="overlay-content">
                <span class="close-btn" id="closeCreateRoom">[x]</span>
                <h2>Create New Chatroom</h2>
                <!-- Form to create a new chatroom -->
                <form id="createRoomForm">
                    <label>Chatroom Name:<br>
                        <input type="text" name="chatroomName" required>
                    </label><br><br>
                    <label>Chatroom Key (optional):<br>
                        <input type="text" name="chatroomKey">
                    </label><br><br>
                    <button type="submit">Create</button>
                </form>
                <!-- Area to display room creation error messages -->
                <div id="createRoomMsg" style="color: red; margin-top: 1rem;"></div>
            </div>
        </div>

        <!-- Join Room Key Overlay (for protected rooms) -->
        <div class="overlay" id="joinKeyOverlay">
            <div class="overlay-content">
                <span class="close-btn" id="closeJoinKey">[x]</span>
                <h2>Enter Room Key</h2>
                <!-- Room name shown here dynamically -->
                <p id="joinRoomTitle"></p>
                <form id="joinRoomForm">
                    <!-- Hidden field to carry room name -->
                    <input type="hidden" id="hiddenRoomName">
                    <label>Room Key:<br>
                        <input type="password" id="roomKeyInput" required>
                    </label><br><br>
                    <button type="submit">Join</button>
                </form>
                <!-- Area to display room join error messages -->
                <div id="joinRoomMsg" style="color: red; margin-top: 1rem;"></div>
            </div>
        </div>

        <!-- Help Overlay - Displayed regardless of session status-->
        <div class="overlay" id="helpOverlay">
            <div class="overlay-content">
                <span class="close-btn" id="closeHelp">[x]</span>
                <h2>How the Chatroom Works</h2>
                 <!-- Descriptive text about how the application functions -->
                <p style="color: black; font-size: 12px;">Welcome to the PHP WebSocket Chatroom by Oliver and Nawal! 
                <br><br>This real-time chat application allows multiple users to create and join chatrooms dynamically using WebSocket technology for live communication without page reloads. To participate, users must first register by providing a username, password, and a screen name. Once logged in, users can view all currently available chatrooms in a dedicated scrollable section. Each chatroom may be locked (requiring a key to join) or unlocked (open to all users). Users may create new chatrooms by specifying a unique name and, optionally, a key to restrict access.
                <br><br>When a new room is created, it is immediately broadcast to all connected users using a WebSocket connection. This ensures that everyone sees new rooms in real time without needing to refresh the page. When joining a chatroom, users are automatically assigned to that room’s message group, meaning they will only send and receive messages with others in the same room. Messages appear instantly for all users in the room, styled with alternating background colors to improve readability
                This system includes a clean and functional user interface with overlays for login, signup, help, chatroom creation, and room key validation. Sessions are managed to ensure only authenticated users access the chat functionalities. The project demonstrates a full-stack implementation of real-time messaging using PHP, WebSockets, HTML, CSS, and JavaScript. It also handles edge cases like duplicate room names, invalid keys, and proper session handling.
                <br><br>Use this chatroom to explore how WebSocket communication can enhance web applications through real-time interactivity.</p>
            </div>
        </div>

        <script>
            // Keeps track of the currently joined chatroom
            let currentRoom = "";
            // Show Help Overlay
            document.getElementById("helpBtn").addEventListener("click", () => {
                document.getElementById("helpOverlay").style.display = "flex";
            });
            // Close Help Overlay
            document.getElementById("closeHelp").addEventListener("click", () => {
                document.getElementById("helpOverlay").style.display = "none";
            });
            // Logout functionality using a fetch call to logout.php
            document.getElementById("logoutBtn")?.addEventListener("click", () => {
                fetch("services/logout.php")
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
            });
            // Utility functions to show/hide overlays
            const show = (id) => document.getElementById(id).style.display = "flex";
            const hide = (id) => document.getElementById(id).style.display = "none";
            // Event listeners for opening/closing login and signup modals
            document.getElementById("loginBtn")?.addEventListener("click", () => show("loginOverlay"));
            document.getElementById("closeLogin")?.addEventListener("click", () => hide("loginOverlay"));
            document.getElementById("signupBtn")?.addEventListener("click", () => show("signupOverlay"));
            document.getElementById("closeSignup")?.addEventListener("click", () => hide("signupOverlay"));
            document.getElementById("addRoomBtn")?.addEventListener("click", () => show("createRoomOverlay"));
            document.getElementById("closeCreateRoom")?.addEventListener("click", () => hide("createRoomOverlay"));
            // Close join room key overlay and clear messages
            document.getElementById("closeJoinKey").addEventListener("click", () => {
                document.getElementById("joinKeyOverlay").style.display = "none";
                document.getElementById("joinRoomMsg").textContent = "";
            });


            // Handle login form submission
            document.getElementById("loginForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                const form = e.target;
                const res = await fetch("services/login.php", {
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

            // Handle signup form submission
            document.getElementById("signupForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                const form = e.target;
                const res = await fetch("services/signup.php", {
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

            // Handle join room form submission (for locked rooms)
            document.getElementById("joinRoomForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                const roomName = document.getElementById("hiddenRoomName").value;
                const roomKey = document.getElementById("roomKeyInput").value.trim();
                const res = await fetch("services/joinRoom.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ chatroomName: roomName, chatroomKey: roomKey })
                });

                const data = await res.json();
                if (data.success) {

                    currentRoom = roomName;

                    document.getElementById("joinKeyOverlay").style.display = "none";
                    document.getElementById("currentRoomName").textContent = roomName;
                    document.getElementById("messageArea").innerHTML = "";
                    
                } else {
                    document.getElementById("joinRoomMsg").textContent = data.error || "Join failed.";
                }
            });


            // Handle create room form submission
            document.getElementById("createRoomForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                const form = e.target;
                const res = await fetch("services/createRoom.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        chatroomName: form.chatroomName.value.trim(),
                        chatroomKey: form.chatroomKey.value.trim()
                    })
                });

                const data = await res.json();
                if (data.success) {
                    hide("createRoomOverlay");

                    // Send room creation event to all connected clients via WebSocket
                    if (typeof socket !== "undefined" && socket.readyState === WebSocket.OPEN) {
                        socket.send(JSON.stringify({
                            type: "new_room",
                            name: form.chatroomName.value,
                            locked: !!form.chatroomKey.value
                        }));
                    }
                    form.reset();
                } else {
                    document.getElementById("createRoomMsg").textContent = data.error || "Failed to create chatroom.";
                }
            });


            // Send message when user clicks "Send" button
            document.getElementById("sendBtn").addEventListener("click", () => {
                const msg = document.getElementById("messageInput").value.trim();
                if (!msg || !socket || socket.readyState !== WebSocket.OPEN);

                socket.send(JSON.stringify({
                    type: "chat_message",
                    room: currentRoom,
                    screenName: "<?= $screenName ?>",
                    message: msg
                }));

                document.getElementById("messageInput").value = "";
            });

            // Fetch and display available chatrooms from server
            async function loadAvailableRooms() {
                const res = await fetch("services/getRooms.php");
                const data = await res.json();

                if (!data.success) return;

                const roomList = document.getElementById("roomList");
                roomList.innerHTML = ""; // Clear old

                data.rooms.forEach(room => {
                    addRoomToList(room);
                });
            }

            // Dynamically add a chatroom to the room list UI
            function addRoomToList(room) {
                const roomList = document.getElementById("roomList");
                const div = document.createElement("div");
                div.style.display = "flex";
                div.style.padding = "5px 0";
                div.style.borderBottom = "1px solid #eee";

                div.innerHTML = `
                    <div style="flex: 3;">${room.name}</div>
                    <div style="flex: 1; text-align: center;">
                        <img src="resources/${room.locked ? 'lock' : 'unlock'}.png" width="16">
                    </div>
                    <div style="flex: 2; text-align: right;">
                        <button data-room="${room.name}" class="joinBtn">Join</button>
                    </div>
                `;
                // Handle room join (locked or unlocked)
                div.querySelector(".joinBtn").addEventListener("click", () => {
                    const roomName = room.name;

                    if (room.locked) {
                        document.getElementById("joinRoomTitle").textContent = `Room: ${roomName}`;
                        document.getElementById("hiddenRoomName").value = roomName;
                        document.getElementById("roomKeyInput").value = "";
                        document.getElementById("joinRoomMsg").textContent = "";
                        document.getElementById("joinKeyOverlay").style.display = "flex";
                        document.getElementById("messageArea").innerHTML = ""; // Clear old messages
                        currentRoom = roomName;
                    } else if (!room.locked) {
                        document.getElementById("joinKeyOverlay").style.display = "none";
                        document.getElementById("currentRoomName").textContent = roomName;
                        document.getElementById("messageArea").innerHTML = ""; // Clear old messages
                        currentRoom = roomName;
                    } else {
                        document.getElementById("joinRoomMsg").textContent = data.error || "Join failed.";
                    }
                });

                roomList.appendChild(div);
            }
            // Load rooms on page load
            window.addEventListener("DOMContentLoaded", loadAvailableRooms);

            loadAvailableRooms();

            // WebSocket for broadcasting new rooms
            const socket = new WebSocket("ws://18.221.3.145:8081");
            const myScreenName = "<?= $screenName ?>";

            // Broadcast to WebSocket
            socket.onmessage = (event) => {
                const data = JSON.parse(event.data);

                if (data.type === "new_room") {
                    addRoomToList({
                        name: data.chatroomName,
                        locked: !!data.locked
                    });
                }

                if (data.type === "chat_message") {
                    if (currentRoom === '') return;

                    const isMe = data.screenName === myScreenName;
                    const sender = isMe ? "Me" : data.screenName;

                    const messageDiv = document.createElement("div");
                    messageDiv.textContent = `${sender}: ${data.message}`;
                    messageDiv.style.padding = "4px";

                    const messageArea = document.getElementById("messageArea");
                    messageDiv.style.backgroundColor = messageArea.children.length % 2 === 0 ? "#f1f1f1" : "#dcdcdc";

                    messageArea.appendChild(messageDiv);
                    messageArea.scrollTop = messageArea.scrollHeight;
                }
            };


        </script>
    </div>
</body>

</html>