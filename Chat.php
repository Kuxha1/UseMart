<?php

/**
 * Project Name: UseMart – Second-Hand Goods Marketplace
 * Author: Kushal Mistry
 * Copyright (c) 2026 Kushal Mistry
 * All rights reserved.
 */

session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
}else{

// Logged-in user's details
$user_id = $_SESSION["user_id"];
$fullname = $_SESSION["fullname"];

// Get receiver ID from URL
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : null;

$receiver = null;
if ($receiver_id) {
    $stmt = $conn->prepare("SELECT fullname, profile_photo FROM users WHERE id = ?");
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    $receiver = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

}?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="style/chat-style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Box</title>
    <div class="header">
        <div class="usemart">
       <img src="resources/A.png" alt="Logo" class="logo" onclick="redirectToHome()">
       <h1 style="margin-top:9px; margin-left:10px;">UseMart</h1>
       </div>
       <div class="icons">
       <button class="icon-button" onclick="redirectToMedia()">Community</button>
           <button class="icon-button" onclick="redirectToChat()">Chat</button>
           <button class="icon-button" onclick="redirectToWish()">Wishlist</button>
           <button class="icon-button" onclick="redirectToCart()">Cart</button>
           <button class="icon-button" onclick="redirectToProfile()"><img src="resources/icons/Vector-4.png" alt="User" class="icon"></button>
           <button class="icon-button" onclick="redirectToDetails()"><img src="resources/icons/Vector-5.png" alt="Menu" class="icon"></button>
        </div>
   </div>
</head>
<body>
<?php
    if (!isset($_SESSION['user_id'])) { ?>

        <div style="text-align: center; padding: 50px;">
            <h2>You are not logged in</h2>
            <p>Please log in or register to start a chat.</p>
            <button onclick="redirectToLogin()" class="login-button">Login / Register</button>
            <br><br><br><br><br><br>
        </div>
        <footer class="footer">
            <table class="footer-table">
                <tr>
                    <th>Social Media</th>
                    <th>Report</th>
                    <th>About Us</th>
                    <th>Help & Contact</th>
                </tr>
                <tr>
                    <td><a href="#">Insta</a></td>
                    <td><a href="#">Buyer/Customer</a></td>
                    <td><a href="#">Terms & Conditions</a></td>
                    <td><a href="#">Seller Info</a></td>
                </tr>
                <tr>
                    <td><a href="#">X</a></td>
                    <td><a href="#">Seller</a></td>
                    <td><a href="#">Developers</a></td>
                    <td><a href="#">Contact Us</a></td>
                </tr>
                <tr>
                    <td><a href="#">Facebook</a></td>
                    <td><a href="#">Bugs</a></td>
                    <td><a href="#">Company Info</a></td>
                    <td></td>
                </tr>
                <tr>
                    <td><a href="#">Reddit</a></td>
                    <td><a href="#">Others</a></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><a href="#">Discord</a></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </footer>
        <?php } else { ?>

    <div class="chat-container">
    <div class="user-list">
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search users..." onkeyup="searchUsers()">
        <img src="resources/icons/Search.png" alt="Search">
    </div>

    <div id="userList">
    <?php
    // Fetch users with whom the current user has a chat history using a prepared statement
    $stmt = $conn->prepare("
        SELECT id, fullname, profile_photo FROM users 
        WHERE id IN (
            SELECT sender_id FROM chats WHERE receiver_id = ? 
            UNION 
            SELECT receiver_id FROM chats WHERE sender_id = ?
        ) 
        AND id != ?
    ");
    $stmt->bind_param("iii", $user_id, $user_id, $user_id);
    $stmt->execute();
    $users = $stmt->get_result();

    if ($users->num_rows > 0) {
        while ($row = $users->fetch_assoc()) {
            $profile_photo = (!empty($row['profile_photo']) && file_exists($row['profile_photo'])) ? $row['profile_photo'] : 'resources/pfp.png';

            echo "<div class='user' onclick=\"selectUser('{$row['fullname']}', '{$profile_photo}', '{$row['id']}')\">";
            echo "<a href='user_profile.php?user_id={$row['id']}' target='_self'><img src='{$profile_photo}' alt='User'></a>";
            echo "<h3>{$row['fullname']}</h3>";
            echo "</div>";
        }
    } else {
        echo "<p>No recent chats. Search for a user to start a conversation.</p>";
    }

    $stmt->close();
    ?>
</div>

</div>
        <div class="chat-box">
        <div class="chat-header" id="chatHeader">
    <?php if ($receiver_id && $receiver): ?>
        <div class="chat-user-info">
            <a id="chatUserProfileLink" href="user_profile.php?user_id=<?= $receiver_id ?>" target="_self">
                <img id="chatUserImg" 
                     src="<?= (!empty($receiver['profile_photo']) && file_exists($receiver['profile_photo'])) 
                     ? htmlspecialchars($receiver['profile_photo']) : 'resources/pfp.png'; ?>" 
                     alt="User">
            </a>
            <div class="chat-user-details">
                <h3 id="chatUserName" style="margin-left: 10px;"><?= htmlspecialchars($receiver['fullname']) ?></h3>
            </div>
        </div>
    <?php else: ?>
        <div class="chat-user-info">
            <img id="chatUserImg" src="resources/pfp.png" alt="User">
            <h3 id="chatUserName" style="margin-left: 10px;">Select a user</h3>
        </div>
    <?php endif; ?>
</div>





            <div class="messages" id="messages"></div>
            <div class="message-input">
                <input type="text" id="messageInput" placeholder="Type...">
                <button class="sendMbutton"onclick="sendMessage()">
                    <img src="resources/icons/send.png" height="15px" width="15px"></button>
            </div>
        </div>
    </div>
    <input type="hidden" id="sender_id" value="<?php echo $user_id; ?>">
    <input type="hidden" id="receiver_id" value="<?php echo $receiver_id; ?>">


<?php } ?>    
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var messageInterval;

    function redirectToLogin() { window.location.href = "login.php"; }
    function redirectToHome() { window.location.href = "index.php"; }
    function redirectToMedia() { window.location.href = "community_form.php"; }
    function redirectToChat() { window.location.href = "Chat.php"; }
    function redirectToWish() { window.location.href = "Wishlist.php"; }
    function redirectToCart() { window.location.href = "Cart.php"; }
    function redirectToProfile() { window.location.href = "Profile.php"; }
    function redirectToDetails() {
            window.location.href = 'user_details.php';
        }

    function sendMessage() {
        let sender_id = $("#sender_id").val();
        let receiver_id = $("#receiver_id").val();
        let message = $("#messageInput").val().trim();

        if (!message || !receiver_id) {
            alert("Select a user and type a message.");
            return;
        }

        $.post("send_message.php", { sender_id, receiver_id, message }, function(response) {
            console.log(response);
            $("#messageInput").val(""); // Clear input after sending

            // Immediately refresh messages for sender
            loadMessages();
        }).fail(function() {
            alert("Message failed to send. Try again.");
        });
    }

    function loadMessages() {
    let sender_id = $("#sender_id").val();
    let receiver_id = $("#receiver_id").val();

    if (!receiver_id) {
        console.warn("No user selected for chat.");
        return;
    }

    $.post("load_messages.php", { sender_id, receiver_id }, function(response) {
        console.log("Received messages:", response);  // Debug log
        $("#messages").html(response);

        // Scroll to bottom when new messages appear
        $("#messages").scrollTop($("#messages")[0].scrollHeight);
    }).fail(function(xhr, status, error) {
        console.error("Error loading messages: " + error);
    });
}


function selectUser(name, imgSrc, id) {
    console.log("Selected User:", name, imgSrc, id); // Debugging

    $("#chatUserName").text(name);
    $("#receiver_id").val(id);
    $("#chatHeader").show();

    // Ensure profile image updates correctly
    if (imgSrc && imgSrc.trim() !== "" && imgSrc !== "null") {
        $("#chatUserImg").attr("src", imgSrc + "?t=" + new Date().getTime()); // Prevent caching issues
    } else {
        console.warn("Profile image missing, using default.");
        $("#chatUserImg").attr("src", "resources/pfp.png");
    }

    // 🔥 Fix: Update profile link dynamically
    $("#chatUserProfileLink").attr("href", "user_profile.php?user_id=" + id);

    // Start loading messages
    loadMessages();

    // Clear previous interval if any
    if (messageInterval) {
        clearInterval(messageInterval);
    }
    messageInterval = setInterval(loadMessages, 1000);
}




    function searchUser() {
        let query = document.getElementById("userSearch").value.toLowerCase();

        if (query === "") {
            document.getElementById("searchResults").innerHTML = "";
            return;
        }

        fetch("search_users.php?query=" + query)
            .then(response => response.text())
            .then(data => {
                document.getElementById("searchResults").innerHTML = data;
            });
    }

    function searchUsers() {
        let query = document.getElementById("searchInput").value;

        fetch("search_users.php?query=" + encodeURIComponent(query))
            .then(response => response.text())
            .then(data => {
                document.getElementById("userList").innerHTML = data;
            })
            .catch(error => console.error("Error searching users:", error));
    }

    $(document).ready(function() {
        // Make sure sender ID is set
        let senderId = $("#sender_id").val();

        if (senderId) {
            // Load messages every 1 second
            messageInterval = setInterval(loadMessages, 1000);
        }
    });
</script>

</body>
</html>
