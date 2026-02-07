import './bootstrap';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

const forms = document.querySelectorAll('[data-items-form]');

forms.forEach((form) => {
    const wrapper = form.querySelector('[data-items-wrapper]');
    const addButton = form.querySelector('[data-add-item]');

    if (!wrapper || !addButton) return;

    addButton.addEventListener('click', () => {
        const group = document.createElement('div');
        group.className = 'input-group';

        const input = document.createElement('input');
        input.className = 'form-control';
        input.name = 'items[]';
        input.placeholder = 'Item name';
        input.required = true;

        const qty = document.createElement('input');
        qty.className = 'form-control';
        qty.name = 'item_quantities[]';
        qty.type = 'number';
        qty.min = '1';
        qty.value = '1';
        qty.style.maxWidth = '120px';
        qty.required = true;

        const estimate = document.createElement('input');
        estimate.className = 'form-control';
        estimate.name = 'item_estimates[]';
        estimate.type = 'number';
        estimate.min = '0';
        estimate.step = '0.01';
        estimate.placeholder = 'Estimate';
        estimate.style.maxWidth = '140px';

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger';
        removeBtn.textContent = 'Remove';
        removeBtn.addEventListener('click', () => {
            group.remove();
        });

        group.appendChild(input);
        group.appendChild(qty);
        group.appendChild(estimate);
        group.appendChild(removeBtn);
        wrapper.appendChild(group);
    });

    wrapper.querySelectorAll('[data-remove-item]').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.closest('.input-group')?.remove();
        });
    });
});

const printButton = document.getElementById('printAuction');
if (printButton) {
    printButton.addEventListener('click', () => {
        window.print();
    });
}

