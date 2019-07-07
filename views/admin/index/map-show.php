<?php

$head = array('title' => html_escape(__('Bulk Import Files')));

echo head($head);
echo common('bulkimportfiles-nav');

?>
<?php echo flash(); ?>

<?php if (count($filesMaps) == 0) : ?>

    <div><?= __('No available maps for import'); ?></div>

<?php else : ?>

    <div class="show">
        <h3><?= __('Available maps for import'); ?></h3>
        <?php foreach ($filesMaps as $item) : ?>
        <table class="tablesaw tablesaw-stack" data-tablesaw-mode="stack" id="table-selected-files">
            <thead>
                <tr></tr>
            </thead>
            <tbody>
                <?php
                $mediaType = $item['media_type'];
                ?>
                <tr>
                    <td class="file_type_property_td">
                        <div class="file_type_property">
                            <h4><?= __('Media type') ?></h4>

                            <a target="_blank" class="underline_link" >
                            <?= $mediaType ?>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php
                unset($item['media_type']);
                unset($item['item_id']);
                ?>
                <tr>
                    <td>
                        <table>
                            <thead>
                                <tr>
                                    <th><?= __('Element') ?></th>
                                    <th><?= __('Map file data field (xpath or object notation)') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($item as $metadata => $queries): ?>
                                <tr>
                                    <td><?= $metadata ?></td>
                                    <td>
                                        <table>
                                            <?php foreach ($queries as $query): ?>
                                            <tr>
                                                <td><?= $query ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </table>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php echo foot(); ?>
