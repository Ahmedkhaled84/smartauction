@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="h4 fw-semibold mb-1">إيصال</h2>
                        <p class="text-muted mb-0">إنشاء إيصال بالاسم والسعر.</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">رقم مسلسل AK</span>
                </div>
                <div class="row g-4" dir="rtl">
                    <div class="col-12 col-lg-5">
                        <div class="border rounded-3 p-4">
                            <form id="receiptForm" autocomplete="off">
                                <div class="mb-3">
                                    <label class="form-label" for="receiptDate">التاريخ</label>
                                    <input class="form-control" id="receiptDate" name="receipt_date" type="date" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="receiptName">الاسم</label>
                                    <input class="form-control" id="receiptName" name="receipt_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="receiptPrice">السعر</label>
                                    <input class="form-control" id="receiptPrice" name="receipt_price" type="number" min="0" step="0.01" required>
                                </div>
                                <button class="btn btn-primary w-100" type="submit" id="receiptSubmit">إنشاء إيصال</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="border rounded-3 p-4 bg-light h-100" id="receiptPrintArea">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small">رقم الإيصال</div>
                                    <div class="h5 fw-semibold mb-0" id="receiptSerial">AK-000001</div>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm" id="printReceipt" type="button" disabled>طباعة</button>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">التاريخ</span>
                                <span class="fw-semibold" id="receiptDatePreview">—</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">الاسم</span>
                                <span class="fw-semibold" id="receiptNamePreview">—</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">السعر</span>
                                <span class="fw-semibold" id="receiptPricePreview">—</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">المبلغ كتابة</span>
                                <span class="fw-semibold" id="receiptAmountWords">—</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4" dir="rtl">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-2">
                        <h3 class="h6 fw-semibold mb-0">الإيصالات المحفوظة</h3>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <input class="form-control form-control-sm w-auto" id="receiptSearch" type="search" placeholder="بحث بالاسم أو السعر أو الرقم">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-bordered mb-0" id="receiptsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>الرقم</th>
                                    <th>التاريخ</th>
                                    <th>الاسم</th>
                                    <th class="text-end">السعر</th>
                                    <th class="text-center">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-muted text-center py-3">لا توجد إيصالات بعد.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const receiptForm = document.getElementById('receiptForm');
    const receiptSerial = document.getElementById('receiptSerial');
    const receiptDate = document.getElementById('receiptDate');
    const receiptNamePreview = document.getElementById('receiptNamePreview');
    const receiptDatePreview = document.getElementById('receiptDatePreview');
    const receiptPricePreview = document.getElementById('receiptPricePreview');
    const receiptAmountWords = document.getElementById('receiptAmountWords');
    const printReceipt = document.getElementById('printReceipt');
    const receiptsTable = document.getElementById('receiptsTable');
    const receiptSubmit = document.getElementById('receiptSubmit');
    const receiptSearch = document.getElementById('receiptSearch');

    const receiptsStorageKey = 'savedReceipts';
    const englishPriceFormatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const toArabicWords = (amount) => {
        const ones = ['صفر', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة'];
        const teens = ['عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
        const tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
        const hundreds = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];

        const groupToWords = (num) => {
            if (num === 0) return '';
            const h = Math.floor(num / 100);
            const r = num % 100;
            const parts = [];
            if (h) parts.push(hundreds[h]);
            if (r) {
                if (r < 10) {
                    parts.push(ones[r]);
                } else if (r < 20) {
                    parts.push(teens[r - 10]);
                } else {
                    const t = Math.floor(r / 10);
                    const o = r % 10;
                    if (o) parts.push(`${ones[o]} و ${tens[t]}`);
                    else parts.push(tens[t]);
                }
            }
            return parts.join(' و ');
        };

        const integerPart = Math.floor(amount);
        const fractionPart = Math.round((amount - integerPart) * 100);

        const millions = Math.floor(integerPart / 1000000);
        const thousands = Math.floor((integerPart % 1000000) / 1000);
        const hundredsPart = integerPart % 1000;

        const parts = [];
        if (millions) {
            if (millions === 1) {
                parts.push('مليون');
            } else if (millions === 2) {
                parts.push('مليونان');
            } else {
                parts.push(`${groupToWords(millions)} ملايين`);
            }
        }
        if (thousands) {
            if (thousands === 1) {
                parts.push('ألف');
            } else if (thousands === 2) {
                parts.push('ألفان');
            } else {
                parts.push(`${groupToWords(thousands)} آلاف`);
            }
        }
        if (hundredsPart) {
            parts.push(groupToWords(hundredsPart));
        }

        const dinars = parts.length ? parts.join(' و ') : ones[0];
        let words = `${dinars} جنيه`;
        if (fractionPart) {
            words += ` و ${groupToWords(fractionPart)} قرش`;
        }
        return `${words} فقط`;
    };

    const generateSerial = () => {
        const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        const random = new Uint8Array(5);
        crypto.getRandomValues(random);
        random.forEach((value) => {
            code += alphabet[value % alphabet.length];
        });
        return `AK${code}`;
    };

    const loadReceipts = () => {
        try {
            return JSON.parse(localStorage.getItem(receiptsStorageKey) || '[]');
        } catch {
            return [];
        }
    };

    const saveReceipts = (receipts) => {
        localStorage.setItem(receiptsStorageKey, JSON.stringify(receipts));
    };

    const renderReceipts = () => {
        if (!receiptsTable) return;
        const tbody = receiptsTable.querySelector('tbody');
        const receipts = loadReceipts();
        const query = (receiptSearch?.value || '').trim().toLowerCase();
        const selectedDate = receiptDate?.value || '';
        if (!tbody) return;
        tbody.innerHTML = '';

        const filtered = receipts.filter((receipt) => {
            if (selectedDate && receipt.date !== selectedDate) return false;
            if (!query) return true;
            const priceText = Number(receipt.price || 0).toFixed(2);
            return [
                receipt.serial,
                receipt.date,
                receipt.name,
                priceText,
            ].some((value) => String(value || '').toLowerCase().includes(query));
        });

        if (!filtered.length) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="5" class="text-muted text-center py-3">لا توجد إيصالات بعد.</td>';
            tbody.appendChild(emptyRow);
            return;
        }

        filtered.forEach((receipt) => {
            const index = receipts.findIndex((item) => item.serial === receipt.serial);
            const row = document.createElement('tr');
            const formattedPrice = englishPriceFormatter.format(Number(receipt.price || 0));
            row.innerHTML = `
                <td class="fw-semibold">${receipt.serial}</td>
                <td>${receipt.date}</td>
                <td>${receipt.name}</td>
                <td class="text-end">${formattedPrice}</td>
                <td class="text-center">
                    <div class="d-inline-flex flex-wrap gap-1">
                        <button class="btn btn-sm btn-primary" type="button" data-receipt-print="${index}">طباعة</button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-receipt-edit="${index}">تعديل</button>
                        <button class="btn btn-sm btn-outline-danger" type="button" data-receipt-delete="${index}">حذف</button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    };

    if (receiptDate) {
        const today = new Date();
        const yyyy = String(today.getFullYear());
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        receiptDate.value = `${yyyy}-${mm}-${dd}`;
        receiptDatePreview.textContent = receiptDate.value;
        receiptDate.addEventListener('change', () => {
            receiptDatePreview.textContent = receiptDate.value || '—';
            renderReceipts();
        });
    }

    if (receiptForm) {
        receiptForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const name = document.getElementById('receiptName').value.trim();
            const priceValue = document.getElementById('receiptPrice').value;
            if (!name || priceValue === '') return;

            const dateValue = receiptDate?.value || '';
            receiptDatePreview.textContent = dateValue || '—';
            receiptNamePreview.textContent = name;
            receiptPricePreview.textContent = englishPriceFormatter.format(Number(priceValue));
            if (receiptAmountWords) receiptAmountWords.textContent = toArabicWords(Number(priceValue));
            printReceipt.disabled = false;

            const receipts = loadReceipts();
            const editIndexRaw = receiptForm.dataset.editIndex;
            if (editIndexRaw !== undefined) {
                const editIndex = Number(editIndexRaw);
                const existing = receipts[editIndex];
                if (existing) {
                    existing.date = dateValue;
                    existing.name = name;
                    existing.price = priceValue;
                    receiptSerial.textContent = existing.serial;
                }
                delete receiptForm.dataset.editIndex;
                if (receiptSubmit) receiptSubmit.textContent = 'إنشاء إيصال';
            } else {
                let serial = generateSerial();
                const existingSerials = new Set(receipts.map((item) => item.serial));
                while (existingSerials.has(serial)) {
                    serial = generateSerial();
                }
                receiptSerial.textContent = serial;
                receipts.unshift({
                    serial,
                    date: dateValue,
                    name,
                    price: priceValue,
                });
            }
            saveReceipts(receipts.slice(0, 200));
            renderReceipts();
        });
    }

    if (printReceipt) {
        printReceipt.addEventListener('click', () => {
            window.print();
        });
    }

    if (receiptSearch) {
        receiptSearch.addEventListener('input', () => {
            renderReceipts();
        });
    }

    if (receiptsTable) {
        receiptsTable.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) return;

            const editIndex = target.getAttribute('data-receipt-edit');
            const deleteIndex = target.getAttribute('data-receipt-delete');
            const printIndex = target.getAttribute('data-receipt-print');
            if (printIndex !== null) {
                const receipts = loadReceipts();
                const receipt = receipts[Number(printIndex)];
                if (!receipt) return;
                receiptSerial.textContent = receipt.serial;
                receiptDatePreview.textContent = receipt.date || '—';
                receiptNamePreview.textContent = receipt.name || '—';
                receiptPricePreview.textContent = englishPriceFormatter.format(Number(receipt.price || 0));
                if (receiptAmountWords) receiptAmountWords.textContent = toArabicWords(Number(receipt.price || 0));
                printReceipt.disabled = false;
                window.print();
                return;
            }

            if (editIndex !== null) {
                const receipts = loadReceipts();
                const receipt = receipts[Number(editIndex)];
                if (!receipt) return;
                if (receiptDate) receiptDate.value = receipt.date || '';
                const nameInput = document.getElementById('receiptName');
                const priceInput = document.getElementById('receiptPrice');
                if (nameInput) nameInput.value = receipt.name || '';
                if (priceInput) priceInput.value = receipt.price ?? '';
                receiptSerial.textContent = receipt.serial;
                receiptDatePreview.textContent = receipt.date || '—';
                receiptNamePreview.textContent = receipt.name || '—';
                receiptPricePreview.textContent = Number(receipt.price || 0).toFixed(2);
                receiptForm.dataset.editIndex = String(editIndex);
                if (receiptSubmit) receiptSubmit.textContent = 'تحديث الإيصال';
                return;
            }

            if (deleteIndex !== null) {
                const receipts = loadReceipts();
                receipts.splice(Number(deleteIndex), 1);
                saveReceipts(receipts);
                renderReceipts();
            }
        });
    }

    renderReceipts();
</script>
@endsection
