window.addEventListener('load', async () => {
    let searchTimeout, searchTimeoutBrands, searchTimeoutInventory, typeConfirmMessage, typeSend;

    const urlParams = new URLSearchParams(window.location.search);
    const order = urlParams.get('view');

    const orderInt = parseInt(order);
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
    const registerModel                = registerFormData.querySelector('input[name="model"]');

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
    const searchMatchModelInventory    = registerFormData.querySelector('input[name="match_model"]');

    const registerFirstItem            = registerFormData.querySelector('label.first-item');
    const registerFirstDataList        = registerFirstItem.querySelector('.custom-datalist');
    const registerFirstItemQuantity    = registerFirstItem.querySelector('input[name="qtd[]"]');
    const registerFirstItemDescription = registerFirstItem.querySelector('input[name="items[]"]');
    const registerFirstItemValue       = registerFirstItem.querySelector('input[name="values[]"]');

    const inventoryInputSearch = (inputDescription, inputValue, dataList) => {
        inputDescription.addEventListener("blur", () => {
            setTimeout(() => {
                dataList.style.display =  'none';
                clearCustomListGLobal(dataList);
            }, 200);
        });
    
        inputDescription.addEventListener("input", event => {
            if (event.target.value == '') {
                dataList.style.display = 'none';
                inputValue.value = 'R$ 0,00';
                sumAllValues();
                return;
            };
    
            clearTimeout(searchTimeoutInventory);
            searchTimeoutInventory = setTimeout(() => {
    
                const formData = new FormData();
                formData.append("search", event.target.value);
    
                if (registerModel.value.length > 0 && searchMatchModelInventory.checked) {
                    formData.append("models[]", registerModel.value);
                };
    
                handleSubmitGlobal(formData, 'php/inventory.php', (data) => {
                    if (data.error) {
                        customAlert(data.error);
                        return;
                    };

                    clearCustomListGLobal(dataList);
                    dataList.style.display = (data.records.length > 0 && event.target.value != '') ? 'flex' : 'none';

                    data.records.forEach(option => {
                        const suggestionModel = document.createElement('li');
                        suggestionModel.textContent = option.description + (option.models ? ' - ' + option.models : '');
                        dataList.appendChild(suggestionModel);

                        suggestionModel.addEventListener('click', () => {
                            inputValue.value = formatCurrency(bigDecimal.multiply(option.value, '100'));
                            inputDescription.value = option.description;
                            inputDescription.blur();
                            sumAllValues();
                        });
                    });
                });
            });
        });
    };

    const sumAllValues = () => {
        const registerFirstItem = registerFormData.querySelectorAll('label.sum-this');
    
        const results = Array.from(registerFirstItem).map(l => {
            const loopQtds = l.querySelector('input[name="qtd[]"]');
            const loopValues = l.querySelector('input[name="values[]"]');
    
            const price = loopValues.value.replace(/[^0-9,]+/g, '').replace(',', '');
    
            const bigPrice = new bigDecimal(price);
            const quantity = new bigDecimal(loopQtds.value || "0");
    
            return quantity.multiply(bigPrice);        
        });
        
        let total = new bigDecimal("0");
        results.forEach(val => total = total.add(val));
        registerItemTotalValue.value = formatCurrency(total.getValue());
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

    const addItems = (n = '', v = 'R$ 0,00', q = '1', after = true) => {
        const item       = document.createElement('label');
        const itemQtd    = document.createElement('input');
        const itemName   = document.createElement('input');
        const itemValue  = document.createElement('input');
        const itemDelete = document.createElement('button');
        const itemDataList = document.createElement('div');

        itemQtd.style.width = '65px';

        itemDelete.textContent = '×';

        item.className  = 'sum-this';
        itemDataList.className = 'custom-datalist';


        itemQtd.type    = 'number';
        itemName.type   =
        itemValue.type  = 'text';
        itemDelete.type = 'button';

        itemName.required  =
        itemValue.required = true;

        itemQtd.name   = 'qtd[]';
        itemName.name  = 'items[]';
        itemValue.name = 'values[]';

        if (permsLocal < 2) {
            itemQtd.readOnly = true;
            itemName.readOnly = true;
            itemValue.readOnly = true;
        };

        itemQtd.value  = q ?? '1';
        itemName.value  = n;
        itemValue.value = v;

        item.placeholder      = '1';
        itemName.placeholder  = 'Descrição';
        itemValue.placeholder = 'R$ 0,00';

        item.appendChild(itemQtd);
        item.appendChild(itemName);
        item.appendChild(itemValue);

        if (permsLocal > 1) item.appendChild(itemDelete);
        
        item.appendChild(itemDataList);

        if (after) {
            registerItemList.firstElementChild.after(item);
        } else {
            registerItemList.appendChild(item);
        };

        if (permsLocal > 1) {
            itemDelete.addEventListener('click', () => {
                item.remove();
                sumAllValues();
            });
            itemQtd.addEventListener('input', event => {
                event.target.value = Math.abs(event.target.value);
                sumAllValues();
            });
            itemValue.addEventListener('input', event => {
                event.target.value = formatCurrency(event.target.value);
                sumAllValues();
            });

            inventoryInputSearch(itemName, itemValue, itemDataList);
        };
    };

    const handleAction = async (action, confirmMessage = null) => {
        if (permsLocal < 2) return;
        
        if(confirmMessage) {
            if (!await customConfirm(confirmMessage, 'Claro', 'Cancelar')) return;
        };

        registerAction.value = action;
        const formData = new FormData(registerFormData);
        handleSubmitGlobal(formData, 'php/orders.php', (data) => {
            if (data.error) {
                customAlert(data.error);
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
                customAlert(data.error);
                return;
            };

            clearCustomListGLobal(clientSearchList)

            data.records.forEach(e => addClients(e.name, e.phones, e.id));

            paginationGlobal(data.pages.total, data?.currentPage, clientSearchPagination, (page) => {
                searchClientes(page);
            });
        });
    };

    if (permsLocal > 1) {
        registerAddItem.addEventListener('click', () => {
            addItems(
                registerFirstItemDescription.value,
                registerFirstItemValue.value,
                registerFirstItemQuantity.value
            );

            registerFirstItemDescription.value = '';
            registerFirstItemValue.value       = 'R$ 0,00';
            registerFirstItemQuantity.value    = '1';

            sumAllValues();
        });
        
        registerFirstItemQuantity.addEventListener('input', event => {
            event.target.value = Math.abs(event.target.value);
            sumAllValues();
        });

        registerFirstItemValue.addEventListener('input', event => {
            console.log('click')
            event.target.value = formatCurrency(event.target.value);
            sumAllValues();
        });

        registerDelete.addEventListener('click', async () => {
            if (await handleAction('del', 'Tem certeza? Isso não poderá ser desfeito')) {
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
        
        registerName.addEventListener("blur", () => {
            setTimeout(() => {
                registerNameDataList.style.display =  'none';
                clearCustomListGLobal(registerNameDataList);
            }, 200);
        });

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
                        customAlert(data.error);
                        return;
                    };

                    registerModel.value = '';
                    clearCustomListGLobal(registerNameDataList);

                    const options = data.records.map(e => ({
                        name: capitalizeFirstLetter(e.type + " " + e.brand + " " + e.name),
                        id: e.id
                    }));

                    registerNameDataList.style.display = (options.length > 0 && event.target.value != '') ? 'flex' : 'none';

                    options.forEach(option => {
                        const suggestionModel = document.createElement('li');
                        suggestionModel.textContent = option.name;
                        registerNameDataList.appendChild(suggestionModel);
                        suggestionModel.addEventListener('click', () => {
                            searchMatchModelInventory.checked = true;
                            registerModel.value = option.id;
                            registerName.value = option.name;
                            registerName.blur();
                        })
                    });
                });

            });
        });

        inventoryInputSearch(
            registerFirstItemDescription, 
            registerFirstItemValue,
            registerFirstDataList
        );
    } else {
        searchMatchModelInventory.disabled = 
        registerFirstItemDescription.readOnly = 
        registerFirstItemQuantity.readOnly =
        registerFirstItemValue.readOnly = 
        registerDetails.readOnly = 
        registerStatus.disabled = 
        registerName.readOnly = 
        registerDate.readOnly = 
            true;

        registerDelete.style.display =
        registerAddItem.style.display = 
            'none';
    };
    
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
                customAlert(data.error);
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
            registerModel.value        = data.records[0]['model'] || 0;
            registerAction.value       = 'edit';

            if (parseInt(registerModel.value) > 0) searchMatchModelInventory.checked = true;

            JSON.parse(data.records[0]['items'])
                .forEach((e, i) => {
                    const item     = e?.item || 'Item desconhecido';
                    const value    = formatCurrency(e?.value || '0');
                    const quantity = e?.quantity || 1;

                    if (i === 0) {
                        registerFirstItemDescription.value = item;
                        registerFirstItemValue.value       = value;
                        registerFirstItemQuantity.value    = quantity;
                        return;
                    };

                    addItems(item, value, quantity, false);                    
                });

            sumAllValues();
        });
    } else {
        if (permsLocal < 2) {
            if (await customConfirm('Você não possui permissões administrativas para criar um orçamento', 'Entendido', false)) {
                window.location.href = '?page=orders';
            };
        };

        typeSend           = 'new'
        typeConfirmMessage = 'Deseja adicionar?'
        registerDate.value = today;
    };

    registerFormData.addEventListener('submit', async event => {
        event.preventDefault();

        if (permsLocal < 2) {
            window.location.href = '?page=orders'
            return;
        };

        await handleAction(typeSend, typeConfirmMessage);
    });

    registerDate.max = today;
});