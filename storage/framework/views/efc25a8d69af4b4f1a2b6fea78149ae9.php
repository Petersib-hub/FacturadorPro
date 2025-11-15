<?php $__env->startSection('title', 'Factura'); ?>

<?php $__env->startPush('head'); ?>
    <style>
        .desc-cell { white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere }
        .table-responsive { overflow: visible }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h4 class="mb-0">Factura <?php echo e($invoice->number); ?></h4>

        <div class="btn-group">
            <a href="<?php echo e(route('invoices.pdf', $invoice)); ?>" class="btn btn-sm btn-success" target="_blank" rel="noopener">Descargar PDF</a>
            <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="visually-hidden">Más acciones</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sendEmailModal">Enviar por email</a></li>
                <li>
                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('markSentForm').submit();">Marcar como enviada</a>
                </li>
                <?php $publicUrl = $invoice->public_token ? route('public.invoices.show', $invoice->public_token) : null; ?>
                <?php if($publicUrl): ?>
                    <li><a class="dropdown-item" href="#" onclick="copyPublicLink('<?php echo e($publicUrl); ?>', <?php echo e($invoice->id); ?>)">Copiar enlace público</a></li>
                    <li><a class="dropdown-item" href="<?php echo e($publicUrl); ?>" target="_blank" rel="noopener">Abrir portal del cliente</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo e(route('invoices.edit', $invoice)); ?>">Editar</a></li>
                
                <li><a class="dropdown-item text-warning" href="#" data-bs-toggle="modal" data-bs-target="#rectifyModal">Rectificar…</a></li>
                
            </ul>
        </div>
    </div>

    
    <?php if(!empty($invoice->origin_budget_number)): ?>
        <div class="alert alert-info py-2 mb-3">
            Factura derivada del presupuesto
            <strong><?php echo e($invoice->origin_budget_number); ?></strong>
            <?php if(!empty($invoice->origin_budget_id)): ?>
                (<a href="<?php echo e(route('budgets.show', $invoice->origin_budget_id)); ?>">ver presupuesto</a>)
            <?php endif; ?>
        </div>
    <?php endif; ?>

    
    <div class="mb-2">
        <?php echo $__env->make('verifactu.status-badge', ['invoice' => $invoice], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php if ($__env->exists('verifactu._qr', ['invoice' => $invoice])) echo $__env->make('verifactu._qr', ['invoice' => $invoice], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php if(auth()->guard()->check()): ?> <?php if ($__env->exists('verifactu._actions', ['invoice' => $invoice])) echo $__env->make('verifactu._actions', ['invoice' => $invoice], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php endif; ?>
    </div>

    <form id="markSentForm" method="POST" action="<?php echo e(route('invoices.markSent', $invoice)); ?>" class="d-none">
        <?php echo csrf_field(); ?>
    </form>

    <?php
        $statusMap = [
            'draft' => ['label' => 'Borrador', 'class' => 'bg-secondary'],
            'pending' => ['label' => 'Pendiente', 'class' => 'bg-warning text-dark'],
            'sent' => ['label' => 'Enviada', 'class' => 'bg-info text-dark'],
            'paid' => ['label' => 'Pagada', 'class' => 'bg-success'],
            'void' => ['label' => 'Anulada', 'class' => 'bg-dark'],
        ];
        $sLabel = $statusMap[$invoice->status]['label'] ?? ucfirst($invoice->status);
        $sClass = $statusMap[$invoice->status]['class'] ?? 'bg-light text-dark';
    ?>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <div class="row gy-2">
                <div class="col-md-6">
                    <div><strong>Cliente:</strong> <?php echo e($invoice->client->name); ?></div>
                    <?php if($invoice->client->email): ?>
                        <div><strong>Email:</strong> <?php echo e($invoice->client->email); ?></div>
                    <?php endif; ?>
                    <?php if($invoice->client->tax_id): ?>
                        <div><strong>NIF/CIF:</strong> <?php echo e($invoice->client->tax_id); ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div><strong>Fecha:</strong> <?php echo e(optional($invoice->date)->format('d/m/Y')); ?></div>
                    <div><strong>Vence:</strong> <?php echo e(optional($invoice->due_date)->format('d/m/Y') ?? '—'); ?></div>
                    <div><strong>Estado:</strong> <span class="badge <?php echo e($sClass); ?>"><?php echo e($sLabel); ?></span></div>

                    
                    <?php if(!empty($invoice->origin_budget_number)): ?>
                        <div><strong>Presupuesto origen:</strong>
                            <?php if(!empty($invoice->origin_budget_id)): ?>
                                <a href="<?php echo e(route('budgets.show', $invoice->origin_budget_id)); ?>"><?php echo e($invoice->origin_budget_number); ?></a>
                            <?php else: ?>
                                <?php echo e($invoice->origin_budget_number); ?>

                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-soft mb-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th class="desc-cell">Descripción</th>
                            <th class="text-end">Cant.</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Desc. %</th>
                            <th class="text-end">IVA %</th>
                            <th class="text-end">Total línea</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="desc-cell"><?php echo e($it->description); ?></td>
                                <td class="text-end"><?php echo e(number_format($it->quantity, 3, ',', '.')); ?></td>
                                <td class="text-end"><?php echo e(number_format($it->unit_price, 2, ',', '.')); ?> €</td>
                                <td class="text-end"><?php echo e(rtrim(rtrim((string) $it->discount, '0'), '.')); ?></td>
                                <td class="text-end"><?php echo e(rtrim(rtrim((string) $it->tax_rate, '0'), '.')); ?></td>
                                <td class="text-end"><?php echo e(number_format($it->total_line, 2, ',', '.')); ?> €</td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3 text-end">
                <div><strong>Subtotal:</strong> <?php echo e(number_format($invoice->subtotal, 2, ',', '.')); ?> €</div>
                <div><strong>Impuestos:</strong> <?php echo e(number_format($invoice->tax_total, 2, ',', '.')); ?> €</div>
                <div class="fs-5"><strong>Total:</strong> <?php echo e(number_format($invoice->total, 2, ',', '.')); ?> €</div>
            </div>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <h6 class="mb-3">Pagos</h6>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Método</th>
                            <th>Notas</th>
                            <th class="text-end">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(optional($p->payment_date)->format('d/m/Y')); ?></td>
                                <td><?php echo e(ucfirst(str_replace('_', ' ', $p->method))); ?></td>
                                <td><?php echo e($p->notes); ?></td>
                                <td class="text-end"><?php echo e(number_format($p->amount, 2, ',', '.')); ?> €</td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Sin pagos registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php
                $paid = (float) $invoice->amount_paid;
                $pending = max(0, (float) $invoice->total - $paid);
            ?>

            <div class="d-flex justify-content-between align-items-center">
                <div><strong>Pagado:</strong> <?php echo e(number_format($paid, 2, ',', '.')); ?> €</div>
                <div><strong>Pendiente:</strong> <?php echo e(number_format($pending, 2, ',', '.')); ?> €</div>
            </div>

            <hr>

            <form id="payForm" class="row g-2 mt-2" method="post" action="<?php echo e(route('invoices.payments.store', $invoice)); ?>">
                <?php echo csrf_field(); ?>
                <div class="col-md-3">
                    <label class="form-label">Importe</label>
                    <input id="amount_display" class="form-control" inputmode="decimal" autocomplete="off" placeholder="0,00">
                    <input type="hidden" name="amount" id="amount" required>
                    <small class="text-muted">Se acepta coma o punto. Se guardará como <?php echo e($invoice->currency ?? 'EUR'); ?>.</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha</label>
                    <input name="payment_date" type="date" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Método</label>
                    <select name="method" class="form-select">
                        <option value="bank_transfer">Transferencia</option>
                        <option value="cash">Efectivo</option>
                        <option value="card">Tarjeta</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Notas</label>
                    <input name="notes" class="form-control" placeholder="Opcional">
                </div>
                <div class="col-12 mt-2 text-end">
                    <button class="btn btn-brand">Registrar pago</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post" action="<?php echo e(route('invoices.email', $invoice)); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="sendEmailModalLabel">Enviar factura por email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Para</label>
                        <input type="email" name="to" class="form-control" required value="<?php echo e(old('to', $invoice->client->email ?? '')); ?>" placeholder="cliente@dominio.com">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Asunto (opcional)</label>
                        <input type="text" name="subject" class="form-control" maxlength="190" placeholder="Factura <?php echo e($invoice->number); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">CC (opcional)</label>
                        <input type="email" name="cc" class="form-control" placeholder="copias@dominio.com">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">BCC (opcional)</label>
                        <input type="email" name="bcc" class="form-control" placeholder="ocultas@dominio.com">
                    </div>
                    <div class="small text-muted">Se adjuntará el PDF de la factura.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-brand">Enviar</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="rectifyModal" tabindex="-1" aria-labelledby="rectifyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post" action="<?php echo e(route('invoices.rectify', $invoice)); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="rectifyModalLabel">Crear factura rectificativa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Motivo de rectificación</label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Describe el motivo..."></textarea>
                    <small class="text-muted">Se creará una factura con líneas negativas por los mismos importes.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-warning">Crear rectificativa</button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const LOCALE = '<?php echo e(str_replace("_", "-", app()->getLocale() ?: "es-ES")); ?>' || 'es-ES';
    const CURRENCY = '<?php echo e($invoice->currency ?? "EUR"); ?>';
    const fmt = new Intl.NumberFormat(LOCALE, { style: 'currency', currency: CURRENCY, minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const $display = document.getElementById('amount_display');
    const $hidden  = document.getElementById('amount');
    const $form    = document.getElementById('payForm');
    const PENDING  = <?php echo e(number_format($pending, 2, '.', '')); ?>;

    $hidden.value = PENDING.toFixed(2);
    $display.value = fmt.format(PENDING);

    function parseMoney(text) {
        if (!text) return NaN;
        let raw = text.toString().replace(/[^\d,\.\-\,]/g, '');
        if (raw.includes('.') && raw.includes(',')) raw = raw.replace(/\./g, '').replace(',', '.');
        else if (raw.includes(',') && !raw.includes('.')) raw = raw.replace(',', '.');
        const n = parseFloat(raw);
        return isFinite(n) ? n : NaN;
    }

    $display.addEventListener('input', () => {
        const n = parseMoney($display.value);
        $hidden.value = isFinite(n) ? n.toFixed(2) : '';
    });

    $display.addEventListener('blur', () => {
        const n = parseMoney($display.value);
        if (isFinite(n)) { $hidden.value = n.toFixed(2); $display.value = fmt.format(n); }
        else { $hidden.value = ''; $display.value = ''; }
    });

    $display.addEventListener('focus', () => {
        const n = parseMoney($display.value);
        $display.value = isFinite(n) ? (n + '').replace('.', ',') : '';
        setTimeout(() => $display.select(), 0);
    });

    $form.addEventListener('submit', (e) => {
        const n = parseMoney($display.value);
        if (!isFinite(n) || n <= 0) { e.preventDefault(); alert('Introduce un importe válido mayor que 0.'); $display.focus(); }
        else { $hidden.value = n.toFixed(2); }
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\laravel\factura\facturadorPro\resources\views/invoices/show.blade.php ENDPATH**/ ?>