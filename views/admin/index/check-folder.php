<?php
/**
 * @var \Zend\View\Renderer\PhpRenderer $this
 * @var array $files_data
 * @var int $total_files
 * @var int $total_files_can_recognized
 * @var string $error
 * @var bool $is_server
 */
?>

<?= flash() ?>

<?php if ($this->error): ?>

    <div class="error"><?= $this->error ?></div>

<?php else: ?>

    <?php if (count($files_data)): ?>
        <div class="total_info">
            <div class="total_info_row"><h4><?= __('Total files'); ?> </h4><?= $this->total_files ?></div>
            <div class="total_info_row"><h4><?= __('Importable files'); ?></h4><?= $this->total_files_can_recognized ?></div>
            <div class="origin" data-origin="<?= $is_server ? 'server' : 'upload' ?>"><h4><?= __('Origin'); ?></h4><?= $is_server ? __('Directory on server') : __('Uploaded files') ?></div>
            <button type="button" class="js-recognize_files"><?= __('Make recognize and add') ?></button>
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
                <?php foreach ($files_data as $file): ?>
                    <tr class="isset_<?= $file['file_isset_maps'] ?> row_id_<?= $i ?> <?= empty($file['error']) ? '' : 'error' ?>">
                        <td><?= $i ?></td>
                        <td class="filename" data-row-id="<?= $i ?>" data-filename="<?= $file['filename'] ?>" data-source="<?= $file['source'] ?>" data-has-error="<?= empty($file['error']) ? '0' : '1' ?>">
                            <?= $is_server ? $file['filename'] : $file['source'] ?>
                        </td>
                        <td><?= $file['file_size'] ?></td>
                        <td><?= $file['file_type'] ?></td>
                        <td><?= $file['file_isset_maps'] ?></td>
                        <td class="status">
                            <?php if ($is_server && !empty($file['error'])): ?>
                            <?= __('Error during upload.') ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php ++$i; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<?php endif; ?>
