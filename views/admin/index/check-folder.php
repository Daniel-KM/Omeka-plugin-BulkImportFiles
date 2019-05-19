<?php


?>

<?php echo flash(); ?>

<?php
/**
 * @var \Zend\View\Renderer\PhpRenderer $this
 * @var array $files_data
 * @var int $total_files
 * @var int $total_files_can_recognized
 * @var string $error
 */

?>
<?php if ($this->error): ?>
    <div class="error"><?php echo $this->error; ?></div>
<?php else : ?>

    <?php if (count($files_data)): ?>
        <div class="total_info">
            <div class="total_info_row"><h4><?= __('Total files'); ?> </h4><?php echo $this->total_files; ?></div>
            <div class="total_info_row"><h4><?= __('Importable files'); ?></h4> <?php echo $this->total_files_can_recognized; ?></div>
            <button type="button" class="js-recognize_files"><?= __('Make recognize and add'); ?></button>
        </div>
        <table>
            <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 40%"><?= __('File name') ?></th>
                <th style="width: 10%"><?= __('Size') ?></th>
                <th style="width: 20%"><?= __('Media type') ?></th>
                <th style="width: 15%"><?= __('Has mapping') ?></th>
                <th style="width: 10%"><?= __('Status') ?></th>
            </tr>
            </thead>
            <tbody>
                <?php $i = 1; ?>
                <?php foreach ($files_data as $val) : ?>
                    <tr class="isset_<?php echo $val['file_isset_maps']; ?> row_id_<?= $i; ?>">
                        <td><?php echo $i; ?></td>
                        <td class="filename" data-row-id="<?= $i; ?>"><?= $val['filename']; ?></td>
                        <td><?= $val['file_size']; ?></td>
                        <td><?= $val['file_type']; ?></td>
                        <td><?= $val['file_isset_maps']; ?></td>
                        <td class="status"></td>
                        <?php ++$i; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<?php endif; ?>
