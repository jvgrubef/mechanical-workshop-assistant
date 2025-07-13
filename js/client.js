window.addEventListener('load', async () => {
    let searchTimeout;

    const clientList       = document.getElementById('client_list');
    const registerNew      = document.getElementById('register_new');
    const paginationList   = document.getElementById('pagination');
    const registerForm     = document.getElementById('register');
    const searchInput      = document.getElementById('search');
    const registerFormData = registerForm.querySelector('form');
    const registerClose    = registerForm.querySelector('.close');
    const registerEdit     = registerForm.querySelector('button[name="edit"]');
    const registerInsert   = registerForm.querySelector('button[name="insert"]');
    const registerDelete   = registerForm.querySelector('button[name="delete"]');
    const registerAction   = registerForm.querySelector('input[name="action"]');
    const registerId       = registerForm.querySelector('input[name="id"]');
    const registerName     = registerForm.querySelector('input[name="name"]');
    const registerPhones   = registerForm.querySelector('input[name="phones"]');
    const registerAddress  = registerForm.querySelector('input[name="address"]');
    const registerRating   = registerForm.querySelector('input[name="rating"]');

    const handleAction = async (action, confirmMessage = null) => {
        if (permsLocal < 2) return;

        if(confirmMessage) {
            if (!await customConfirm(confirmMessage, 'Claro', 'Cancelar')) return;
        };

        registerAction.value = action;
        const formData = new FormData(registerFormData);
        handleSubmitGlobal(formData, 'php/clients.php', (data) => {
            if (data.error) {
                customAlert(data.error);
                return;
            };
    
            fetchRecords();
            resetRegister();
        });
    };

    const listPhones = n => {
        const itemPhone = document.createElement('input');
        const itemDiv   = document.createElement('div');

        itemPhone.type      = 'text';
        itemPhone.readOnly  = true;

        itemPhone.name      = 'phones_list[]';
        itemPhone.value     = n;
        itemPhone.className = 'alias';

        itemDiv.appendChild(itemPhone);
        registerPhones.nextElementSibling.appendChild(itemDiv);

        itemDiv.addEventListener('click', () => {
            itemDiv.remove();
        });

        registerPhones.value = '';
    };

    const listInput = input => {
        const phonesList = JSON.parse(input.phones);

        const item        = document.createElement('li');
        const itemEnd     = document.createElement('div');
        const itemEdit    = document.createElement('a');
        const itemSearch  = document.createElement('a');

        const itemName    = document.createElement('p');
        const itemPhones  = document.createElement('p');
        const itemAddress = document.createElement('p');
        const itemRating  = document.createElement('p');
        const itemIcon    = document.createElement('img');
        const itemISearch = document.createElement('img');

        item.style.padding           = '5px 2px 5px 10px';
        item.style.height            = 'initial';
        itemName.style.width         = '200px';
        itemPhones.style.width       = '150px';
        itemRating.style.marginRight = '8px';

        itemName.className   = 'description';
        itemPhones.className = 'phones';
        itemEnd.className    = 'end';
        itemEdit.className   = 
        itemSearch.className = 'edit';

        itemName.textContent    = input.name;
        itemRating.textContent  = 'S' + input.rating;
        itemAddress.textContent = input.address;
        itemPhones.innerHTML    = phonesList.join('<br>');

        itemIcon.src            = 'img/icons/ellipsis-vertical.svg';
        itemISearch.src         = 'img/icons/search.svg';

        itemEdit.appendChild(itemIcon);
        itemSearch.appendChild(itemISearch);

        itemEnd.appendChild(itemRating);
        itemEnd.appendChild(itemSearch);

        if (permsLocal > 1) itemEnd.appendChild(itemEdit);
        
        item.appendChild(itemName);
        item.appendChild(itemPhones);
        item.appendChild(itemAddress);
        item.appendChild(itemEnd);
        clientList.appendChild(item);
    
        itemEdit.addEventListener('click', () => {
            if (phonesList.length > 1) {
                phonesList.forEach((phone, i) => {
                    if (i > 0) listPhones(phone);
                });
            };

            registerId.value      = input.id;
            registerName.value    = input.name;
            registerPhones.value  = phonesList[0] || '';
            registerRating.value  = input.rating;
            registerAddress.value = input.address;

            registerDelete.style.display = 'initial';
            registerEdit.style.display   = 'initial';
            registerInsert.style.display = 'none';

            registerForm.classList.add('show');
        });

        itemSearch.addEventListener('click', () => 
            window.location.href = '?page=orders#?client_id=' + input.id + '&client_name=' + input.name
        );
    };

    const fetchRecords = () => {
        const searchFromHash = getSearchFromHash();
        const formData = new FormData();

        if(searchFromHash.search) formData.append("search", searchFromHash.search);
        if(searchFromHash.page) formData.append("page", searchFromHash.page);

        handleSubmitGlobal(formData, 'php/clients.php', (data) => {
            if (data.error) {
                customAlert(data.error);
                return;
            };

            clearCustomListGLobal(clientList);
            data.records.forEach(e => listInput(e));

            paginationGlobal(data.pages.total, data?.currentPage, paginationList, (page) => {
                window.location.hash = (searchFromHash.search ? 'search=' + searchFromHash.search + '&' : '') + 'page=' + page;
            });
        });
    };

    const resetRegister = () => {
        clearCustomListGLobal(registerPhones.nextElementSibling);
        
        registerForm.classList.remove('show');
        registerAction.value  = "new";
        registerId.value      = "";
        registerName.value    = '';
        registerPhones.value  = '';
        registerRating.value  = 3;
        registerAddress.value = '';

        registerInsert.style.display = 'initial';
        registerDelete.style.display = 'none';
        registerEdit.style.display   = 'none';
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            window.location.hash = '?search=' + searchInput.value;
        }, 400);
    });

    if (permsLocal > 1) {
        registerNew.addEventListener('click', () => registerForm.classList.add('show'));
        registerFormData.addEventListener('submit', event => event.preventDefault());
        registerDelete.addEventListener('click', async () => await handleAction('del', 'Tem certeza? Isso não poderá ser desfeito'));
        registerInsert.addEventListener('click', async () => await handleAction('new', null));
        registerEdit.addEventListener('click', async () => await handleAction('edit', 'Confirmar alteração?'));
        registerClose.addEventListener('click', () => resetRegister());
    } else {
        registerNew.style.display = 'none';
    }
    window.addEventListener('hashchange', () => {
        const newSearchFromHash = getSearchFromHash();
        searchInput.value = newSearchFromHash.search ?? '';
        fetchRecords();
    });

    const searchFromHash = getSearchFromHash();
    searchInput.value = searchFromHash.search ?? '';

    fetchRecords();

    registerPhones.addEventListener('keydown', event => {
        if (event.key !== ',')              return;
        if (event.target.value.length < 15) return;

        listPhones(event.target.value);
    });
});