import { createApp } from 'vue';

let orderLineCounter = 0;

function formatPrice(value) {
    return Number(value).toFixed(2).replace('.', ',');
}

function normalizeText(value) {
    return String(value ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
}

function getCommonRemarks() {
    return Array.isArray(window.kassaCommonRemarks) ? window.kassaCommonRemarks : [];
}

function getSideDishOptions() {
    return Array.isArray(window.kassaSideDishOptions) ? window.kassaSideDishOptions : [];
}

function getSelectedSideDishExtraPrice(row) {
    const sideDishChoice = row.querySelector('.sideDishChoice');

    if (!sideDishChoice) {
        return 0;
    }

    const selectedOption = sideDishChoice.options[sideDishChoice.selectedIndex];

    if (!selectedOption) {
        return 0;
    }

    return Number(selectedOption.dataset.extraPrice || 0);
}

function getSelectedSideDishOption(row) {
    const sideDishChoice = row.querySelector('.sideDishChoice');

    if (!sideDishChoice) {
        return 'geen_bijgerecht';
    }

    return sideDishChoice.value || 'geen_bijgerecht';
}

function openModal(message) {
    const modal = document.querySelector('#myModal');

    if (!modal) {
        return;
    }

    modal.querySelector('p').textContent = message;
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.querySelector('#myModal');

    if (!modal) {
        return;
    }

    modal.style.display = 'none';
}

function updateTotal() {
    const selectedRows = document.querySelectorAll('.itemSelectedTable .selectedMenuItem');
    let total = 0;

    selectedRows.forEach((row) => {
        const price = Number(row.dataset.price);
        const amountInput = row.querySelector('.orderAmount');

        if (!amountInput) {
            return;
        }

        const amount = Number(amountInput.value);
        const sideDishExtraPrice = getSelectedSideDishExtraPrice(row);
        const subtotal = (price + sideDishExtraPrice) * amount;

        const subAmount = row.querySelector('.subAmount');

        if (subAmount) {
            subAmount.textContent = formatPrice(subtotal);
        }

        total += subtotal;
    });

    const totalAmount = document.querySelector('.totalAmount');

    if (totalAmount) {
        totalAmount.textContent = formatPrice(total);
    }
}

function createRemarkPicker() {
    const wrapper = document.createElement('div');
    wrapper.className = 'remarkPicker';

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'orderRemark';
    input.maxLength = 255;
    input.placeholder = 'Opmerking, bijv. geen ui';

    const select = document.createElement('select');
    select.className = 'commonRemarkSelect';
    select.setAttribute('aria-label', 'Kies veelgebruikte opmerking');

    const placeholderOption = document.createElement('option');
    placeholderOption.value = '';
    placeholderOption.textContent = '▼';
    select.appendChild(placeholderOption);

    getCommonRemarks().forEach((remark) => {
        const option = document.createElement('option');
        option.value = remark;
        option.textContent = remark;
        select.appendChild(option);
    });

    select.addEventListener('change', () => {
        if (select.value !== '') {
            input.value = select.value;
            select.value = '';
        }
    });

    wrapper.appendChild(input);
    wrapper.appendChild(select);

    return wrapper;
}

function createSideDishPicker() {
    const wrapper = document.createElement('div');
    wrapper.className = 'sideDishWrapper';

    const label = document.createElement('label');
    label.textContent = 'Bijgerecht';

    const select = document.createElement('select');
    select.className = 'sideDishChoice';

    getSideDishOptions().forEach((sideDishOption) => {
        const option = document.createElement('option');

        option.value = sideDishOption.id;
        option.dataset.extraPrice = sideDishOption.extra_price;

        if (Number(sideDishOption.extra_price) > 0) {
            option.textContent = `${sideDishOption.name} (+ € ${formatPrice(sideDishOption.extra_price)})`;
        } else {
            option.textContent = sideDishOption.name;
        }

        select.appendChild(option);
    });

    select.value = 'geen_bijgerecht';
    select.addEventListener('change', updateTotal);

    wrapper.appendChild(label);
    wrapper.appendChild(select);

    return wrapper;
}

function createOrderRow(menuItem) {
    orderLineCounter++;

    const row = document.createElement('tr');
    row.className = 'selectedMenuItem';
    row.dataset.lineId = String(orderLineCounter);
    row.dataset.id = String(menuItem.id);
    row.dataset.price = String(menuItem.price);

    const numberCell = document.createElement('td');
    numberCell.textContent = `${menuItem.number}.`;

    const nameCell = document.createElement('td');

    const nameText = document.createElement('span');
    nameText.textContent = menuItem.name;
    nameCell.appendChild(nameText);

    if (menuItem.description) {
        const description = document.createElement('i');
        description.textContent = ` (${menuItem.description})`;
        nameCell.appendChild(description);
    }

    nameCell.appendChild(createRemarkPicker());
    nameCell.appendChild(createSideDishPicker());

    const priceCell = document.createElement('td');
    priceCell.innerHTML = `€ <span class="subAmount">${formatPrice(menuItem.price)}</span>`;

    const amountCell = document.createElement('td');

    const amountInput = document.createElement('input');
    amountInput.type = 'number';
    amountInput.className = 'orderAmount';
    amountInput.name = String(menuItem.id);
    amountInput.min = '0';
    amountInput.value = '1';

    amountInput.addEventListener('change', () => {
        if (Number(amountInput.value) <= 0) {
            row.remove();
        }

        updateTotal();
    });

    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'removeOrderLine';
    removeButton.textContent = 'X';
    removeButton.setAttribute('aria-label', 'Verwijder orderregel');

    removeButton.addEventListener('click', () => {
        row.remove();
        updateTotal();
    });

    amountCell.appendChild(amountInput);
    amountCell.appendChild(removeButton);

    row.appendChild(numberCell);
    row.appendChild(nameCell);
    row.appendChild(priceCell);
    row.appendChild(amountCell);

    return row;
}

function clearOrder() {
    const selectedOrderRows = document.querySelector('#selectedOrderRows');

    if (selectedOrderRows) {
        selectedOrderRows.innerHTML = '';
    }

    const totalAmount = document.querySelector('.totalAmount');

    if (totalAmount) {
        totalAmount.textContent = '0,00';
    }
}

function setupCashdeskButtons() {
    document.querySelectorAll('.addMenuItem').forEach((button) => {
        button.addEventListener('click', () => {
            const selectedOrderRows = document.querySelector('#selectedOrderRows');

            if (!selectedOrderRows) {
                return;
            }

            const menuItem = {
                id: Number(button.dataset.id),
                number: button.dataset.number || '',
                name: button.dataset.name || '',
                description: button.dataset.description || '',
                price: Number(button.dataset.price || 0),
            };

            const row = createOrderRow(menuItem);

            selectedOrderRows.appendChild(row);

            updateTotal();
        });
    });

    const clearButton = document.querySelector('#clearOrder');

    if (clearButton) {
        clearButton.addEventListener('click', clearOrder);
    }

    const payOrderButton = document.querySelector('#payOrder');

    if (payOrderButton) {
        payOrderButton.addEventListener('click', async () => {
            const selectedRows = document.querySelectorAll('.itemSelectedTable .selectedMenuItem');

            const items = Array.from(selectedRows)
                .map((row) => {
                    const amountInput = row.querySelector('.orderAmount');
                    const remarkInput = row.querySelector('.orderRemark');

                    return {
                        id: Number(row.dataset.id),
                        amount: amountInput ? Number(amountInput.value) : 0,
                        remark: remarkInput ? remarkInput.value.trim() : '',
                        side_dish_option: getSelectedSideDishOption(row),
                    };
                })
                .filter((item) => item.amount > 0);

            if (items.length === 0) {
                openModal('Niets geselecteerd');
                return;
            }

            try {
                const response = await fetch(window.kassaPayUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        items: items,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.message) {
                        openModal(data.message);
                        return;
                    }

                    openModal('Iets is verkeerd gegaan. Controleer de bestelling.');
                    return;
                }

                clearOrder();
                openModal(data.message || 'Verkoop succesvol!');
            } catch (error) {
                openModal('Iets is verkeerd gegaan. Contacteer Peter.');
            }
        });
    }

    const closeButton = document.querySelector('.close');

    if (closeButton) {
        closeButton.addEventListener('click', closeModal);
    }

    window.addEventListener('click', (event) => {
        const modal = document.querySelector('#myModal');

        if (event.target === modal) {
            closeModal();
        }
    });
}

