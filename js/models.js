window.addEventListener('load', async () => {
    let searchTimeout;

    const modelsList       = document.getElementById('models_list');
    const registerNew      = document.getElementById('register_new');
    const toggleIventory   = document.getElementById('toggle_inventory');
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
    const registerBrand    = registerForm.querySelector('input[name="brand"]');
    const registerType     = registerForm.querySelector('input[name="type"]');

    const handleAction = async (action, confirmMessage = null) => {
        if (permsLocal < 2) return;

        if(confirmMessage) {
            if (!await customConfirm(confirmMessage, 'Claro', 'Cancelar')) return;
        };

        registerAction.value = action;
        const formData = new FormData(registerFormData);
        handleSubmitGlobal(formData, 'php/inventory.models.php', (data) => {
            if (data.error) {
                customAlert(data.error);
                return;
            };
    
            fetchRecords();
            resetRegister();
        });
    };

    const listInput = input => {
        const item        = document.createElement('li');
        const itemEnd     = document.createElement('div');
        const itemEdit    = document.createElement('a');
        const itemName    = document.createElement('p');
        const itemBrand   = document.createElement('p');
        const itemType    = document.createElement('p');
        const itemIcon    = document.createElement('img');

        item.style.padding           = '5px 2px 5px 10px';
        item.style.height            = 'initial';
        itemName.style.width         = '200px';
        itemBrand.style.width        = '150px';
        itemType.style.textTransform = 'capitalize';
        itemName.className           = "description";
        itemBrand.className          = "brand";
        itemEnd.className            = "end";
        itemEdit.className           = "edit";

        itemName.textContent  = input.name;
        itemBrand.textContent = input.brand;
        itemType.textContent  = input.type;

        itemIcon.src = 'img/icons/ellipsis-vertical.svg';

        itemEdit.appendChild(itemIcon);

        if (permsLocal > 1) itemEnd.appendChild(itemEdit);

        item.appendChild(itemName);
        item.appendChild(itemBrand);
        item.appendChild(itemType);
        item.appendChild(itemEnd);
        modelsList.appendChild(item);
    
        itemEdit.addEventListener("click", () => {
            registerId.value    = input.id;
            registerName.value  = input.name;
            registerBrand.value = input.brand;
            registerType.value = input.type;

            registerDelete.style.display = "initial";
            registerEdit.style.display   = "initial";
            registerInsert.style.display = "none";

            registerForm.classList.add("show");
        });
    };

    const fetchRecords = () => {
        const searchFromHash = getSearchFromHash();
        const formData = new FormData();

        if(searchFromHash.search) formData.append("search", searchFromHash.search);
        if(searchFromHash.page) formData.append("page", searchFromHash.page);

        handleSubmitGlobal(formData, 'php/inventory.models.php', (data) => {
            if (data.error) {
                customAlert(data.error);
                return;
            };

            clearCustomListGLobal(modelsList);
            data.records.forEach(e => listInput(e));

            paginationGlobal(data.pages.total, data?.currentPage, paginationList, (page) => {
                window.location.hash = (searchFromHash.search ? 'search=' + searchFromHash.search + '&' : '') + 'page=' + page;
            });
        });
    };

    const resetRegister = () => {        
        registerForm.classList.remove("show");
        registerAction.value = 'new';
        registerId.value     = '';
        registerName.value   = '';
        registerBrand.value  = '';

        registerInsert.style.display = "initial";
        registerDelete.style.display = "none";
        registerEdit.style.display   = "none";
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            window.location.hash = '?search=' + searchInput.value;
        }, 400);
    });

    if (permsLocal > 1) {
        registerNew.addEventListener('click', () => registerForm.classList.add("show"));
        registerFormData.addEventListener('submit', event => event.preventDefault());
        registerDelete.addEventListener('click', async () => await handleAction('del', 'Tem certeza? Isso não poderá ser desfeito'));
        registerInsert.addEventListener('click', async () => await handleAction('new', null));
        registerEdit.addEventListener('click', async () => await handleAction('edit', 'Confirmar alteração?'));
        registerClose.addEventListener('click', () => resetRegister());
    } else {
        registerNew.style.display = 'none';
    };

    toggleIventory.addEventListener('click', () => window.location.href = '?page=inventory');

    window.addEventListener('hashchange', () => {
        const newSearchFromHash = getSearchFromHash();
        searchInput.value = newSearchFromHash.search ?? '';
        fetchRecords();
    });

    const searchFromHash = getSearchFromHash();
    searchInput.value = searchFromHash.search ?? '';

    fetchRecords();
});