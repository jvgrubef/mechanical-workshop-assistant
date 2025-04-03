window.addEventListener('load', () => {
    let searchTimeout;
    const adminLevelList = [
        'Assistente',
        'Funcionário',
        'Gerente',
        'Administrador'
    ];

    const usersList               = document.getElementById('users_list');
    const registerNew             = document.getElementById('register_new');
    const paginationList          = document.getElementById('pagination');
    const registerForm            = document.getElementById('register');
    const searchInput             = document.getElementById('search');
    const registerFormData        = registerForm.querySelector('form');
    const registerClose           = registerForm.querySelector('.close');
    const registerEdit            = registerForm.querySelector('button[name="edit"]');
    const registerInsert          = registerForm.querySelector('button[name="insert"]');
    const registerDelete          = registerForm.querySelector('button[name="delete"]');
    const registerAction          = registerForm.querySelector('input[name="action"]');
    const registerId              = registerForm.querySelector('input[name="id"]');
    const registerUsername        = registerForm.querySelector('input[name="username"]');
    const registerFirstName       = registerForm.querySelector('input[name="first_name"]');
    const registerLastName        = registerForm.querySelector('input[name="last_name"]');
    const registerNewPassword     = registerForm.querySelector('input[name="new_password"]');
    const registerConfirmPassword = registerForm.querySelector('input[name="confirm_password"]');

    const handleAction = (action, confirmMessage = null) => {
        if(confirmMessage) {
            if (!confirm(confirmMessage)) return;
        };

        registerAction.value = action;
        const formData = new FormData(registerFormData);
        handleSubmitGlobal(formData, 'php/users.php', (data) => {
            if (data.error) {
                alert(data.error);
                return;
            };
    
            fetchRecords();
            resetRegister();
        });
    };

    const listInput = input => {
        const item      = document.createElement('li');
        const itemEnd   = document.createElement('div');
        const itemEdit  = document.createElement('a');
        const itemName  = document.createElement('p');
        const itemAdmin = document.createElement('p');
        const itemIcon  = document.createElement('img');

        item.style.padding    = '5px 2px 5px 10px';
        item.style.height     = 'initial';
        itemAdmin.style.width = '100px';

        itemName.className  = "name";
        itemAdmin.className = "admin_level";
        itemEnd.className   = "end"
        itemEdit.className  = "edit"

        itemName.textContent  = input.first_name + ' ' + input.last_name;
        itemAdmin.textContent = adminLevelList[input.admin_level];
        itemIcon.src          = 'img/icons/ellipsis-vertical.svg';
    
        itemEdit.appendChild(itemIcon);
        itemEnd.appendChild(itemAdmin);
        itemEnd.appendChild(itemEdit);

        item.appendChild(itemName);
        item.appendChild(itemEnd);
        
        usersList.appendChild(item);
    
        itemEdit.addEventListener("click", () => {
            registerForm.querySelector('option[value="'+input.admin_level+'"]')
                .selected = true;

            registerNewPassword.required = false;
            registerUsername.value       = input.username
            registerFirstName.value      = input.first_name;
            registerLastName.value       = input.last_name;
            registerId.value             = input.id;

            registerDelete.style.display = "initial";
            registerEdit.style.display   = "initial";
            registerInsert.style.display = "none";

            registerForm.classList.add("show");
        })
    };
    
    const fetchRecords = () => {
        const searchFromHash = getSearchFromHash();
        const formData = new FormData();

        if(searchFromHash.search) formData.append("search", searchFromHash.search);
        if(searchFromHash.page)   formData.append("page", searchFromHash.page);

        handleSubmitGlobal(formData, 'php/users.php', (data) => {
            if (data.error) {
                alert(data.error);
                return;
            };

            while (usersList.firstChild) usersList.removeChild(usersList.firstChild);
            data.records.forEach(e => listInput(e));

            paginationGlobal(data.pages.total, data?.currentPage, paginationList, (page) => {
                window.location.hash = (searchFromHash.search ? 'search=' + searchFromHash.search + '&' : '') + 'page=' + page;
            });
        });
    };

    const resetRegister = () => {
        registerForm.classList.remove("show");

        registerForm.querySelector('option[value="n"]')
            .selected = true;

        registerNewPassword.required = true;
        registerAction.value         = "new";
        registerUsername.value       = ''
        registerFirstName.value      = '';
        registerLastName.value       = '';
        registerId.value             = '';

        registerInsert.style.display = "initial";
        registerDelete.style.display = "none";
        registerEdit.style.display   = "none";
    };

    registerFormData.addEventListener('submit', event => event.preventDefault());
    registerNew.addEventListener('click', () => registerForm.classList.add("show"));
    registerDelete.addEventListener('click', () => handleAction('del', 'Tem certeza? Isso não poderá ser desfeito'));
    registerInsert.addEventListener('click', () => handleAction('new', null));
    registerEdit.addEventListener('click', () => handleAction('edit', 'Confirmar alteração?'));
    registerClose.addEventListener('click', () => resetRegister());

    registerNewPassword.addEventListener('input', event => {
        const passwordValue = event.target.value;

        if (passwordValue.length < 8) {
            if (passwordValue.length == 0) {
                registerConfirmPassword.required = false;
                registerConfirmPassword.value = '';

                event.target.previousElementSibling.textContent = 'Senha (Deixe vazio para não alterar)';
            } else {
                event.target.previousElementSibling.textContent = 'Senha (A nova senha deve conter 8 ou mais caracteres)';
            };
        } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+?])[A-Za-z\d!@#$%^&*()_+?]{8,}$/.test(passwordValue)) {
            event.target.previousElementSibling.textContent = 'Senha (A senha deve conter letras maiúsculas, minúsculas, números e caracteres especiais)';
        } else if (/(\w)\1{2,}/.test(passwordValue) || /1234|abcd/.test(passwordValue)) {
            event.target.previousElementSibling.textContent = 'Senha (A senha não pode conter caracteres repetitivos ou sequências simples)';
        } else {
            event.target.previousElementSibling.textContent = 'Senha (Próximo passo abaixo)';
            registerConfirmPassword.parentElement.style.display = 'flex';
            registerConfirmPassword.required = true;
            return;
        };

        registerConfirmPassword.parentElement.style.display = 'none';
    });

    registerConfirmPassword.addEventListener('input', event => {
        if (event.target.value.length == 0) {
            event.target.previousElementSibling.textContent = 'Confirme a Senha';
        } else {
            if (event.target.value !== registerNewPassword.value) {
                event.target.previousElementSibling.textContent = 'Confirme a Senha (A confirmação está diferente da nova senha)';
            } else{
                event.target.previousElementSibling.textContent = 'Confirme a Senha (Senha confirmada)';
                return;
            };
        };
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            window.location.hash = '?search=' + searchInput.value;
        }, 400);
    });

    window.addEventListener('hashchange', () => {
        const newsearchFromHash = getSearchFromHash();

        if(newsearchFromHash.search) {
            searchInput.value = newsearchFromHash.search;
        };

        fetchRecords();
    });

    const searchFromHash = getSearchFromHash();
    
    if (searchFromHash.search) {
        searchInput.value = searchFromHash.search;
    };

    fetchRecords();
});