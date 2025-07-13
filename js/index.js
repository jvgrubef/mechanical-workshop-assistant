const hasPermission = (permissions, module) => {
    const permissionsMap = {
        cashbook: 0,
        clients: 2,
        orders: 4,
        inventory: 6,
        reminders: 8,
        users: 10,
    };

    if (!(module in permissionsMap)) return false;

    const shift = permissionsMap[module];
    return (permissions >> shift) & 0b11;
};

const reloadUserImage = path => {
    document.querySelectorAll('.profile-image')
        .forEach(image => {
            image.src = path;
        });
};

const formatCurrency = (value = '0', additionalString = 'R$ ') => {

    var valueClean = new bigDecimal(value.replace(/\D+/g, ''));
    var cents = new bigDecimal(100);
    var result = valueClean.divide(cents, 2);
    var toStr = result.getPrettyValue(3, '.');

    if(toStr == '0') toStr = '0.00';

    return additionalString + toStr.replace(/\.(?=[^\.]*$)/, ',');
};

const getSearchFromHash = () => {
    const params = new URLSearchParams(window.location.hash.substring(1));
    let result = {};

    params.forEach((value, key) => {
        result[key] = value;
    });

    return result;
};

const capitalizeFirstLetter = (val) => {
    return String(val).charAt(0).toUpperCase() + String(val).slice(1);
};

const isValidDate = (dateString) =>  {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(dateString)) return false;

    const objDate = new Date(dateString).getTime();
    const objToday = new Date(today).getTime();

    if (isNaN(objDate))     return false;
    if (objDate > objToday) return false;

    return true;
};

const handleSubmitGlobal = (formData, link, callback = null, callbackError = null, method = 'POST') => {
    fetch(link, {
        method: method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (typeof callback === 'function') callback(data);
    })
    .catch(error => {
        if(typeof callbackError === 'function') callbackError(error);
        console.error('Error:', error);
    });
};

const clearCustomListGLobal = customList => {
    while (customList.firstChild) customList.removeChild(customList.firstChild);
};

const paginationGlobal = (totalPages, currentPage, appendTo, callback = null, maxButtons = 5) => {

    clearCustomListGLobal(appendTo);

    totalPages  = parseInt(totalPages)  || 1;
    currentPage = parseInt(currentPage) || 1;

    if (totalPages < 2) return;

    let startPage = 1;
    let endPage   = totalPages;

    if (totalPages > maxButtons) {

        startPage = Math.max(currentPage - 2, 1);
        endPage   = Math.min(currentPage + 2, totalPages); 

        if (endPage - startPage + 1 < maxButtons) {
            if (startPage === 1) {
                endPage = Math.min(startPage + maxButtons - 1, totalPages);
            } else if (endPage === totalPages) {
                startPage = Math.max(endPage - maxButtons + 1, 1);
            };
        };
    };

    for (let pageIndex = startPage; pageIndex <= endPage; pageIndex++) {
        const page     = document.createElement('li');
        const pageText = document.createElement('a');

        pageText.textContent = pageIndex;

        page.className = "button " + (pageIndex === currentPage ? 'active' : '');

        page.appendChild(pageText);
        appendTo.appendChild(page);

        if (typeof callback === 'function') {
            page.addEventListener("click", () => {
                callback(pageIndex);
            });
        };
    };
};

const customAlert = (message, yesText = 'Ok') => {
    const overlay = document.createElement('div');
    overlay.classList.add('custom-confirm-overlay');
    overlay.id = 'customConfirmOverlay';

    const autoRemove = () => {
        overlay.classList.remove('active');
        setTimeout(()=> document.body.removeChild(overlay), 1100);
    };

    const modal = document.createElement('div');
    modal.classList.add('custom-confirm-modal');

    const buttons = document.createElement('div');
    buttons.classList.add('custom-confirm-buttons');

    const messageElement = document.createElement('p');
    messageElement.id = 'customConfirmMessage';
    messageElement.textContent = message;

    const yesBtn = document.createElement('button');
    yesBtn.classList.add('yes-btn');
    yesBtn.id = 'yesBtn';
    yesBtn.textContent = yesText;

    modal.appendChild(messageElement);
    buttons.appendChild(yesBtn);
    modal.appendChild(buttons);
    overlay.appendChild(modal);

    yesBtn.addEventListener('click', () => autoRemove());

    document.body.insertBefore(overlay, document.body.firstChild);

    // Fix transition
    setTimeout(() => overlay.classList.add('active'), 0);
};

const customConfirm = (message, yesText = 'Sim', noText = false) => {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.classList.add('custom-confirm-overlay');
        overlay.id = 'customConfirmOverlay';

        const autoRemove = () => {
            overlay.classList.remove('active');
            setTimeout(()=> document.body.removeChild(overlay), 1100);
        };
    
        const modal = document.createElement('div');
        modal.classList.add('custom-confirm-modal');

        const buttons = document.createElement('div');
        buttons.classList.add('custom-confirm-buttons');

        const messageElement = document.createElement('p');
        messageElement.id = 'customConfirmMessage';
        messageElement.textContent = message;

        const yesBtn = document.createElement('button');
        yesBtn.classList.add('yes-btn');
        yesBtn.id = 'yesBtn';
        yesBtn.textContent = yesText;

        const noBtn = document.createElement('button');
        noBtn.classList.add('no-btn');
        noBtn.id = 'noBtn';
        noBtn.textContent = noText;

        modal.appendChild(messageElement);
        buttons.appendChild(yesBtn);

        if (noText) buttons.appendChild(noBtn);

        modal.appendChild(buttons);
        overlay.appendChild(modal);

        yesBtn.addEventListener('click', () => {
            autoRemove(); resolve(true);
        });

        noBtn.addEventListener('click', () => {
            autoRemove(); resolve(false);
        });

        document.body.insertBefore(overlay, document.body.firstChild);

        //Fix transition
        setTimeout(()=> overlay.classList.add('active'), 0);
    });
};

window.addEventListener('load', async () => {

    const menuToggle    = document.getElementById('menu_toggle');
    const widgetToggle  = document.getElementById('widget_toggle');
    const mainSide      = document.getElementById('side');
    const widgetProfile = document.getElementById('widget_profile');
    const content       = document.querySelector('.content');

    menuToggle.addEventListener('click', () => mainSide.classList.toggle('show'));
    widgetToggle.addEventListener('click', () => widgetProfile.classList.toggle('show'));

    document.querySelectorAll('input')
        .forEach(input => {
            input.addEventListener('input', event => {
                const maskType = event.target.dataset.mask;

                if (masks[maskType]) {
                    event.target.value = masks[maskType](event.target.value);
                };
            });
        });

    [ 'cashbook', 'clients', 'orders', 'inventory', 'reminders', 'users']
        .forEach(thisPage => {
            const permissionTest = hasPermission(perms, thisPage);
            mainSide.querySelector('.menu .' + thisPage).style.display = permissionTest > 0 ?  'flex' : 'none';
        });

    try {
        const menuSelected = mainSide.querySelector('.menu .' + path) ?? null;
        if (menuSelected) menuSelected.classList.add('active');

        const checkScroll = () => {
            const contentMenu = content.querySelector('.menu') ?? null;
            if (contentMenu) contentMenu.style.boxShadow = content.scrollTop > 0 
                ? '0px 7.5px 10px -10px rgba(0, 0, 0, 0.5)' 
                : 'none';
        };

        content.addEventListener('scroll', checkScroll);
        checkScroll();
    } catch (e) {

    };
});