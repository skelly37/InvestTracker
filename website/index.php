<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InvestTracker - Sign In</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js" defer></script>
</head>
<body>
    <div class="container">
        <h1>InvestTracker</h1>
        <form class="form-box" method="post" action="#">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Input">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Input">

            <div class="button-full">
                <button type="button" onclick="showPopup('loginOrRegister')">SIGN IN</button>
            </div>
        </form>
    </div>
</body>
</html>
