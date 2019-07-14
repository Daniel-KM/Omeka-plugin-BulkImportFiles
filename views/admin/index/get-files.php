<?php
/**
 * @var Zend_View $this
 * @var array $files_data_for_view
 * @var array $listTerms
 * @var array $filesMaps
 */
?>

<div class="messages">
    <div class="response"></div>
</div>

<legend><?= __('Selected files') ?></legend>

<table class="tablesaw tablesaw-stack" data-tablesaw-mode="stack" id="table-selected-files">
    <thead>
        <tr>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($this->files_data_for_view as $files_data): ?>
        <?php
        $set_active = isset($files_data['file']['item_id']) ? ' set_active' : '';
        $itemId = isset($files_data['file']['item_id']) ? $files_data['file']['item_id'] : '';
        ?>
        <tr class="separator selected-files-row<?= $set_active ?>" data-file-type="<?= $files_data['file']['type'] ?>" data-file-item-id="<?= $itemId ?>">
            <td colspan="1">
                <span>
                    <span class="label"><?= __('File name') ?></span>
                    <span><?= $files_data['file']['name'] ?></span>
                </span>
                <span>
                    <span class="label"><?= __('Media type') ?></span>
                    <span><?= $files_data['file']['type'] ?></span>
                    <?php if ($itemId): ?>
                        <a class="small red button file-type delete-file-type" href="#"><?= __('Delete') ?></a>
                    <?php else: ?>
                        <a class="small green button file-type add-file-type" href="#"><?= __('Add') ?></a>
                    <?php endif; ?>
                </span>
                <span>
                    <?php echo count($files_data['source_data']) ? sprintf(__('%d available fields'), count($files_data['source_data'])) : __('No available fields'); ?>
                </span>

                <?php if (empty($files_data['errors']) || $files_data['errors'] == ""): ?>
                    <div class="action-button">
                        <a style="display: none" data-sidebar-content-url="" class="o-icon-delete sidebar-content"
                           href="#"
                           title="<?= __('Delete') ?>"
                           aria-label="<?= __('Delete') ?>"></a>
                        <a data-sidebar-content-url="" class="o-icon-more sidebar-content"
                           href="#"
                           title="<?= __('Show all data') ?>"
                           aria-label="<?= __('Show all data') ?>" style="font-size: 30px; line-height: 15px;">...</a>
                    </div>

                    <div class="full_info">
                        <table>
                            <tr>
                                <td><?php echo count($files_data['source_data']) ? sprintf(__('%d available fields'), count($files_data['source_data'])) : __('No available fields'); ?>
                                    <div class="js-save-button">
                                        <button type="submit" name="add-item-submit"><?= __('Save') ?></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table class="tablesaw tablesaw-stack" data-tablesaw-mode="stack" id="subtablesaw">
                                        <thead>
                                            <tr>
                                                <th><?= __('Element') ?></th>
                                                <th><?= __('Field') ?></th>
                                                <th><?= __('Value') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (!empty($files_data['source_data'])):
                                            //var_dump($files_data['source_data']);
                                            foreach ($files_data['source_data'] as $val):
                                                $additional_class = '';
                                                $find_field = '';

                                                if (!empty($files_data['recognized_data'])):
                                                    foreach ($files_data['recognized_data'] as $recognized_data_val):
                                                        if ($recognized_data_val['field'] == $val['key']):
                                                            $additional_class = 'with_property';
                                                            $find_field .= '<div class="omeka_property">';
                                                            $find_field .= '<span class="omeka_property_name">' . $recognized_data_val['target'] . '</span>';
                                                            $find_field .= '<div class="js-action-property-button">';
                                                            // $find_field .= '<span class="js-add-action">+</span>';
                                                            $find_field .= '<span class="js-remove-action">-</span>';
                                                            $find_field .= '</div></div>';
                                                        endif;
                                                    endforeach;
                                                endif;

                                                if ($additional_class == ''):
                                                    $find_field .= '<div class="omeka_list_property">';
                                                    $find_field .= '</div>';
                                                    $find_field .= '<div class="omeka_property">';
                                                    $find_field .= '<div class="js-action-property-button">';
                                                    $find_field .= '<span class="js-add-action">+</span>';
                                                    // $find_field .= '<span class="js-remove-action">-</span>';
                                                    $find_field .= '</div></div>';
                                                else:
                                                    $find_field .= '<div class="omeka_list_property">';
                                                    $find_field .= '</div>';
                                                    $find_field .= '<div class="omeka_property">';
                                                    $find_field .= '<div class="js-action-property-button">';
                                                    $find_field .= '<span class="js-add-action">+</span>';
                                                    $find_field .= '</div></div>';
                                                endif;
                                            ?>

                                                <tr data-property-count="0" class="<?= $additional_class; ?>">
                                                    <td><?= $find_field; ?></td>
                                                    <td class="js-file_field_property"><?= $val['key'] ?></td>
                                                    <td><?= $val['value'] ?></td>
                                                </tr>

                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>

                    </div>
                <?php else: ?>
                <?php // echo '--' . $files_data['errors'] . '--';  ?>
                <?php endif; ?>
            </td>

        </tr>
        <?php if (empty($files_data['errors']) || $files_data['errors'] == ''): ?>
            <?php if (!empty($files_data['recognized_data'])): ?>
                <tr>
                    <td><?= __('Recognized fields') ?></td>
                </tr>
                <tr>
                    <td>
                        <table class="tablesaw tablesaw-stack" data-tablesaw-mode="stack" id="subtablesaw">
                            <thead>
                                <tr>
                                    <th><?= __('Element') ?></th>
                                    <th><?= __('Field') ?></th>
                                    <th><?= __('Value') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($files_data['recognized_data'] as $val): ?>
                                    <tr>
                                        <td><?= $val['target'] ?></td>
                                        <td><?= $val['field'] ?></td>
                                        <td><?= $val['value'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            <?php else: ?>
                <tr>
                    <td class="errors"><?= __('No recognized fields') ?></td>
                </tr>
            <?php endif; ?>

        <?php else: ?>

            <tr>
                <td class="errors">
                    <div>
                        <?=  $files_data['errors'] ?>
                    </div>
                </td>
            </tr>

        <?php endif; ?>

    <?php endforeach; ?>

    </tbody>
</table>

<div class="listterms type_hidden">
    <div class="listterms_with_action">
        <select class="listterms_select">
            <?php foreach ($listTerms as $key => $val): ?>
                <option value="<?= $key ?>"><?= $val ?></option>
            <?php endforeach; ?>
        </select>
        <div class="js-action-property-button">
            <span class="js-single-remove-action">-</span>
        </div>
    </div>
</div>