const cashdeskPage = document.querySelector('#cashDeskPage');

if (cashdeskPage) {
    const app = createApp({
        data() {
            return {
                search: '',
                category: 'all',
                groups: window.kassaFilterGroups || [],
            };
        },

        computed: {
            normalizedSearch() {
                return normalizeText(this.search);
            },

            hasActiveFilter() {
                return this.normalizedSearch !== '' || this.category !== 'all';
            },

            hasResults() {
                return this.groups.some((group) => this.groupVisible(group.type));
            },
        },

        methods: {
            resetFilters() {
                this.search = '';
                this.category = 'all';
            },

            matchesCategory(type) {
                return this.category === 'all' || this.category === type;
            },

            matchesSearch(searchText) {
                if (this.normalizedSearch === '') {
                    return true;
                }

                return normalizeText(searchText).includes(this.normalizedSearch);
            },

            rowVisible(type, searchText) {
                return this.matchesCategory(type) && this.matchesSearch(searchText);
            },

            groupVisible(type) {
                if (!this.matchesCategory(type)) {
                    return false;
                }

                const group = this.groups.find((item) => item.type === type);

                if (!group) {
                    return false;
                }

                return group.items.some((item) => this.matchesSearch(item.searchText));
            },
        },

        mounted() {
            setupCashdeskButtons();
        },
    });

    app.mount('#cashDeskPage');
}
