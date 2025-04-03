window.addEventListener('load', () => {
    const cashbookList            = document.getElementById('cashbook_list');
    const totalBalance            = document.getElementById('total_balance');
    const monthBalance            = document.getElementById('month_balance');
    const registerNew             = document.getElementById('register_new');
    const registerForm            = document.getElementById('register');
    const dateInput               = document.getElementById('date');
    const registerFormData        = registerForm.querySelector('form');
    const registerClose           = registerForm.querySelector('.close');
    const registerEdit            = registerForm.querySelector('button[name="edit"]');
    const registerInsert          = registerForm.querySelector('button[name="insert"]');
    const registerDelete          = registerForm.querySelector('button[name="delete"]');
    const registerAction          = registerForm.querySelector('input[name="action"]');
    const registerId              = registerForm.querySelector('input[name="id"]');
    const registerAmount          = registerForm.querySelector('input[name="amount"]');
    const registerAmountFake      = registerForm.querySelector('input[name="amount_fake"]');
    const registerDescription     = registerForm.querySelector('input[name="description"]');
    const registerTransactionDate = registerForm.querySelector('input[name="transaction_date"]');
    const registerTransactionType = registerForm.querySelectorAll('input[name="transaction_type"]');
    
    const handleAction = (action, confirmMessage = null) => {
        if(confirmMessage) {
            if (!confirm(confirmMessage)) return;
        };

        registerAction.value = action;
        const formData = new FormData(registerFormData);
        handleSubmitGlobal(formData, 'php/cashbook.php', (data) => {
            if (data.error) {
                alert(data.error);
                return;
            };
    
            fetchRecords(registerTransactionDate.value);
            resetRegister();
        });
    };

    const listInput = input => {
        const item       = document.createElement('li');
        const itemEnd    = document.createElement('div');
        const itemEdit   = document.createElement('a');
        const itemName   = document.createElement('p');
        const itemAmount = document.createElement('p');
        const itemIcon   = document.createElement('img');

        item.className       = input.amount > 0 ? 'green' : 'red';
        itemName.className   = "description";
        itemAmount.className = "amount";
        itemEnd.className    = "end"
        itemEdit.className   = "edit"

        itemName.textContent   = input.description;
        itemAmount.textContent = input.amount.replace('.', ',');

        itemIcon.src = 'img/icons/ellipsis-vertical.svg';

        itemEdit.appendChild(itemIcon);
        itemEnd.appendChild(itemAmount);
        itemEnd.appendChild(itemEdit);
        item.appendChild(itemName);
        item.appendChild(itemEnd);
        cashbookList.appendChild(item);
    
        itemEdit.addEventListener("click", () => {
            registerId.value              = input.id;
            registerDescription.value     = input.description;
            registerTransactionDate.value = input.transaction_date;

            const amountValue             = Math.abs(input.amount);
            registerAmount.value          = amountValue;
            registerAmountFake.value      = formatCurrency(String(Math.round(amountValue * 100)));
        
            registerTransactionType[input.amount < 0 ? 1 : 0].checked = true;

            registerDelete.style.display = "initial";
            registerEdit.style.display   = "initial";
            registerInsert.style.display = "none";

            registerForm.classList.add("show");
        });
    };

    const fetchRecords = date => {
        const formData = new FormData();
        formData.append("date", date);

        handleSubmitGlobal(formData, 'php/cashbook.php', (data) => {
            if (data.error) {
                alert(data.error);
                return;
            };

            while (cashbookList.firstChild) cashbookList.removeChild(cashbookList.firstChild);

            data.records.forEach(e => listInput(e));
            totalBalance.value = ('Em caixa: R$ ' + (data?.total_balance ?? '0,00')).replace('.', ',');
            monthBalance.value = ('Lucro do Mês: R$ ' + (data?.month_balance ?? '0,00')).replace('.', ',');
        });
    };

    const getDateFromHash = () => {
        const hash = window.location.hash.substring(1);
        return (hash && isValidDate(hash)) ? hash : false;
    };

    const resetRegister = () => {
        registerForm.classList.remove("show");

        registerAction.value               = "new";
        registerId.value                   = "";
        registerAmount.value               = "";
        registerAmountFake.value           = "";
        registerDescription.value          = "";
        registerTransactionDate.value      = today;
        registerTransactionType[0].checked = true;

        registerInsert.style.display = "initial";
        registerDelete.style.display = "none";
        registerEdit.style.display   = "none";
    };

    registerAmountFake.addEventListener('input', event => {
        event.target.value = formatCurrency(event.target.value);
        registerAmount.value = event.target.value
            .replace(/[^0-9,]+/g, '')
            .replace(',', '.');
    });

    registerNew.addEventListener('click', () => registerForm.classList.add("show"));
    registerFormData.addEventListener('submit', event => event.preventDefault());
    registerDelete.addEventListener('click', () => handleAction('del', 'Tem certeza? Isso não poderá ser desfeito'));
    registerInsert.addEventListener('click', () => handleAction('new', null));
    registerEdit.addEventListener('click', () => handleAction('edit', 'Confirmar alteração?'));
    registerClose.addEventListener('click', resetRegister);
    
    registerTransactionDate.value = 
    registerTransactionDate.max = 
    dateInput.max = 
        today;

    dateInput.addEventListener('change', () => window.location.hash = dateInput.value);

    window.addEventListener('hashchange', () => {
        const newDateFromHash = getDateFromHash();

        if(newDateFromHash) {
            dateInput.value = newDateFromHash;
            fetchRecords(newDateFromHash);
            return;
        };

        window.location.hash = 
        dateInput.value = 
        today;
    });

    const dateFromHash = getDateFromHash();
    
    if (dateFromHash) {
        dateInput.value = dateFromHash;
        fetchRecords(dateFromHash);
        return;
    };

    window.location.hash = 
    dateInput.value = 
    today;
});