const subjectSearch = document.getElementById('subjectSearch');
if (subjectSearch) {
    const table = document.getElementById('subjectsTable');
    const rows = Array.from(table?.querySelectorAll('tbody tr') || []);

    subjectSearch.addEventListener('input', (event) => {
        const query = event.target.value.trim().toLowerCase();
        rows.forEach((row) => {
            const text = row.textContent?.toLowerCase() || '';
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
}

const auctionSearch = document.getElementById('auctionSearch');
if (auctionSearch) {
    const table = document.getElementById('auctionTable');
    const rows = Array.from(table?.querySelectorAll('tbody tr.auction-entry-row') || []);

    auctionSearch.addEventListener('input', (event) => {
        const query = event.target.value.trim().toLowerCase();
        rows.forEach((row) => {
            const text = row.textContent?.toLowerCase() || '';
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
}

const enableResizableColumns = (table) => {
    if (!table) return;

    const headerCells = Array.from(table.querySelectorAll('thead th'));
    if (!headerCells.length) return;

    const storageKey = `columnWidths:${table.id || 'table'}`;
    const storedWidths = (() => {
        try {
            return JSON.parse(localStorage.getItem(storageKey) || '[]');
        } catch {
            return [];
        }
    })();

    headerCells.forEach((cell, index) => {
        if (storedWidths[index]) {
            cell.style.width = `${storedWidths[index]}px`;
        }
    });

    const resizableCells = headerCells.slice(0, -1);
    const isRtl = getComputedStyle(table).direction === 'rtl';

    const saveWidths = () => {
        const widths = headerCells.map((cell) => Math.round(cell.getBoundingClientRect().width));
        localStorage.setItem(storageKey, JSON.stringify(widths));
    };

    resizableCells.forEach((cell) => {
        if (cell.querySelector('.column-resizer')) return;

        const resizer = document.createElement('div');
        resizer.className = 'column-resizer';
        cell.appendChild(resizer);

        resizer.addEventListener('pointerdown', (event) => {
            event.preventDefault();

            const startX = event.clientX;
            const startWidth = cell.getBoundingClientRect().width;
            const minWidth = Math.max(70, parseInt(cell.dataset.minWidth || '0', 10));

            resizer.classList.add('is-resizing');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';

            const onPointerMove = (moveEvent) => {
                const delta = isRtl ? startX - moveEvent.clientX : moveEvent.clientX - startX;
                const nextWidth = Math.max(minWidth, startWidth + delta);
                cell.style.width = `${nextWidth}px`;
            };

            const onPointerUp = () => {
                resizer.classList.remove('is-resizing');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                document.removeEventListener('pointermove', onPointerMove);
                document.removeEventListener('pointerup', onPointerUp);
                saveWidths();
            };

            document.addEventListener('pointermove', onPointerMove);
            document.addEventListener('pointerup', onPointerUp);
        });
    });
};

enableResizableColumns(document.getElementById('auctionTable'));

const toggleEstimate = document.getElementById('toggleEstimate');
if (toggleEstimate) {
    const table = document.getElementById('auctionTable');
    const storageKey = 'showEstimateColumn';
    const saved = localStorage.getItem(storageKey);
    const isVisible = saved === null ? true : saved === '1';
    toggleEstimate.checked = isVisible;

    const applyVisibility = (show) => {
        table?.querySelectorAll('.estimate-col').forEach((cell) => {
            cell.classList.toggle('d-none', !show);
        });
    };

    applyVisibility(isVisible);

    toggleEstimate.addEventListener('change', (event) => {
        const show = event.target.checked;
        localStorage.setItem(storageKey, show ? '1' : '0');
        applyVisibility(show);
    });
}

const entriesPayload = window.__entries || [];
const usedItemCountsGlobal = window.__usedItemCounts || {};

const buildItemsEditor = ({ container, subjectValue, selectedItems, selectedQuantities, selectedNames, usedItemCounts }) => {
    container.innerHTML = '';
    const subjects = window.__subjects || [];
    const subject = subjects.find((entry) => entry.name === subjectValue || entry.number === subjectValue) || null;
    const formId = container.dataset.formId;

    if (!subject) {
        container.innerHTML = '<div class="text-muted small">Write a valid name or number to load items.</div>';
        return;
    }

    const availableItems = subject.items.filter((item) => {
        const used = Number(usedItemCounts[item.id] ?? 0);
        const total = Number(item.quantity ?? 1);
        return used < total || selectedItems.includes(item.id);
    });

    if (!availableItems.length) {
        container.innerHTML = '<div class="text-muted small">All items for this name/number are already used.</div>';
        return;
    }

    availableItems.forEach((item) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex align-items-center gap-2 mb-2';

        const input = document.createElement('input');
        input.className = 'form-check-input';
        input.type = 'checkbox';
        input.name = 'items[]';
        input.value = item.id;
        input.id = `edit-item-${container.dataset.entryId}-${item.id}`;
        if (formId) {
            input.setAttribute('form', formId);
        }
        if (selectedItems.includes(item.id)) {
            input.checked = true;
        }

        const total = Number(item.quantity ?? 1);
        const used = Number(usedItemCounts[item.id] ?? 0);
        const remaining = Math.max(0, total - used);
        const safeRemaining = Math.max(1, remaining);
        const nameDisplay = document.createElement('label');
        nameDisplay.className = 'form-check-label me-auto item-name-display';
        nameDisplay.htmlFor = input.id;
        const displayName = selectedNames[item.id] || item.name;
        nameDisplay.textContent = safeRemaining > 1 ? `${safeRemaining} ${displayName}` : displayName;

        const nameHidden = document.createElement('input');
        nameHidden.type = 'hidden';
        nameHidden.name = `item_names[${item.id}]`;
        nameHidden.value = displayName;
        if (formId) {
            nameHidden.setAttribute('form', formId);
        }
        if (!input.checked) {
            nameHidden.disabled = true;
        }

        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.className = 'form-control form-control-sm d-none';
        nameInput.value = displayName;
        nameInput.style.maxWidth = '200px';
        nameInput.autocomplete = 'off';

        let qty = null;
        if (safeRemaining > 1) {
            qty = document.createElement('input');
            qty.type = 'number';
            qty.min = '1';
            qty.name = `quantities[${item.id}]`;
            qty.className = 'form-control form-control-sm';
            qty.style.maxWidth = '90px';
            qty.max = String(safeRemaining);
            qty.value = Math.min(selectedQuantities[item.id] ?? 1, safeRemaining);
            if (formId) {
                qty.setAttribute('form', formId);
            }
            if (!input.checked) {
                qty.disabled = true;
                qty.name = '';
            }
        } else {
            const hiddenQty = document.createElement('input');
            hiddenQty.type = 'hidden';
            hiddenQty.name = `quantities[${item.id}]`;
            hiddenQty.value = '1';
            hiddenQty.dataset.autoQuantity = '1';
            if (formId) {
                hiddenQty.setAttribute('form', formId);
            }
            wrapper.appendChild(hiddenQty);
        }

        const commitNameEdit = () => {
            const value = nameInput.value.trim() || displayName;
            nameInput.value = value;
            nameHidden.value = value;
            nameDisplay.textContent = safeRemaining > 1 ? `${safeRemaining} ${value}` : value;
            nameInput.classList.add('d-none');
            nameDisplay.classList.remove('d-none');
        };

        nameDisplay.addEventListener('contextmenu', (event) => {
            event.preventDefault();
            if (!input.checked) {
                input.checked = true;
                input.dispatchEvent(new Event('change'));
            }
            if (nameInput.disabled) return;
            nameDisplay.classList.add('d-none');
            nameInput.classList.remove('d-none');
            nameInput.focus();
            nameInput.select();
        });

        nameInput.addEventListener('input', () => {
            nameHidden.value = nameInput.value;
        });

        nameInput.addEventListener('blur', commitNameEdit);
        nameInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                commitNameEdit();
            }
        });

        input.addEventListener('change', () => {
            if (qty) {
                qty.disabled = !input.checked;
                if (qty.disabled) {
                    qty.value = 1;
                    qty.name = '';
                } else {
                    qty.name = `quantities[${item.id}]`;
                    if (formId) {
                        qty.setAttribute('form', formId);
                    }
                }
            } else {
                const hiddenQty = wrapper.querySelector('input[type=\"hidden\"][data-auto-quantity]');
                if (hiddenQty) {
                    hiddenQty.disabled = !input.checked;
                }
            }
            nameHidden.disabled = !input.checked;
            if (nameHidden.disabled) {
                nameDisplay.classList.remove('d-none');
                nameInput.classList.add('d-none');
            }
        });

            wrapper.appendChild(input);
            wrapper.appendChild(nameDisplay);
            wrapper.appendChild(nameHidden);
            wrapper.appendChild(nameInput);
        if (qty) {
            wrapper.appendChild(qty);
        }
        container.appendChild(wrapper);
    });
};

const resolveSubjectName = (value) => {
    const subjects = window.__subjects || [];
    const trimmed = (value || '').trim();
    const subject = subjects.find((item) => item.name === trimmed || item.number === trimmed);
    return subject?.name || trimmed;
};

const buildDescText = (subjectValue, selectedItems, selectedQuantities, selectedNames = {}) => {
    const subjects = window.__subjects || [];
    const trimmed = (subjectValue || '').trim();
    const subject = subjects.find((item) => item.name === trimmed || item.number === trimmed);
    if (!subject) return '';
    const parts = selectedItems.map((itemId) => {
        const item = subject.items.find((i) => i.id === Number(itemId));
        if (!item) return '';
        const qty = Number(selectedQuantities[itemId] ?? 1);
        const name = selectedNames[itemId] || item.name;
        return qty > 1 ? `${qty} ${name}` : name;
    }).filter(Boolean);
    return parts.join(' و ');
};

const updateHiddenItems = (form, selectedItems, selectedQuantities, selectedNames) => {
    form.querySelectorAll('input[type="hidden"][name="items[]"], input[type="hidden"][name^="quantities["], input[type="hidden"][name^="item_names["]')
        .forEach((el) => el.remove());
    selectedItems.forEach((itemId) => {
        const itemInput = document.createElement('input');
        itemInput.type = 'hidden';
        itemInput.name = 'items[]';
        itemInput.value = itemId;
        form.appendChild(itemInput);

        const qtyInput = document.createElement('input');
        qtyInput.type = 'hidden';
        qtyInput.name = `quantities[${itemId}]`;
        qtyInput.value = selectedQuantities[itemId] ?? 1;
        form.appendChild(qtyInput);

        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = `item_names[${itemId}]`;
        nameInput.value = selectedNames[itemId] ?? '';
        form.appendChild(nameInput);
    });
};

const collectRowData = (row) => {
    const form = row.querySelector('form[id^="edit-form-"]');
    const snameInput = row.querySelector('input[name="sname"]');
    const priceInput = row.querySelector('input[name="price"]');
    const buyerInput = row.querySelector('input[name="buyer_name"]');
    const itemsEditor = row.querySelector('[data-items-editor]');

    let selectedItems = [];
    let selectedQuantities = {};
    let selectedNames = {};

    if (itemsEditor) {
        const checked = Array.from(itemsEditor.querySelectorAll('input[type="checkbox"][name="items[]"]:checked'));
        checked.forEach((input) => {
            const itemId = Number(input.value);
            selectedItems.push(itemId);
            const qtyInput = itemsEditor.querySelector(`input[name="quantities[${itemId}]"]`);
            selectedQuantities[itemId] = Number(qtyInput?.value ?? 1);
            const nameInput = itemsEditor.querySelector(`input[name="item_names[${itemId}]"]`);
            if (nameInput) {
                selectedNames[itemId] = nameInput.value;
            }
        });
    }

    if (form) {
        const hiddenItems = Array.from(form.querySelectorAll('input[type="hidden"][name="items[]"]'));
        hiddenItems.forEach((input) => {
            const itemId = Number(input.value);
            if (!selectedItems.includes(itemId)) {
                selectedItems.push(itemId);
            }
            const qtyInput = form.querySelector(`input[type="hidden"][name="quantities[${itemId}]"]`);
            if (selectedQuantities[itemId] == null) {
                selectedQuantities[itemId] = Number(qtyInput?.value ?? 1);
            }
            const nameInput = form.querySelector(`input[type="hidden"][name="item_names[${itemId}]"]`);
            if (nameInput) {
                if (!selectedNames[itemId]) {
                    selectedNames[itemId] = nameInput.value;
                }
            }
        });
    }

    return {
        form,
        sname: snameInput?.value ?? '',
        price: priceInput?.value ?? '',
        buyerName: buyerInput?.value ?? '',
        selectedItems,
        selectedQuantities,
        selectedNames,
    };
};

const saveRow = async (row, focusCell) => {
    const { form, sname, price, buyerName, selectedItems, selectedQuantities, selectedNames } = collectRowData(row);
    if (!form) return;

    const formData = new FormData();
    formData.append('_token', form.querySelector('input[name="_token"]')?.value || '');
    formData.append('_method', 'PUT');
    formData.append('sname', sname);
    formData.append('price', price);
    formData.append('buyer_name', buyerName);
    selectedItems.forEach((itemId) => {
        formData.append('items[]', itemId);
        formData.append(`quantities[${itemId}]`, selectedQuantities[itemId] ?? 1);
        formData.append(`item_names[${itemId}]`, selectedNames[itemId] ?? '');
    });

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            const data = await response.json().catch(() => null);
            const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : 'Save failed.';
            alert(firstError || 'Save failed.');
            return;
        }

        updateHiddenItems(form, selectedItems, selectedQuantities, selectedNames);

        const entryId = Number(row.querySelector('[data-items-editor]')?.dataset.entryId || 0);
        const entry = entriesPayload.find((item) => item.id === entryId);
        const previousItems = entry?.items || [];

        previousItems.forEach((item) => {
            const used = Number(usedItemCountsGlobal[item.id] ?? 0);
            usedItemCountsGlobal[item.id] = Math.max(0, used - (item.quantity ?? 1));
        });
        selectedItems.forEach((itemId) => {
            const used = Number(usedItemCountsGlobal[itemId] ?? 0);
            const qty = Number(selectedQuantities[itemId] ?? 1);
            usedItemCountsGlobal[itemId] = used + qty;
        });

        if (entry) {
            entry.items = selectedItems.map((itemId) => ({
                id: itemId,
                quantity: Number(selectedQuantities[itemId] ?? 1),
                name: selectedNames[itemId] || '',
            }));
        }

        // Do not update global subject items; names are per-row only.

        const snameDisplay = row.querySelector('.cell-display[data-cell="sname"]');
        const descDisplay = row.querySelector('.cell-display[data-cell="desc"]');
        const priceDisplay = row.querySelector('.cell-display[data-cell="price"]');
        const bnameDisplay = row.querySelector('.cell-display[data-cell="bname"]');

        if (snameDisplay) snameDisplay.textContent = resolveSubjectName(sname);
        if (descDisplay) descDisplay.textContent = buildDescText(sname, selectedItems, selectedQuantities, selectedNames);
        if (priceDisplay) priceDisplay.textContent = price;
        if (bnameDisplay) bnameDisplay.textContent = buyerName;

        row.querySelectorAll('.cell-display').forEach((el) => el.classList.remove('d-none'));
        row.querySelectorAll('.cell-edit').forEach((el) => el.classList.add('d-none'));
        row.querySelector('[data-save-row]')?.classList.add('d-none');
        row.querySelector('[data-edit-row]')?.classList.remove('d-none');
        row.querySelector('[data-cancel-row]')?.classList.add('d-none');

        if (focusCell) {
            const nextRow = row.nextElementSibling;
            if (nextRow) {
                enterEditMode(nextRow, focusCell);
            }
        }
    } catch (error) {
        alert('Save failed.');
    }
};

const enterEditMode = (row, focusCell) => {
    if (!row) return;

    document.querySelectorAll('tr').forEach((otherRow) => {
        if (otherRow === row) return;
        otherRow.querySelectorAll('.cell-display').forEach((el) => el.classList.remove('d-none'));
        otherRow.querySelectorAll('.cell-edit').forEach((el) => el.classList.add('d-none'));
        otherRow.querySelector('[data-save-row]')?.classList.add('d-none');
        otherRow.querySelector('[data-cancel-row]')?.classList.add('d-none');
        otherRow.querySelector('[data-edit-row]')?.classList.remove('d-none');
    });

    row.querySelectorAll('.cell-display').forEach((el) => el.classList.add('d-none'));
    row.querySelectorAll('.cell-edit').forEach((el) => el.classList.remove('d-none'));
    row.querySelector('[data-save-row]')?.classList.remove('d-none');
    row.querySelector('[data-cancel-row]')?.classList.remove('d-none');
    row.querySelector('[data-edit-row]')?.classList.add('d-none');

    const entryId = Number(row.querySelector('[data-items-editor]')?.dataset.entryId || 0);
    const entry = entriesPayload.find((item) => item.id === entryId);
    const selectedItems = (entry?.items || []).map((item) => item.id);
    const selectedQuantities = (entry?.items || []).reduce((acc, item) => {
        acc[item.id] = item.quantity ?? 1;
        return acc;
    }, {});
    const selectedNames = (entry?.items || []).reduce((acc, item) => {
        acc[item.id] = item.name || '';
        return acc;
    }, {});

    const usedItemCounts = { ...(usedItemCountsGlobal || {}) };
    selectedItems.forEach((itemId) => {
        const used = Number(usedItemCounts[itemId] ?? 0);
        const selectedQty = Number(selectedQuantities[itemId] ?? 1);
        usedItemCounts[itemId] = Math.max(0, used - selectedQty);
    });

    const snameInput = row.querySelector('input[name="sname"]');
    const itemsEditor = row.querySelector('[data-items-editor]');

    if (itemsEditor && snameInput) {
        buildItemsEditor({
            container: itemsEditor,
            subjectValue: snameInput.value,
            selectedItems,
            selectedQuantities,
            selectedNames,
            usedItemCounts,
        });
    }

    snameInput?.addEventListener('input', () => {
        if (!itemsEditor) return;
        buildItemsEditor({
            container: itemsEditor,
            subjectValue: snameInput.value,
            selectedItems: [],
            selectedQuantities: {},
            selectedNames: {},
            usedItemCounts,
        });
    });

    if (focusCell) {
        const target = row.querySelector(`[data-cell-input="${focusCell}"]`);
        if (target) {
            target.focus();
            target.select?.();
        }
    }
};

document.querySelectorAll('[data-edit-row]').forEach((button) => {
    button.addEventListener('click', () => {
        const row = button.closest('tr');
        enterEditMode(row);
    });
});

document.querySelectorAll('form[id^="edit-form-"]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const row = form.closest('tr');
        saveRow(row);
    });
});

