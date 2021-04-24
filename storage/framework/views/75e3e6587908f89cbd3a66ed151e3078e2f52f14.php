<?php $__env->startSection('content'); ?>



    <table>
        <?php if(count($data['quotes']) > 0): ?>
            <?php $__currentLoopData = $data['quotes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $quote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                
                <?php if($key == 0): ?>
                    <tr>
                        <?php $__currentLoopData = $quote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $attribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td><?php echo e($key); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                <?php endif; ?>

                
                <tr>
                    <?php $__currentLoopData = $quote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td><?php echo e($attribute); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    </table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/lrjohnst/domains/lv1.phitech.dev/private_html/resources/views/pages/StocksKeyIndicators.blade.php ENDPATH**/ ?>