window.addEventListener('load', () => {
    let searchTimeout;
    const remindersList  = document.getElementById('reminders_list');
    const registerNew    = document.getElementById('register_new');
    const paginationList = document.getElementById('pagination');
    const searchInput    = document.getElementById('search');

    const categoryListLabels = ['baixa', 'média', 'alta', 'muito alta'];
    const categoryListColors = ['#A8D5BA', '#F1C232', '#FF8C00', '#D32F2F'];

    const dateLocal = date => {
        const d = new Date(date);
        d.setMinutes(d.getMinutes() + d.getTimezoneOffset());
        return d.toLocaleDateString('pt-BR') || '';
    };

    const listInput = input => {
        const item         = document.createElement('li');
        const itemEnd      = document.createElement('div');
        const itemEdit     = document.createElement('a');
        const itemTitle    = document.createElement('p');
        const itemCategory = document.createElement('p');
        const itemDate     = document.createElement('p');
        const itemIcon     = document.createElement('img');

        item.style.padding    = '5px 2px 5px 10px';
        item.style.borderLeft = 'solid 5px ' + categoryListColors[input.reminder_category];
        item.style.height     = 'initial';

        itemTitle.innerHTML   = '<b>' + input.title + '</b><br>Prioridade ' + categoryListLabels[input.reminder_category];

        switch (input.reminder_type) {
            case 0: itemDate.textContent = 'No dia ' + dateLocal(input.reminder_date); break;
            case 1: itemDate.textContent = 'De ' + dateLocal(input.reminder_date) + ' Até ' + dateLocal(input.reminder_deadline); break;
            case 2: itemDate.textContent = 'Todo dia ' + (input.reminder_day <= 9 ? '0': '') + input.reminder_day; break;
            case 3: itemDate.textContent = 'Indeterminado'; break;
        };

        itemTitle.className    = "title";
        itemCategory.className = "category";
        itemDate.className     = "date";
        itemEnd.className      = "end";
        itemEdit.className     = "edit";

        itemIcon.src = 'img/icons/ellipsis-vertical.svg';

        itemEdit.appendChild(itemIcon);
        itemEnd.appendChild(itemDate);
        itemEnd.appendChild(itemEdit);
        item.appendChild(itemTitle);
        item.appendChild(itemCategory);
        item.appendChild(itemEnd);
        remindersList.appendChild(item);

        item.addEventListener("click", () => {
            window.location.href = '?page=reminders&view=' + input.id;
        });
    };

    const fetchRecords = () => {
        const searchFromHash = getSearchFromHash();
        const formData = new FormData();

        if (searchFromHash.search) formData.append("search", searchFromHash.search);
        if (searchFromHash.page) formData.append("page", searchFromHash.page);

        handleSubmitGlobal(formData, 'php/reminders.php', (data) => {
            if (data.error) {
                customAlert(data.error);
                return;
            };

            clearCustomListGLobal(remindersList);
            data.records.forEach(e => listInput(e));

            paginationGlobal(data.pages.total, data?.currentPage, paginationList, (page) => {
                window.location.hash = (searchFromHash.search ? 'search=' + searchFromHash.search + '&' : '') + 'page=' + page;
            });
        });
    };

    if (permsLocal > 1) {
        registerNew.addEventListener('click', () => {
            window.location.href = '?page=reminders&view=new';
        });
    } else {
        registerNew.style.display = 'none';
    };

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

    fetchRecords();
});