window.addEventListener('load', () => {
    const loginForm     = document.getElementById('login');
    const loginFormData = loginForm.querySelector('.form-content');
    const loginMessage  = loginForm.querySelector('.form-message');
    const inputPassword = loginForm.querySelector('input[name="password"]');

    loginFormData.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(loginFormData);

        fetch('php/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                loginMessage.textContent = data.error;
                inputPassword.value = '';
                return;
            };

            loginMessage.textContent = "Login bem-sucedido!";
            window.location.href = 'index.php';
        })
        .catch(error => {
            console.error('Erro ao fazer login:', error);
            loginMessage.textContent = 'Ocorreu um erro. Tente novamente mais tarde.';
        });
    });
});