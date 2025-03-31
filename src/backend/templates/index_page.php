<?php

use utils\SessionManager;

SessionManager::createSession();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yalp | Anti Social, Social Media</title>
    <link rel="stylesheet" href="dist/css/style.css">
</head>
<body>
    <header class="main-menu-header">
        <h1>Yalp.app</h1>
        <nav>
            <ul style="display: flex;align-items:center;gap: 20px;">
                <?php if (SessionManager::isActive() && SessionManager::isAuthenticated()): ?>
                    <li><a href="/app">Go to App</a></li>
                    <li><a href="api/logout">Logout</a></li>
                <?php else: ?>
                <li><a href="/login">Login</a></li>
                <li><a href="/create-account">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div style="text-align: center; margin-top: 100px;">
        <h1>Willkommen bei Yalp!</h1>
        <p>Die soziale Plattform für Menschen, die keine sozialen Plattformen mögen.</p>
    </div>
</body>
</html>