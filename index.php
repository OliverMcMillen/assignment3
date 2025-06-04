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
            margin: 0;
            padding-top: 20px;
            background: #fff;
            font-family: sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
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
    <div class="container">

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

            <div class="top-row"><br></div>
            <div class="middle-sec">

                <div class="chatCol-left">
                    <div style="padding: 10px; max-height: 350px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3>Available Rooms</h3>
                            <button id="addRoomBtn"><b>+</b></button>
                        </div>

                        <div style="display: flex; font-weight: bold; padding: 5px 0; border-bottom: 1px solid #ccc;">
                            <div style="flex: 3;">Room Name</div>
                            <div style="flex: 1; text-align: center;">Status</div>
                            <div style="flex: 2; text-align: right;">Join</div>
                        </div>

                        <div id="roomList" style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                        </div>
                    </div>
                </div>


                <div class="chatCol-center">
                    <p></p>
                </div>

                <!-- Chat Area -->
                <div class="chatCol-right">
                    <div
                        style="margin-top: 15px; padding: 10px; height: 100%; min-height: 350px; max-height: 350px; display: flex; flex-direction: column;">

                        <!-- Header for current room name -->
                        <h3 style="margin: 0 0 10px;"><span id="currentRoomName"></span></h3>

                        <!-- Scrollable message area -->
                        <div id="messageArea"
                            style="flex: 2; overflow-y: auto; max-height: 250px; min-height: 100px; max-height: 400px; border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; margin-top: 10px;">
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
        <?php endif; ?>

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

        <!-- Create Room Overlay -->
        <div class="overlay" id="createRoomOverlay">
            <div class="overlay-content">
                <span class="close-btn" id="closeCreateRoom">[x]</span>
                <h2>Create New Chatroom</h2>
                <form id="createRoomForm">
                    <label>Chatroom Name:<br>
                        <input type="text" name="chatroomName" required>
                    </label><br><br>
                    <label>Chatroom Key (optional):<br>
                        <input type="text" name="chatroomKey">
                    </label><br><br>
                    <button type="submit">Create</button>
                </form>
                <div id="createRoomMsg" style="color: red; margin-top: 1rem;"></div>
            </div>
        </div>

        <!-- Join Room Key Overlay -->
        <div class="overlay" id="joinKeyOverlay">
            <div class="overlay-content">
                <span class="close-btn" id="closeJoinKey">[x]</span>
                <h2>Enter Room Key</h2>
                <p id="joinRoomTitle"></p>
                <form id="joinRoomForm">
                    <input type="hidden" id="hiddenRoomName">
                    <label>Room Key:<br>
                        <input type="password" id="roomKeyInput" required>
                    </label><br><br>
                    <button type="submit">Join</button>
                </form>
                <div id="joinRoomMsg" style="color: red; margin-top: 1rem;"></div>
            </div>
        </div>

        <!-- Help Overlay - Displayed regardless of session status-->
        <div class="overlay" id="helpOverlay">
            <div class="overlay-content">
                <span class="close-btn" id="closeHelp">[x]</span>
                <h2>How the Chatroom Works</h2>
                <p style="color: black; font-size: 12px;">Welcome to the PHP WebSocket Chatroom by Oliver and Nawal! 
                <br><br>This real-time chat application allows multiple users to create and join chatrooms dynamically using WebSocket technology for live communication without page reloads. To participate, users must first register by providing a username, password, and a screen name. Once logged in, users can view all currently available chatrooms in a dedicated scrollable section. Each chatroom may be locked (requiring a key to join) or unlocked (open to all users). Users may create new chatrooms by specifying a unique name and, optionally, a key to restrict access.
                <br><br>When a new room is created, it is immediately broadcast to all connected users using a WebSocket connection. This ensures that everyone sees new rooms in real time without needing to refresh the page. When joining a chatroom, users are automatically assigned to that room’s message group, meaning they will only send and receive messages with others in the same room. Messages appear instantly for all users in the room, styled with alternating background colors to improve readability
                This system includes a clean and functional user interface with overlays for login, signup, help, chatroom creation, and room key validation. Sessions are managed to ensure only authenticated users access the chat functionalities. The project demonstrates a full-stack implementation of real-time messaging using PHP, WebSockets, HTML, CSS, and JavaScript. It also handles edge cases like duplicate room names, invalid keys, and proper session handling.
                <br><br>Use this chatroom to explore how WebSocket communication can enhance web applications through real-time interactivity.</p>
            </div>
        </div>

        <script>
            let currentRoom = "";

            document.getElementById("helpBtn").addEventListener("click", () => {
                document.getElementById("helpOverlay").style.display = "flex";
            });

            document.getElementById("closeHelp").addEventListener("click", () => {
                document.getElementById("helpOverlay").style.display = "none";
            });

            document.getElementById("logoutBtn")?.addEventListener("click", () => {
                fetch("services/logout.php")
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
            });

            const show = (id) => document.getElementById(id).style.display = "flex";
            const hide = (id) => document.getElementById(id).style.display = "none";

            document.getElementById("loginBtn")?.addEventListener("click", () => show("loginOverlay"));
            document.getElementById("closeLogin")?.addEventListener("click", () => hide("loginOverlay"));
            document.getElementById("signupBtn")?.addEventListener("click", () => show("signupOverlay"));
            document.getElementById("closeSignup")?.addEventListener("click", () => hide("signupOverlay"));
            document.getElementById("addRoomBtn")?.addEventListener("click", () => show("createRoomOverlay"));
            document.getElementById("closeCreateRoom")?.addEventListener("click", () => hide("createRoomOverlay"));
            document.getElementById("closeJoinKey").addEventListener("click", () => {
                document.getElementById("joinKeyOverlay").style.display = "none";
                document.getElementById("joinRoomMsg").textContent = "";
            });


            // Login overlay
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

            // Signup overlay
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

            // Join room overlay
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


            // Create room form submission
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

                    // Broadcast new room to all connected WebSocket clients
                    if (typeof socket !== "undefined" && socket.readyState === WebSocket.OPEN) {
                        socket.send(JSON.stringify({
                            type: "new_room",
                            name: form.chatroomName.value,
                            locked: !!form.chatroomKey.value
                        }));
                    }
                    // form.reset();
                } else {
                    document.getElementById("createRoomMsg").textContent = data.error || "Failed to create chatroom.";
                }
            });


            // Send button functionality
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

            window.addEventListener("DOMContentLoaded", loadAvailableRooms);

            loadAvailableRooms();

            // WebSocket for broadcasting new rooms
            const socket = new WebSocket("ws://localhost:8081");
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