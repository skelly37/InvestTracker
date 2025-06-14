<?php
?>
    <script src="/assets/js/app.js"></script>
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>