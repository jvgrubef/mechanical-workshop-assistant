window.addEventListener('load', async () => {
    let searchTimeout, selected = [];
    let cacheSearchModels, cacheModelsIds = [];

    const inventoryList             = document.getElementById('inventory_list');
    const registerNew               = document.getElementById('register_new');
    const toggleModels              = document.getElementById('toggle_models');
    const paginationList            = document.getElementById('pagination');
    const registerForm              = document.getElementById('register');
    const modelsSearch              = document.getElementById('models');
    const searchInput               = document.getElementById('search');
    const searchModelInput          = document.getElementById('search_model');
    const registerFormData          = registerForm.querySelector('form');
    const registerClose             = registerForm.querySelector('.close');
    const registerEdit              = registerForm.querySelector('button[name="edit"]');
    const registerInsert            = registerForm.querySelector('button[name="insert"]');
    const registerDelete            = registerForm.querySelector('button[name="delete"]');
    const registerAction            = registerForm.querySelector('input[name="action"]');
    const registerId                = registerForm.querySelector('input[name="id"]');
    const registerDescription       = registerForm.querySelector('input[name="description"]');
    const registerPriceFake         = registerForm.querySelector('input[name="price_fake"]');
    const registerPrice             = registerForm.querySelector('input[name="price"]');
    const registerQuantity          = registerForm.querySelector('input[name="quantity"]');
    const registerCompatibleDisplay = registerForm.querySelector('input[name="compatible_display"]');
    const registerCompatible        = registerForm.querySelector('input[name="compatible"]');
    const modelsSearchClose         = modelsSearch.querySelector('.close');
    const modelsSearchInput         = modelsSearch.querySelector('input[name="search"]');
    const modelsSearchList          = modelsSearch.querySelector('.models-list');
    const modelsSearchPagination    = modelsSearch.querySelector('.pagination-list');

    const handleAction = async (action, confirmMessage = null) => {
        if (permsLocal < 2) return;

        if(confirmMessage) {
            if (!await customConfirm(confirmMessage, 'Claro', 'Cancelar')) return;
        };

        registerAction.value = action;
        const formData = new FormData(registerFormData);
        handleSubmitGlobal(formData, 'php/inventory.php', (data) => {
            if (data.error) {
                customAlert(data.error);
                return;
            };
    
            fetchRecords();
            resetRegister();
        });
    };

    const listModels = input => {
        const item      = document.createElement('li');
        const itemName  = document.createElement('p');
        const itemType  = document.createElement('p');
        const itemBrand = document.createElement('p');
        const itemCheck = document.createElement('input');
        const itemDiv   = document.createElement('div');

        itemCheck.type = 'checkbox';
        itemCheck.readonly = true;

        if (selected[input.id]) {
            selected[input.id] = input.name;
            itemCheck.checked = true;
        };

        item.style.cursor            = 'pointer';
        item.style.padding           = '5px 2px 5px 10px'; 
        item.style.height            = 'initial';
        itemName.style.minWidth      = '60px';
        itemType.style.textTransform = 'capitalize';
        itemType.style.minWidth      = '100px';
        itemBrand.style.minWidth     = '100px';
        itemCheck.style.width        = 
        itemCheck.style.height       = '1em';
        itemCheck.style.margin       = '0px 8px 0px 0px';
        itemDiv.style.display        = 'flex';
        itemDiv.style.flex           = '1';
        itemDiv.style.justifyContent = 'space-between';

        itemName.textContent  = input.name;
        itemType.textContent  = input.type;
        itemBrand.textContent = input.brand;

        itemDiv.appendChild(itemName);
        itemDiv.appendChild(itemType);
        itemDiv.appendChild(itemBrand);
        item.appendChild(itemCheck);
        item.appendChild(itemDiv);
        modelsSearchList.appendChild(item);

        item.addEventListener('click', () => {
            if (itemCheck.checked) {
                itemCheck.checked = false;
                delete selected[input.id];
            } else {
                itemCheck.checked = true;
                selected[input.id] = input.name;
            };

            registerCompatibleDisplay.value = 
                selected.filter(el => 
                    el !== undefined && 
                    el !== null && 
                    el !== ''
                ).join(', ');

            registerCompatible.value = 
                Object.keys(selected)
                    .join(', ');

        });
    };

    const listInput = input => {
        const item         = document.createElement('li');
        const itemEnd      = document.createElement('div');
        const itemEdit     = document.createElement('a');
        const itemName     = document.createElement('p');
        const itemModels   = document.createElement('p');
        const itemValue    = document.createElement('p');
        const itemQuantity = document.createElement('p');
        const itemIcon     = document.createElement('img');

        itemName.style.minWidth    = '200px';
        itemValue.style.marginLeft = '10px';
        itemValue.style.minWidth   = '90px';

        itemName.className     = "description";
        itemModels.className   = "models";
        itemValue.className    = "value";
        itemQuantity.className = "quantity";
        itemEnd.className      = "end"
        itemEdit.className     = "edit"

        itemName.textContent     = input.description;
        itemModels.textContent   = input.models;
        itemValue.textContent    = formatCurrency(input.value);
        itemQuantity.textContent = 'Qtd: ' + input.quantity;

        itemIcon.src = 'img/icons/ellipsis-vertical.svg';

        itemEdit.appendChild(itemIcon);
        itemEnd.appendChild(itemQuantity);
        itemEnd.appendChild(itemValue);

        if (permsLocal > 1) itemEnd.appendChild(itemEdit);
        
        item.appendChild(itemName);
        item.appendChild(itemModels);
        item.appendChild(itemEnd);
        inventoryList.appendChild(item);
    
        itemEdit.addEventListener("click", () => {
            registerId.value                = input.id;
            registerDescription.value       = input.description;
            registerPriceFake.value         = formatCurrency(bigDecimal.multiply(input.value, '100'));
            registerPrice.value             = input.value;
            registerQuantity.value          = input.quantity;
            registerCompatible.value        = input.compatible;
            registerCompatibleDisplay.value = input.models;

            registerDelete.style.display = "initial";
            registerEdit.style.display   = "initial";
            registerInsert.style.display = "none";

            const compatible = input.compatible.split(",").map(Number);
            compatible.forEach(v => {
                selected[v] = true;
            });

            registerForm.classList.add("show");
        });
    };

    const searchModels = (page = 1) => {
        const formData = new FormData();
        formData.append('search', modelsSearchInput.value.trim());
        formData.append('page', page);

        handleSubmitGlobal(formData, 'php/inventory.models.php', (data) => {
            if (data.error) {
                customAlert(data.error);
                return;
            };

            clearCustomListGLobal(modelsSearchList);
            data.records.forEach(e => listModels(e));

            paginationGlobal(data.pages.total, data?.currentPage, modelsSearchPagination, (page) => {
                searchModels(page);
            });
        });
    };

    const searchModelsIds = () => {
        return new Promise((resolve, reject) => {    
            if (searchModelInput.value.trim() === '') {
                cacheSearchModels = '';
                cacheModelsIds = [];
                return resolve(cacheModelsIds);
            };

            if (cacheSearchModels == searchModelInput.value.trim()) {
                return resolve(cacheModelsIds);
            };
    
            cacheSearchModels = searchModelInput.value.trim();
    
            const formData = new FormData();
            formData.append('search', cacheSearchModels);
    
            handleSubmitGlobal(formData, 'php/inventory.models.php', (data) => {
                if (data.error) {
                    customAlert(data.error);
                    return reject(data.error);
                }
                console.log('pesquisa realizada');

                cacheModelsIds = data.records.map(e => e.id);
                resolve(cacheModelsIds);
            });
        });
    };

    const fetchRecords = async () => {
        await searchModelsIds().then(e => {
            const searchFromHash = getSearchFromHash();

            const formData = new FormData();
            if(cacheModelsIds.length > 0) cacheModelsIds.forEach(id => formData.append('models[]', id));
            if(searchFromHash.search) formData.append("search", searchFromHash.search);
            if(searchFromHash.for) formData.append("for", searchFromHash.for);
            if(searchFromHash.page) formData.append("page", searchFromHash.page);
    
            handleSubmitGlobal(formData, 'php/inventory.php', (data) => {
                if (data.error) {
                    customAlert(data.error);
                    return;
                };
    
                clearCustomListGLobal(inventoryList);
                data.records.forEach(e => listInput(e));
    
                paginationGlobal(data.pages.total, data?.currentPage, paginationList, (page) => {
                    window.location.hash = '?' + (
                        (searchFromHash.search || searchFromHash.for) ? 
                            'search=' + searchFromHash.search + 
                            '&for=' + searchModelInput.value + '&' : 
                            ''
                    ) + 'page=' + page;
                });
            });
        });       
    };

    const resetRegister = () => {
        registerForm.classList.remove("show");
        selected = [];

        registerAction.value            = 'new';
        registerId.value                = '';
        modelsSearchInput.value         = '';
        registerDescription.value       = '';
        registerPriceFake.value         = '';
        registerPrice.value             = '';
        registerQuantity.value          = '';
        registerCompatibleDisplay.value = '';
        registerCompatible.value        = '';

        registerInsert.style.display = 'initial';
        registerDelete.style.display = 'none';
        registerEdit.style.display   = 'none';
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            window.location.hash = '?search=' + searchInput.value + '&for=' + searchModelInput.value;
        }, 400);
    });
    searchModelInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            window.location.hash = '?search=' + searchInput.value + '&for=' + searchModelInput.value;
        }, 400);
    });
    modelsSearchClose.addEventListener('click', () => {
        registerForm.classList.add("show");
        modelsSearch.classList.remove("show");
    });
    modelsSearchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchModels();
        }, 400);
    });    
    registerCompatibleDisplay.addEventListener('click', () => { 
        searchModels();
        registerForm.classList.remove("show");
        modelsSearch.classList.add("show");
    });
    registerPriceFake.addEventListener('input', event => {
        event.target.value = formatCurrency(event.target.value);
        registerPrice.value = event.target.value
            .replace(/[^0-9,]+/g, '')
            .replace(',', '.');
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

    toggleModels.addEventListener('click', () => window.location.href = '?page=inventory&models');

    window.addEventListener('hashchange', () => {
        const newsearchFromHash = getSearchFromHash();
        searchInput.value = newsearchFromHash.search ?? '';
        fetchRecords();
    });

    const searchFromHash = getSearchFromHash();
    searchInput.value = searchFromHash.search ?? '';

    fetchRecords();

    modelsSearchList.style.overflow        = 'auto';
    modelsSearchList.style.maxHeight       = '300px';
    modelsSearchList.style.paddingRight    = '10px';
    registerCompatibleDisplay.style.cursor = 'pointer';
});