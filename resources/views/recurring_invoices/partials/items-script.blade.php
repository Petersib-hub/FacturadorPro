<script>
(function(){
    const tableBody = document.querySelector('#riItemsTable tbody');
    const currencyInput = document.querySelector('input[name="currency"]');
    const curEls = [document.getElementById('riCur1'), document.getElementById('riCur2'), document.getElementById('riCur3')].filter(Boolean);

    function fmt(n){ return (isNaN(n)?0:n).toLocaleString('es-ES',{minimumFractionDigits:2,maximumFractionDigits:2}); }

    function recalc(){
        let sub=0, tax=0;
        tableBody.querySelectorAll('tr').forEach(tr=>{
            const q = parseFloat(tr.querySelector('.qty')?.value || 0);
            const p = parseFloat(tr.querySelector('.price')?.value || 0);
            const d = parseFloat(tr.querySelector('.disc')?.value || 0);
            const t = parseFloat(tr.querySelector('.tax')?.value || 0);

            const net = q*p*(1-(d/100));
            const tAmt = net*(t/100);
            sub += net; tax += tAmt;
        });
        const subEl = document.getElementById('riSub');
        const taxEl = document.getElementById('riTax');
        const totEl = document.getElementById('riTot');
        if(subEl) subEl.textContent = fmt(sub);
        if(taxEl) taxEl.textContent = fmt(tax);
        if(totEl) totEl.textContent = fmt(sub+tax);
        const cur = (currencyInput?.value || 'EUR');
        curEls.forEach(e=> e.textContent = cur);
    }

    window.riAddRow = function(){
        const idx = tableBody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="items[${idx}][product_id]" class="form-select prods">
                    <option value="">— Seleccionar —</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" data-price="{{ (float)$p->unit_price }}" data-tax="{{ (float)$p->tax_rate }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><textarea name="items[${idx}][description]" class="form-control desc" rows="1" style="min-height:38px;resize:vertical;overflow:auto;" required></textarea></td>
            <td><input name="items[${idx}][quantity]" type="number" step="0.001" min="0" class="form-control qty" value="1"></td>
            <td><input name="items[${idx}][unit_price]" type="number" step="0.01" min="0" class="form-control price" value="0"></td>
            <td><input name="items[${idx}][discount]" type="number" step="0.01" min="0" class="form-control disc" value="0"></td>
            <td><input name="items[${idx}][tax_rate]" type="number" step="0.01" min="0" class="form-control tax" value="0"></td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="riRemoveRow(this)">—</button></td>
        `;
        tableBody.appendChild(tr);
        hookRow(tr);
        recalc();
    };

    window.riRemoveRow = function(btn){
        btn.closest('tr').remove();
        recalc();
    };

    function autosize(el){ el.style.height='auto'; el.style.height = el.scrollHeight + 'px'; }

    function hookRow(scope){
        scope.querySelectorAll('.qty,.price,.disc,.tax').forEach(i=> i.addEventListener('input', recalc));
        scope.querySelectorAll('textarea.desc').forEach(t=>{ autosize(t); t.addEventListener('input', ()=>autosize(t)); });

        const sel = scope.querySelector('.prods');
        if(sel){
            sel.addEventListener('change', ()=>{
                const opt = sel.options[sel.selectedIndex];
                const price = parseFloat(opt.getAttribute('data-price')||'0');
                const tax   = parseFloat(opt.getAttribute('data-tax')||'0');
                const priceInput = scope.querySelector('.price');
                const taxInput   = scope.querySelector('.tax');
                if(priceInput) priceInput.value = isFinite(price)? price : 0;
                if(taxInput)   taxInput.value   = isFinite(tax)?   tax   : 0;
                recalc();
            });
        }
    }

    tableBody.querySelectorAll('tr').forEach(tr=>hookRow(tr));
    currencyInput?.addEventListener('input', recalc);
    recalc();
})();
</script>
