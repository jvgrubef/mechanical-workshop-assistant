window.addEventListener('load', () => {
    const menuToggle    = document.getElementById('menu_toggle');
    const widgetToggle  = document.getElementById('widget_toggle');
    const mainSide      = document.getElementById('side');
    const widgetProfile = document.getElementById('widget_profile');

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

    try {
        mainSide.querySelector('.menu .' + path).classList.add('active');
    } catch (e) {

    }
});

const reloadUserImage = path => {
    document.querySelectorAll('.profile-image')
        .forEach(image => {
            image.src = path;
        });
};

const formatCurrency = (value = '0') => {

    const options = {
        style: 'currency',
        currency: 'BRL'
    };
    return new Intl.NumberFormat('pt-br', options)
        .format(value.replace(/\D+/g, '') / 100);
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
}

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

const paginationGlobal = (totalPages, currentPage, appendTo, callback = null, maxButtons = 5) => {

    while (appendTo.firstChild) appendTo.removeChild(appendTo.firstChild);

    totalPages  = parseInt(totalPages)  || 1;
    currentPage = parseInt(currentPage) || 1;

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
    
        page.addEventListener("click", () => {
            if (typeof callback === 'function') callback(pageIndex);
        });
    };
};