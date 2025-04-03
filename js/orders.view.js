window.addEventListener('load', () => {
    let searchTimeout, searchTimeoutBrands, searchTimeoutInventory, typeConfirmMessage, typeSend;

    const orderInt = parseFloat(order);
    const isNew = (!isNaN(order) && (orderInt | 0) === orderInt);

    const registerFormData             = document.getElementById('order_itens');
    const clientSearch                 = document.getElementById('client');
    const clientSearchClose            = clientSearch.querySelector('.close');
    const clientSearchInput            = clientSearch.querySelector('input[name="search"]');
    const clientSearchList             = clientSearch.querySelector('.client-list');
    const clientSearchPagination       = clientSearch.querySelector('.pagination-list');
    const registerItemList             = registerFormData.querySelector('.order_itens_list');
    const registerId                   = registerFormData.querySelector('input[name="id"]');
    const registerName                 = registerFormData.querySelector('input[name="name"]');
    const registerNameDataList         = registerFormData.querySelector('div[name="datalist_brands"]');
    const registerDate                 = registerFormData.querySelector('input[name="date"]');
    const registerAction               = registerFormData.querySelector('input[name="action"]');
    const registerClientId             = registerFormData.querySelector('input[name="client_id"]');
    const registerClientName           = registerFormData.querySelector('input[name="client_name"]');
    const registerClientPhones         = registerFormData.querySelector('input[name="client_phones"]');
    const registerDelete               = registerFormData.querySelector('button[name="delete_order"]');
    const registerDetails              = registerFormData.querySelector('textarea[name="details"]');
    const registerStatus               = registerFormData.querySelector('select[name="status"]');
    const registerAddItem              = registerFormData.querySelector('button[name="add_item"]');
    const registerItemTotalValue       = registerFormData.querySelector('input[name="value"]');
    const registerFirstItemDescription = registerFormData.querySelectorAll('input[name="items[]"]')[0];
    const registerFirstItemValue       = registerFormData.querySelectorAll('input[name="values[]"]')[0];

    const sumAllValues = () => {
        const registerFormDataValues = registerFormData.querySelectorAll('input[name="values[]"]');
        let total = 0;

        registerFormDataValues.forEach(input => {
            total += Math.round(parseFloat(input.value.replace(/[^0-9,]+/g, '').replace(',', '.') * 100));
        });

        registerItemTotalValue.value = formatCurrency(String(total));
    };

    const addClients = (n = '', p = '', id = '') => {
        const clientPhonesList = JSON.parse(p);

        const item       = document.createElement('li');
        const itemName   = document.createElement('p');
        const itemPhones = document.createElement('p');

        item.style.cursor         = 'pointer';
        item.style.padding        = '5px 2px 5px 10px';
        item.style.height         = 'initial';
        item.style.justifyContent = 'space-between';

        itemName.textContent = n;
        itemPhones.innerHTML = clientPhonesList.join(/,/g, '<br>');

        item.appendChild(itemName);
        item.appendChild(itemPhones);
        clientSearchList.appendChild(item);

        item.addEventListener('click', () => {
            registerClientId.value     = id;
            registerClientName.value   = n;
            registerClientPhones.value = clientPhonesList.length > 0 ? 
                clientPhonesList.join(', ') : 'Sem contatos disponíveis';

            clientSearch.classList.remove('show');
        });
    };

    const addItems = (n = '', v = 'R$ 0,00', after = true) => {
        const item       = document.createElement('label');
        const itemName   = document.createElement('input');
        const itemValue  = document.createElement('input');
        const itemDelete = document.createElement('button');

        itemDelete.textContent = '×';

        itemName.type   =
        itemValue.type  = 'text';
        itemDelete.type = 'button';

        itemName.required  =
        itemValue.required = true;

        itemName.name  = 'items[]';
        itemValue.name = 'values[]';

        itemName.value  = n;
        itemValue.value = v;

        itemName.placeholder  = 'Descrição';
        itemValue.placeholder = 'R$ 0,00';

        item.appendChild(itemName);
        item.appendChild(itemValue);
        item.appendChild(itemDelete);

        if (after) {
            registerItemList.firstElementChild.after(item);
        } else {
            registerItemList.appendChild(item);
        };

        itemDelete.addEventListener('click', () => {
            item.remove();
            sumAllValues();
        });

        itemValue.addEventListener('input', event => {
            event.target.value = formatCurrency(event.target.value);
            sumAllValues();
        });

    };

    const handleAction = (action, confirmMessage = null) => {
        if(confirmMessage) {
            if (!confirm(confirmMessage)) return false;
        };

        registerAction.value = action;
        const formData = new FormData(registerFormData);
        handleSubmitGlobal(formData, 'php/orders.php', (data) => {
            if (data.error) {
                alert(data.error);
                return;
            };
    
            if (data.last) window.location.href = '?page=orders&view=' + data.last;
        });
        
        return true;
    };

    const searchClientes = (page = 1) => {
        const formData = new FormData();
        formData.append('search', clientSearchInput.value);
        formData.append('page', page);

        handleSubmitGlobal(formData, 'php/clients.php', (data) => {
            if (data.error) {
                alert(data.error);
                return;
            };

            while (clientSearchList.firstChild) clientSearchList.removeChild(clientSearchList.firstChild);
            data.records.forEach(e => addClients(e.name, e.phones, e.id));

            paginationGlobal(data.pages.total, data?.currentPage, clientSearchPagination, (page) => {
                searchClientes(page);
            });
        });
    };

    registerAddItem.addEventListener('click', () => {
        addItems(
            registerFirstItemDescription.value,
            registerFirstItemValue.value
        );

        registerFirstItemDescription.value = '';
        registerFirstItemValue.value       = 'R$ 0,00';

        sumAllValues();
    });

    registerFirstItemValue.addEventListener('input', event => {
        event.target.value = formatCurrency(event.target.value);
        sumAllValues();
    });

    registerDelete.addEventListener('click', () => {
        if (handleAction('del', 'Tem certeza? Isso não poderá ser desfeito')) {
            window.location.href = '?page=orders';
        };
    });

    registerClientName.addEventListener('click', () => {
        clientSearch.classList.add('show');
    });

    clientSearchClose.addEventListener('click', () => {
        clientSearch.classList.remove('show');
    });

    clientSearchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchClientes();
        }, 400);
    });

    searchClientes();


    const clearCustomDataList = customDataList => {
        while (customDataList.firstChild) customDataList.removeChild(customDataList.firstChild);
    };
    
    registerName.addEventListener("focus", () => {
        registerName.addEventListener("input", event => {
            
            if (event.target.value == '') {
                registerNameDataList.style.display = 'none';
                return;
            }

            clearTimeout(searchTimeoutBrands);
            searchTimeoutBrands = setTimeout(() => {
            
                const formData = new FormData();
                formData.append("search", event.target.value);
                
                handleSubmitGlobal(formData, 'php/inventory.models.php', (data) => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    clearCustomDataList(registerNameDataList);
    
                    const options = data.records.map(e => capitalizeFirstLetter(e.type + " " + e.brand + " " + e.name));

                    registerNameDataList.style.display = (options.length > 0 && event.target.value != '') ? 'flex' : 'none';

                    options.forEach(option => {
                        const suggestionModel = document.createElement('li');
                        suggestionModel.textContent = option;
                        registerNameDataList.appendChild(suggestionModel);
                        suggestionModel.addEventListener('click', () => {
                            clearCustomDataList(registerNameDataList);
                            registerNameDataList.style.display = 'none';
                            registerName.value = option;
                            registerName.parentElement.blur();
                        })
                    });
                });

            });
        });
    });
    

    clientSearchList.style.overflow     = 'auto';
    clientSearchList.style.maxHeight    = '300px';
    clientSearchList.style.paddingRight = '10px';

    if (isNew) {
        typeSend           = 'edit'
        typeConfirmMessage = 'Confirmar alteração?'

        const formData = new FormData(registerFormData);
        formData.append('action', 'get');
        formData.append('id', order);

        handleSubmitGlobal(formData, 'php/orders.php', (data) => {
            if (data.error) {
                alert(data.error);
                return;
            };
    
            const phonesList = JSON.parse(data.records[0]['client_phones']);
            registerClientPhones.value = phonesList.length > 0 ? 
                phonesList.join(', ') : 'Sem contatos disponíveis';

            registerClientId.value     = data.records[0]['client_id'];
            registerClientName.value   = data.records[0]['client_name'];
            registerStatus.value       = data.records[0]['status'];
            registerDetails.value      = data.records[0]['details'];
            registerName.value         = data.records[0]['name'];
            registerId.value           = data.records[0]['id'];
            registerDate.value         = data.records[0]['order_date'];
            registerAction.value       = 'edit';

            JSON.parse(data.records[0]['items'])
                .forEach((e, i) => {
                    if (i !== 0) {
                        addItems(e.item, formatCurrency(String(e.value)), false);
                        return;
                    };

                    registerFirstItemDescription.value = e.item;
                    registerFirstItemValue.value = formatCurrency(String(e.value));
                });

            sumAllValues();
        });
    } else {
        typeSend           = 'new'
        typeConfirmMessage = 'Deseja adicionar?'
        registerDate.value = today;
    };

    registerFormData.addEventListener('submit', event => {
        event.preventDefault();
        handleAction(typeSend, typeConfirmMessage);
    });

    registerDate.max = today;
});