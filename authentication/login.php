<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <main style="display: flex; gap: 32px; align-items: flex-start;">
        <section>
            <h2>Login</h2>
            <form action="authenticate.php" method="post">
                <div>
                    <label for="login-email">Email</label><br>
                    <input type="email" id="login-email" name="login_email" maxlength="255" autocomplete="email" required>
                </div>
                <div>
                    <label for="login-password">Password</label><br>
                    <input type="password" id="login-password" name="login_password" minlength="8" maxlength="128" autocomplete="current-password" required>
                </div>
                <button type="submit">Login</button>
            </form>
        </section>

        <section>
            <h2>Create an Account</h2>
            <form action="create_user.php" method="post">
                <div>
                    <label for="register-username">Username</label><br>
                    <input type="text" id="register-username" name="register_username" minlength="3" maxlength="50" pattern="[A-Za-z0-9_]{3,50}" title="Use 3 to 50 characters: letters, numbers, and underscores only." autocomplete="username" required>
                </div>
                <div>
                    <label for="register-email">Email</label><br>
                    <input type="email" id="register-email" name="register_email" maxlength="255" autocomplete="email" required>
                </div>
                <div>
                    <label for="register-password">Password</label><br>
                    <input type="password" id="register-password" name="register_password" minlength="8" maxlength="128" autocomplete="new-password" required>
                </div>
                <div>
                    <label for="register-confirm-password">Confirm Password</label><br>
                    <input type="password" id="register-confirm-password" name="register_confirm_password" minlength="8" maxlength="128" autocomplete="new-password" required>
                </div>
                <button type="submit">Create Account</button>
            </form>
        </section>
    </main>
</body>
</html>