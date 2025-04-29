<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InvestTracker - User Management</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js" defer></script>
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="logo">InvestTracker</div>
            <div class="search-bar">
                <input type="text" placeholder="Search">
                <button>🔍</button>
            </div>
            <div class="nav-buttons">
                <button class="active">Users</button>
                <button>Favorites</button>
                <button>Settings</button>
            </div>
        </header>

        <main class="search-results-table">
            <table>
                <thead>
                    <tr>
                        <th>E-mail</th>
                        <th>Reset password</th>
                        <th>Account type</th>
                        <th>Confirm changes</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="email" value="e@mail.com" readonly></td>
                        <td><button class="reset-button">Reset password</button></td>
                        <td>
                            <select>
                                <option selected>User</option>
                                <option>Moderator</option>
                                <option>Administrator</option>
                            </select>
                        </td>
                        <td>
                            <button class="round red">✕</button>
                            <button class="round green">✓</button>
                        </td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td><input type="email" value="e2@mail.com" readonly></td>
                        <td><button class="reset-button">Reset password</button></td>
                        <td>
                            <select>
                                <option>User</option>
                                <option>Moderator</option>
                                <option selected>Administrator</option>
                            </select>
                        </td>
                        <td>
                            <button class="round red">✕</button>
                            <button class="round green">✓</button>
                        </td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td><input type="email" value="e3@mail.com" readonly></td>
                        <td><button class="reset-button">Reset password</button></td>
                        <td>
                            <select>
                                <option selected>User</option>
                                <option>Moderator</option>
                                <option>Administrator</option>
                            </select>
                        </td>
                        <td>
                            <button class="round red">✕</button>
                            <button class="round green">✓</button>
                        </td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td><input type="email" value="e4@mail.com" readonly></td>
                        <td><button class="reset-button">Reset password</button></td>
                        <td>
                            <select>
                                <option selected>User</option>
                                <option>Moderator</option>
                                <option>Administrator</option>
                            </select>
                        </td>
                        <td>
                            <button class="round red">✕</button>
                            <button class="round green">✓</button>
                        </td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td><input type="email" value="e5@mail.com" readonly></td>
                        <td><button class="reset-button">Reset password</button></td>
                        <td>
                            <select>
                                <option selected>User</option>
                                <option>Moderator</option>
                                <option>Administrator</option>
                            </select>
                        </td>
                        <td>
                            <button class="round red">✕</button>
                            <button class="round green">✓</button>
                        </td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td><input type="email" value="e6@mail.com" readonly></td>
                        <td><button class="reset-button">Reset password</button></td>
                        <td>
                            <select>
                                <option selected>User</option>
                                <option>Moderator</option>
                                <option>Administrator</option>
                            </select>
                        </td>
                        <td>
                            <button class="round red">✕</button>
                            <button class="round green">✓</button>
                        </td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td><input type="email" value="e7@mail.com" readonly></td>
                        <td><button class="reset-button">Reset password</button></td>
                        <td>
                            <select>
                                <option selected>User</option>
                                <option>Moderator</option>
                                <option>Administrator</option>
                            </select>
                        </td>
                        <td>
                            <button class="round red">✕</button>
                            <button class="round green">✓</button>
                        </td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td><input type="email" value="e8@mail.com" readonly></td>
                        <td><button class="reset-button">Reset password</button></td>
                        <td>
                            <select>
                                <option selected>User</option>
                                <option>Moderator</option>
                                <option>Administrator</option>
                            </select>
                        </td>
                        <td>
                            <button class="round red">✕</button>
                            <button class="round green">✓</button>
                        </td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
