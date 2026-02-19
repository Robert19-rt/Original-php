<?php
require_once 'includes/auth.php';
$redirect = $_GET['redirect'] ?? '/';

if (isLoggedIn()) {
    header("Location: $redirect");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход / Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-login">Вход</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-register">Регистрация</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div id="login-form">
                        <h5 class="card-title mb-4">Войдите в аккаунт</h5>
                        <div id="login-error" class="alert alert-danger d-none"></div>
                        <form id="form-login">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Пароль</label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Войти</button>
                        </form>
                    </div>

                    <div id="register-form" class="d-none">
                        <h5 class="card-title mb-4">Регистрация</h5>
                        <div id="register-error" class="alert alert-danger d-none"></div>
                        <form id="form-register">
                            <div class="mb-3">
                                <label class="form-label">Имя</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Пароль</label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Повторите пароль</label>
                                <input type="password" name="password2" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Зарегистрироваться</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('tab-login').onclick = () => {
        document.getElementById('login-form').classList.remove('d-none');
        document.getElementById('register-form').classList.add('d-none');
        document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-login').classList.add('active');
    };
    document.getElementById('tab-register').onclick = () => {
        document.getElementById('register-form').classList.remove('d-none');
        document.getElementById('login-form').classList.add('d-none');
        document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-register').classList.add('active');
    };

    async function submitForm(type, form) {
        const data = new FormData(form);
        data.append('type', type);

        try {
            const res = await fetch('/api/auth.php', {
                method: 'POST',
                body: JSON.stringify(Object.fromEntries(data)),
                headers: { 'Content-Type': 'application/json' }
            });
            const json = await res.json();

            if (json.success) {
                const cart = JSON.parse(localStorage.getItem('cart') || '{"items":[]}');
                if (cart.items.length > 0) {
                    await fetch('/api/cart.php', {
                        method: 'POST',
                        body: JSON.stringify(cart),
                        headers: { 'Content-Type': 'application/json' }
                    });
                    localStorage.removeItem('cart');
                }
                window.location.href = '<?= $redirect ?>';
            } else {
                document.getElementById(type + '-error').textContent = json.error;
                document.getElementById(type + '-error').classList.remove('d-none');
            }
        } catch (e) {
            document.getElementById(type + '-error').textContent = 'Ошибка сети';
            document.getElementById(type + '-error').classList.remove('d-none');
        }
    }

    document.getElementById('form-login').onsubmit = e => {
        e.preventDefault();
        submitForm('login', e.target);
    };
    document.getElementById('form-register').onsubmit = e => {
        e.preventDefault();
        submitForm('register', e.target);
    };
});
</script>
</body>
</html>