document.querySelectorAll('[data-cancel-row]').forEach((button) => {
    button.addEventListener('click', () => {
        const row = button.closest('tr');
        if (!row) return;

        row.querySelectorAll('.cell-display').forEach((el) => el.classList.remove('d-none'));
        row.querySelectorAll('.cell-edit').forEach((el) => el.classList.add('d-none'));
        row.querySelector('[data-save-row]')?.classList.add('d-none');
        row.querySelector('[data-edit-row]')?.classList.remove('d-none');
        button.classList.add('d-none');
    });
});

const addRowButton = document.getElementById('addRowButton');
const addRow = document.getElementById('addRow');
const cancelAddRow = document.getElementById('cancelAddRow');
const createForm = document.getElementById('create-form');

if (addRowButton && addRow && createForm) {
    const snameInput = addRow.querySelector('input[name="sname"]');
    const itemsEditor = addRow.querySelector('[data-items-editor]');

    const renderCreateItems = (value) => {
        if (!itemsEditor) return;
        buildItemsEditor({
            container: itemsEditor,
            subjectValue: value,
            selectedItems: [],
            selectedQuantities: {},
            selectedNames: {},
            usedItemCounts: { ...(usedItemCountsGlobal || {}) },
        });
    };

    addRowButton.addEventListener('click', () => {
        addRow.classList.remove('d-none');
        snameInput?.focus();
        renderCreateItems(snameInput?.value || '');
    });

    snameInput?.addEventListener('input', (event) => {
        renderCreateItems(event.target.value);
    });

    if (!addRow.classList.contains('d-none')) {
        renderCreateItems(snameInput?.value || '');
    }

    cancelAddRow?.addEventListener('click', () => {
        addRow.classList.add('d-none');
        if (snameInput) snameInput.value = '';
        const priceInput = addRow.querySelector('input[name="price"]');
        if (priceInput) priceInput.value = '';
        const buyerInput = addRow.querySelector('input[name="buyer_name"]');
        if (buyerInput) buyerInput.value = '';
        if (itemsEditor) {
            itemsEditor.innerHTML = '';
        }
    });
}

