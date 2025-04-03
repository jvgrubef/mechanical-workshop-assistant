window.addEventListener('load', () => {
    const userForm        = document.getElementById('user');
    const uploadImage     = userForm.querySelector('button[name="upload_image"]');
    const newPassword     = userForm.querySelector('input[name="new_password"]');
    const confirmPassword = userForm.querySelector('input[name="confirm_password"]');
    const currentPassword = userForm.querySelector('input[name="current_password"]');

    newPassword.addEventListener('input', event => {
        const passwordValue = event.target.value;
        
        if (passwordValue.length < 8) {
            if (passwordValue.length == 0) {
                confirmPassword.required = currentPassword.required = false;
                confirmPassword.value = currentPassword.value = '';

                event.target.previousElementSibling.textContent = 'Senha (Deixe vazio para não alterar)';
            } else {
                event.target.previousElementSibling.textContent = 'Senha (A nova senha deve conter 8 ou mais caracteres)';
            };
        } 

        else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+?])[A-Za-z\d!@#$%^&*()_+?]{8,}$/.test(passwordValue)) {
            event.target.previousElementSibling.textContent = 'Senha (A senha deve conter letras maiúsculas, minúsculas, números e caracteres especiais)';
        }

        else if (/(\w)\1{2,}/.test(passwordValue) || /1234|abcd/.test(passwordValue)) {
            event.target.previousElementSibling.textContent = 'Senha (A senha não pode conter caracteres repetitivos ou sequências simples)';
        }
        else {
            event.target.previousElementSibling.textContent = 'Senha (Próximo passo abaixo)';
            confirmPassword.parentElement.style.display = 'flex';
            confirmPassword.required = true;
            currentPassword.required = true;

            return;
        };

        confirmPassword.parentElement.style.display = 'none';
        currentPassword.parentElement.style.display = 'none';
    });

    confirmPassword.addEventListener('input', event => {
        if (event.target.value.length == 0) {
            event.target.previousElementSibling.textContent = 'Confirme a Senha (Confirme a senha)';
        } else {
            if (event.target.value !== newPassword.value) {
                event.target.previousElementSibling.textContent = 'Confirme a Senha (A confirmação está diferente da nova senha)';
            } else{
                event.target.previousElementSibling.textContent = 'Confirme a Senha (Próximo passo abaixo)';
                currentPassword.parentElement.style.display = 'flex';

                return;
            };
        };

        currentPassword.parentElement.style.display = 'none';
    });

    userForm.addEventListener('submit', event => {
        event.preventDefault();
        if (!confirm('Confirmar alteração?')) return;

        handleSubmitGlobal(new FormData(userForm), 'php/profile.php', (data) => {
            if (data.error) {
                alert(data.error);
                return;
            };
        });
    });

    uploadImage.addEventListener('click', function() {
        const inputImage = document.createElement('input');
        inputImage.type = 'file';
        inputImage.accept = 'image/*';

        inputImage.addEventListener('change', event => {
            const file = event.target.files[0];

            if (!file) return;

            if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
                alert('Apenas arquivos JPG, PNG ou GIF são permitidos!');
                return;
            };

            const formData = new FormData();
            formData.append('image', file);
            formData.append('submit', 'true'); 

            handleSubmitGlobal(formData, 'php/profile.image.php', (data) => {
                if (data.error) {
                    alert(data.error);
                    return;
                };

                reloadUserImage('img/users/' + data.image.new);
            });
        });

        inputImage.click();
    });
});