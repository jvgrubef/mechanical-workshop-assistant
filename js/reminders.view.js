window.addEventListener('load', () => {
    let typeConfirmMessage, typeSend;

    const reminderInt = parseFloat(reminder);
    const isNew = (!isNaN(reminder) && (reminderInt | 0) === reminderInt);

    const registerFormData   = document.getElementById('order_itens');
    const registerId         = registerFormData.querySelector('input[name="id"]');
    const registerTitle      = registerFormData.querySelector('input[name="title"]');
    const registerDate       = registerFormData.querySelector('input[name="date"]');
    const registerDay        = registerFormData.querySelector('select[name="day"]');
    const registerDeadLine   = registerFormData.querySelector('input[name="deadline"]');
    const registerAction     = registerFormData.querySelector('input[name="action"]');
    const registerDelete     = registerFormData.querySelector('button[name="delete"]');
    const registerDetails    = registerFormData.querySelector('textarea[name="details"]');
    const registerPeriod     = registerFormData.querySelector('select[name="period"]');
    const registerImportance = registerFormData.querySelector('select[name="importance"]');

    const handleAction = (action, confirmMessage = null) => {
        if(confirmMessage) {
            if (!confirm(confirmMessage)) return false;
        };

        registerAction.value = action;
        const formData = new FormData(registerFormData);
        handleSubmitGlobal(formData, 'php/reminders.php', (data) => {
            if (data.error) {
                alert(data.error);
                return;
            };
    
            if (data.last) window.location.href = '?page=reminders&view=' + data.last;
        });
        
        return true;
    };

    const displayOptionDate = typeDate => {
        switch (typeDate) {
            case '0':
                registerDate.disabled = false;
                registerDeadLine.disabled = true;
                registerDay.disabled = true;

                registerDate.parentElement.classList.remove('hide');
                registerDeadLine.parentElement.classList.add('hide');
                registerDay.parentElement.classList.add('hide');

                break;
            case '1':
                registerDate.disabled = false;
                registerDeadLine.disabled = false;
                registerDay.disabled = true;

                registerDate.parentElement.classList.remove('hide');
                registerDeadLine.parentElement.classList.remove('hide');
                registerDay.parentElement.classList.add('hide');

                break;
            case '2':
                registerDate.disabled = true;
                registerDeadLine.disabled = true;
                registerDay.disabled = false;

                registerDate.parentElement.classList.add('hide');
                registerDeadLine.parentElement.classList.add('hide');
                registerDay.parentElement.classList.remove('hide');

                break;
            case '3':
                registerDate.disabled = true;
                registerDeadLine.disabled = true;
                registerDay.disabled = true;

                registerDate.parentElement.classList.add('hide');
                registerDeadLine.parentElement.classList.add('hide');
                registerDay.parentElement.classList.add('hide');

                break;
        };
    }
    registerDelete.addEventListener('click', () => {
        if (handleAction('del', 'Tem certeza? Isso não poderá ser desfeito')) {
            window.location.href = '?page=reminders';
        };
    });

    registerPeriod.addEventListener('change', event => displayOptionDate(event.target.value));

    if (isNew) {
        typeSend           = 'edit';
        typeConfirmMessage = 'Confirmar alteração?';

        const formData = new FormData();
        formData.append('action', 'get');
        formData.append('id', reminder);
        
        handleSubmitGlobal(formData, 'php/reminders.php', (data) => {
            if (data.error) {
                alert(data.error);
                return;
            };

            displayOptionDate(String(data.records[0]['reminder_type']));

            registerPeriod.value     = data.records[0]['reminder_type']     || '0';
            registerDate.value       = data.records[0]['reminder_date']     || today;
            registerDeadLine.value   = data.records[0]['reminder_deadline'] || today;
            registerDay.value        = data.records[0]['reminder_day']      || '1';
            registerTitle.value      = data.records[0]['title'];
            registerDetails.value    = data.records[0]['description'];
            registerImportance.value = data.records[0]['reminder_category'];
            registerId .value        = data.records[0]['id']
        });
    } else {
        typeSend               = 'new'
        typeConfirmMessage     = null;
        registerDate.value     = 
        registerDeadLine.value = today;
    };

    registerFormData.addEventListener('submit', event => {
        event.preventDefault();
        handleAction(typeSend, typeConfirmMessage);
    });
});