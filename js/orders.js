window.addEventListener('load', () => {
    let searchTimeout, clientTarget = null;

    const ordersList     = document.getElementById('orders_list');
    const registerNew    = document.getElementById('register_new');
    const onlyClient     = document.getElementById('only_client');
    const paginationList = document.getElementById('pagination');
    const searchInput    = document.getElementById('search');

    const orderStatusList = [
        'Em espera',
        'Autorizado',
        'Em andamento',
        'A receber',
        'Finalizado',
        'Cancelado'
    ];

    const listInput = input => {
        const item       = document.createElement('li');
        const itemEnd    = document.createElement('div');
        const itemEdit   = document.createElement('a');
        const itemName   = document.createElement('p');
        const itemStatus = document.createElement('p');
        const itemClient = document.createElement('p');
        const itemIcon   = document.createElement('img');

        item.style.padding = '5px 2px 5px 10px';
        item.style.height  = 'initial';
        itemName.style.width   = '280px';
        itemClient.style.width = '150px';

        if (input.order_status === 4) {
            itemStatus.style.color = '#77DD77';
        } else if (input.order_status === 5) {
            itemStatus.style.color = '#ff0000';
        };

        itemName.className   = "description";
        itemClient.className = "client";
        itemStatus.className = "status";
        itemEnd.className    = "end";
        itemEdit.className   = "edit";

        itemName.textContent   = input.order_name;
        itemClient.textContent = input.client_name;
        itemStatus.textContent = orderStatusList[input.order_status];

        itemIcon.src = 'img/icons/ellipsis-vertical.svg';

        itemEdit.appendChild(itemIcon);
        itemEnd.appendChild(itemStatus);
        itemEnd.appendChild(itemEdit);
        item.appendChild(itemName);
        item.appendChild(itemClient);
        item.appendChild(itemEnd);
        ordersList.appendChild(item);

        item.addEventListener("click", () => {
            window.location.href = '?page=orders&view=' + input.order_id;
        });
    };

    const fetchRecords = () => {
        const searchFromHash = getSearchFromHash();
        const formData = new FormData();

        if (searchFromHash.search) formData.append("search", searchFromHash.search);
        if (searchFromHash.page) formData.append("page", searchFromHash.page);
        if (clientTarget) formData.append("client", clientTarget);

        handleSubmitGlobal(formData, 'php/orders.php', (data) => {
            if (data.error) {
                alert(data.error);
                return;
            };

            while (ordersList.firstChild) ordersList.removeChild(ordersList.firstChild);
            data.records.forEach(e => listInput(e));

            paginationGlobal(data.pages.total, data?.currentPage, paginationList, (page) => {
                window.location.hash = (searchFromHash.search ? 'search=' + searchFromHash.search + '&' : '') + 'page=' + page;
            });
        });
    };

    registerNew.addEventListener('click', () => {
        window.location.href = '?page=orders&view=new';
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            window.location.hash = '?search=' + searchInput.value;
        }, 400);
    });

    window.addEventListener('hashchange', () => {
        const newSearchFromHash = getSearchFromHash();

        if (newSearchFromHash.search) {
            searchInput.value = newSearchFromHash.search;
        }

        fetchRecords();
    });

    const searchFromHash = getSearchFromHash();

    if (searchFromHash.search) {
        searchInput.value = searchFromHash.search;
    }

    if (searchFromHash.client_id && searchFromHash.client_name) {
        window.location.hash = '';
        clientTarget = searchFromHash.client_id;
        onlyClient.querySelector('b').textContent = searchFromHash.client_name;
        onlyClient.style.display = 'block';
    };

    onlyClient.addEventListener('click', () => {
        clientTarget = null;
        onlyClient.style.display = 'none';
        onlyClient.querySelector('b').textContent = '';
        fetchRecords();
    });

    fetchRecords();
});