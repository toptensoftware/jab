<h1>Internal Error</h1>
<p>Error: <?php echo htmlspecialchars($model->getMessage()) ?></p>

<?php if ($model instanceof jabPhpException): ?>
<p>Location: <b><?php echo htmlspecialchars($model->errfile) ?></b> line <b><?php echo $model->errline ?></b></p>
<?php else: ?>
<p>Location: <b><?php echo htmlspecialchars($model->getFile()) ?></b> line <b><?php echo $model->getLine() ?></b></p>
<?php endif; ?>

<?php if (sizeof($model->getTrace())): ?>
<h3>Stack Trace</h3>
<ol>
<?php foreach ($model->getTrace() as $frame): ?>
<li><?php echo htmlspecialchars($frame['file'])."(".$frame['line'].") ".htmlspecialchars($frame['function'])."" ?></li>
<?php endforeach; ?>
</ol>
<?php endif; ?>

<p>Sorry for the inconvenience, please <a href="/contact">Contact</a> Topten Software if this problem persists.</p>