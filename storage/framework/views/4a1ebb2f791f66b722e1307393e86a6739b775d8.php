<!doctype html>
<html lang="en">
    <head>
        <title><?php echo e(config('app.name', 'LSAPP')); ?></title>
        <link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">
    </head>
    <body>
        <?php echo $__env->yieldContent('content'); ?>
    </body>
</html>
<?php /**PATH /home/lrjohnst/domains/lv1.phitech.dev/private_html/resources/views/layouts/app.blade.php ENDPATH**/ ?>