const handleCellNavigation = (event) => {
    const target = event.target;
    if (!target?.dataset?.cellInput) return;

    const row = target.closest('tr');
    if (!row) return;

    const cellInputs = Array.from(row.querySelectorAll('[data-cell-input]'));
    const priceInput = cellInputs.find((input) => input.dataset.cellInput === 'price');
    const bnameInput = cellInputs.find((input) => input.dataset.cellInput === 'bname');

    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'd') {
        event.preventDefault();
        const prevRow = row.previousElementSibling;
        const prevInput = prevRow?.querySelector(`[data-cell-input=\"${target.dataset.cellInput}\"]`);
        if (prevInput) {
            target.value = prevInput.value;
        }
        return;
    }

    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'e') {
        event.preventDefault();
        const nextRow = row.nextElementSibling;
        const nextInput = nextRow?.querySelector(`[data-cell-input=\"${target.dataset.cellInput}\"]`);
        if (nextInput) {
            target.value = nextInput.value;
        }
        return;
    }

    if (event.key === 'Tab') {
        event.preventDefault();
        if (target.dataset.cellInput === 'price' && bnameInput) {
            bnameInput.focus();
        } else if (target.dataset.cellInput === 'bname' && priceInput) {
            priceInput.focus();
        }
        return;
    }

    if (event.key === 'Enter') {
        event.preventDefault();
        saveRow(row, target.dataset.cellInput);
    }
};

