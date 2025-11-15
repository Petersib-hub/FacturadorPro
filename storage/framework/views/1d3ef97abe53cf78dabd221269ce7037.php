<?php $__env->startSection('title','Dashboard'); ?>

<?php use Illuminate\Support\Str; ?>

<?php
    // Avatar del usuario (bienvenida)
    $u = auth()->user();
    $avatarUrl = $u?->avatar_path
        ? asset('storage/'.$u->avatar_path)
        : 'https://ui-avatars.com/api/?name='.urlencode($u?->name ?? 'User').'&background=2fca6c&color=fff&size=128';

    // Mapa de estados → badge para listas rápidas
    $statusMap = [
        'draft'   => ['label'=>'Borrador','class'=>'bg-secondary'],
        'pending' => ['label'=>'Pendiente','class'=>'bg-warning text-dark'],
        'sent'    => ['label'=>'Enviada','class'=>'bg-info text-dark'],
        'paid'    => ['label'=>'Pagada','class'=>'bg-success'],
        'void'    => ['label'=>'Anulada','class'=>'bg-dark'],
        'accepted'=> ['label'=>'Aceptado','class'=>'bg-success'],
        'rejected'=> ['label'=>'Rechazado','class'=>'bg-danger'],
    ];
?>

<?php $__env->startSection('content'); ?>
<div class="row g-3">

    
    <div class="col-12">
        <div class="card card-soft">
            <div class="card-body d-flex align-items-center gap-3">
                <img src="<?php echo e($avatarUrl); ?>" alt="Avatar" class="rounded-circle" style="width:56px;height:56px;object-fit:cover;">
                <div>
                    <div class="fw-semibold">¡Hola, <?php echo e($u?->name ?? 'Usuario'); ?>!</div>
                    <div class="text-muted small">Bienvenido a tu panel de control.</div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body">
                <div class="text-muted small">Facturado este mes</div>
                <div class="fs-4 fw-bold"><?php echo e(number_format($invoicedThisMonth,2,',','.')); ?> €</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body">
                <div class="text-muted small">Pendiente de cobro</div>
                <div class="fs-4 fw-bold"><?php echo e(number_format($pendingAmount,2,',','.')); ?> €</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body">
                <div class="text-muted small">Presupuestos abiertos</div>
                <div class="fs-4 fw-bold"><?php echo e($budgetsOpen); ?></div>
            </div>
        </div>
    </div>

    
    <div class="col-lg-7">
        <div class="card card-soft">
            <div class="card-body">
                <h6 class="mb-3">Facturas recientes</h6>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th class="text-end">Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $s = $statusMap[$i->status] ?? ['label'=>ucfirst($i->status),'class'=>'bg-light text-dark'];
                            ?>
                            <tr>
                                <td><a href="<?php echo e(route('invoices.show',$i)); ?>"><?php echo e($i->number); ?></a></td>
                                <td><?php echo e($i->client?->name); ?></td>
                                <td><?php echo e(optional($i->date)->format('d/m/Y')); ?></td>
                                <td class="text-end"><?php echo e(number_format($i->total,2,',','.')); ?> €</td>
                                <td><span class="badge <?php echo e($s['class']); ?>"><?php echo e($s['label']); ?></span></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Sin facturas.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-lg-5">
        <div class="card card-soft">
            <div class="card-body">
                <h6 class="mb-3">Top clientes (6 meses)</h6>

                <ul class="list-group list-group-flush">
                    <?php $__empty_1 = true; $__currentLoopData = $topClients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?php echo e($row->client?->name ?? '—'); ?></div>
                                <div class="small text-muted">
                                    
                                    <?php echo e($row->count_invoices); ?>

                                    <?php echo e(Str::plural('factura', $row->count_invoices)); ?>


                                    en el periodo
                                </div>
                            </div>
                            <strong><?php echo e(number_format($row->total_sum,2,',','.')); ?> €</strong>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="list-group-item text-muted">Sin datos.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    
    <div class="col-12">
        <div class="card card-soft">
            <div class="card-body">
                <h6 class="mb-3">Presupuestos recientes</h6>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th class="text-end">Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentBudgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $s = $statusMap[$b->status] ?? ['label'=>ucfirst($b->status),'class'=>'bg-light text-dark'];
                            ?>
                            <tr>
                                <td><a href="<?php echo e(route('budgets.show',$b)); ?>"><?php echo e($b->number); ?></a></td>
                                <td><?php echo e($b->client?->name); ?></td>
                                <td><?php echo e(optional($b->date)->format('d/m/Y')); ?></td>
                                <td class="text-end"><?php echo e(number_format($b->total,2,',','.')); ?> €</td>
                                <td><span class="badge <?php echo e($s['class']); ?>"><?php echo e($s['label']); ?></span></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Sin presupuestos.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\laravel\factura\facturadorPro\resources\views/dashboard.blade.php ENDPATH**/ ?>