document.addEventListener('keydown', handleCellNavigation);

document.querySelectorAll('td').forEach((cell) => {
    cell.addEventListener('click', (event) => {
        const display = cell.querySelector('.cell-display[data-cell="price"], .cell-display[data-cell="bname"]');
        if (!display || event.target.closest('input, button, select, textarea')) return;
        const row = cell.closest('tr');
        const focusCell = display.dataset.cell === 'price' ? 'price' : 'bname';
        enterEditMode(row, focusCell);
    });
});

const terminalInput = document.querySelector('[data-terminal-input]');
const terminalOutput = document.querySelector('[data-terminal-output]');
const terminalForm = document.querySelector('[data-terminal-form]');
const newAuctionForm = document.querySelector('[data-new-auction-form]');
const importTrigger = document.querySelector('[data-import-trigger]');
const importInput = document.querySelector('[data-import-input]');
const importForm = document.querySelector('[data-import-form]');

if (terminalInput && terminalOutput && terminalForm) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const appendLine = (text, className = '') => {
        const line = document.createElement('div');
        line.className = `terminal-line ${className}`.trim();
        line.textContent = text;
        terminalOutput.appendChild(line);
        terminalOutput.scrollTop = terminalOutput.scrollHeight;
    };

    const showNewAuctionForm = () => {
        if (!newAuctionForm) return;
        newAuctionForm.classList.remove('d-none');
        const today = new Date().toISOString().slice(0, 10);
        const startInput = newAuctionForm.querySelector('[data-start-date]');
        const endInput = newAuctionForm.querySelector('[data-end-date]');
        if (startInput && !startInput.value) startInput.value = today;
        if (endInput && !endInput.value) endInput.value = today;
        startInput?.focus();
    };

    const hideNewAuctionForm = () => {
        newAuctionForm?.classList.add('d-none');
    };

    const handleCommand = async (command) => {
        const trimmed = command.trim();
        if (!trimmed) return;

        const lower = trimmed.toLowerCase();
        appendLine(`$ ${trimmed}`);

        if (lower === 'new auction') {
            appendLine('Enter start and end dates below.', 'muted');
            showNewAuctionForm();
            return;
        }

        if (lower.startsWith('load ')) {
            const code = trimmed.slice(5).trim();
            if (!code) {
                appendLine('Please provide an auction code.', 'muted');
                return;
            }
            const response = await fetch('/auctions/load', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ code }),
            });
            const data = await response.json().catch(() => null);
            if (!response.ok) {
                appendLine(data?.message || 'Failed to load auction.', 'muted');
                return;
            }
            appendLine(`Current auction set to ${data.code}.`, 'success');
            return;
        }

        if (lower === 'show codes') {
            const response = await fetch('/auctions/codes', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
            const data = await response.json().catch(() => null);
            if (!response.ok) {
                appendLine('Unable to load codes.', 'muted');
                return;
            }
            if (!data?.codes?.length) {
                appendLine('No auctions found.', 'muted');
                return;
            }
            data.codes.forEach((entry) => {
                appendLine(`${entry.code} (${entry.start_date} → ${entry.end_date})`, 'muted');
            });
            return;
        }

        appendLine('Unknown command.', 'muted');
    };

    terminalInput.addEventListener('keydown', async (event) => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        const value = terminalInput.value;
        terminalInput.value = '';
        await handleCommand(value);
    });

    newAuctionForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const startInput = newAuctionForm.querySelector('[data-start-date]');
        const endInput = newAuctionForm.querySelector('[data-end-date]');
        const startDate = startInput?.value;
        const endDate = endInput?.value;
        if (!startDate || !endDate) {
            appendLine('Please select both dates.', 'muted');
            return;
        }
        const response = await fetch('/auctions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ start_date: startDate, end_date: endDate }),
        });
        const data = await response.json().catch(() => null);
        if (!response.ok) {
            appendLine(data?.message || 'Failed to create auction.', 'muted');
            return;
        }
        appendLine(`Auction created. Code: ${data.code}`, 'success');
        hideNewAuctionForm();
    });
}

if (importTrigger && importInput && importForm) {
    importTrigger.addEventListener('click', () => {
        importInput.click();
    });

    importInput.addEventListener('change', () => {
        if (!importInput.files || !importInput.files.length) return;
        const ok = confirm('Importing will replace the current database. Continue?');
        if (!ok) {
            importInput.value = '';
            return;
        }
        importForm.submit();
    });
}
