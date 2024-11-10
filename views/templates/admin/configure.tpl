<form method="post" action="{$link->getAdminLink('AdminModules')}">
    <input type="hidden" name="configure" value="{$module->name}">
    <input type="hidden" name="tab_module" value="{$module->tab}">
    <input type="hidden" name="module_name" value="{$module->name}">
    <input type="hidden" name="submitAddFlag" value="1">

    <div class="panel">
        <div class="panel-heading">
            <i class="icon-flag"></i> {$module->l('Add a new flag')}
        </div>
        <div class="form-group">
            <label>{$module->l('Flag name')}</label>
            <input type="text" name="flag_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>{$module->l('Condition (optional)')}</label>
            <input type="checkbox" name="flag_condition_enabled" id="flag_condition_enabled">
            <div class="condition-fields" style="display: none;">
                <label>{$module->l('Select Condition')}</label>
                <div class="form-inline">
                    <select name="flag_condition_mode" class="form-control">
                        <option value="Quantity">{$module->l('Quantity in stock')}</option>
                    </select>
                    <select name="flag_condition_operator" class="form-control">
                        <option value=">">{$module->l('Greater than')}</option>
                        <option value="<">{$module->l('Less than')}</option>
                        <option value="=">{$module->l('Equal to')}</option>
                    </select>
                    <input type="number" name="flag_condition_value" class="form-control" min="0">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">
            {$module->l('Add Flag')}
        </button>
    </div>
</form>

<div class="panel">
    <div class="panel-heading">
        <i class="icon-list"></i> {$module->l('Existing Flags')}
    </div>
    <ul>
        {foreach from=$flags item=flag}
            <li style="margin-bottom: 5px">
                {$flag.name} {if $flag.condition} - {$module->l('Condition')}: {$module->l($flag.condition)}{/if}
                <form method="post" action="{$link->getAdminLink('AdminModules')}" style="display:inline;">
                    <input type="hidden" name="configure" value="{$module->name}">
                    <input type="hidden" name="tab_module" value="{$module->tab}">
                    <input type="hidden" name="module_name" value="{$module->name}">
                    <input type="hidden" name="submitDeleteFlag" value="1">
                    <input type="hidden" name="flag_id" value="{$flag.id_flag}">
                    <button type="submit" class="btn btn-danger">
                        <i class="icon-trash icon-white"></i> {$module->l('Delete')}
                    </button>
                </form>
                <button type="button" class="btn btn-warning edit-flag" data-toggle="modal"
                        data-target="#editFlagModal" data-id="{$flag.id_flag}" data-name="{$flag.name}"
                        data-condition="{$flag.condition}">
                    <i class="icon-edit icon-white"></i> {$module->l('Edit')}
                </button>
                {if $flag.is_global}
                    <form method="post" action="{$link->getAdminLink('AdminModules')}" style="display:inline;">
                        <input type="hidden" name="configure" value="{$module->name}">
                        <input type="hidden" name="tab_module" value="{$module->tab}">
                        <input type="hidden" name="module_name" value="{$module->name}">
                        <input type="hidden" name="submitUnsetGlobalFlag" value="1">
                        <input type="hidden" name="flag_id" value="{$flag.id_flag}">
                        <button type="submit" class="btn btn-info">
                            <i class="icon-globe icon-white"></i> {$module->l('Unset as Global')}
                        </button>
                    </form>
                {else}
                    <form method="post" action="{$link->getAdminLink('AdminModules')}" style="display:inline;">
                        <input type="hidden" name="configure" value="{$module->name}">
                        <input type="hidden" name="tab_module" value="{$module->tab}">
                        <input type="hidden" name="module_name" value="{$module->name}">
                        <input type="hidden" name="submitSetGlobalFlag" value="1">
                        <input type="hidden" name="flag_id" value="{$flag.id_flag}">
                        <button type="submit" class="btn btn-info">
                            <i class="icon-globe icon-white"></i> {$module->l('Set as Global')}
                        </button>
                    </form>
                {/if}
            </li>
        {/foreach}
    </ul>
</div>

<div class="modal fade" id="editFlagModal" tabindex="-1" role="dialog" aria-labelledby="editFlagModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{$link->getAdminLink('AdminModules')}">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFlagModalLabel">{$module->l('Edit Flag')}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="configure" value="{$module->name}">
                    <input type="hidden" name="tab_module" value="{$module->tab}">
                    <input type="hidden" name="module_name" value="{$module->name}">
                    <input type="hidden" name="submitEditFlag" value="1">
                    <input type="hidden" name="flag_id" id="edit_flag_id">
                    <div class="form-group">
                        <label>{$module->l('Flag name')}</label>
                        <input type="text" name="flag_name" id="edit_flag_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{$module->l('Condition (optional)')}</label>
                        <input type="checkbox" name="flag_condition_enabled" id="edit_flag_condition_enabled">
                        <div class="condition-fields" style="display: none;">
                            <label>{$module->l('Select Condition')}</label>
                            <div class="form-inline">
                                <select name="flag_condition_mode" id="edit_flag_condition_mode" class="form-control">
                                    <option value="Quantity">{$module->l('Quantity in stock')}</option>
                                </select>
                                <select name="flag_condition_operator" id="edit_flag_condition_operator"
                                        class="form-control">
                                    <option value=">">{$module->l('Greater than')}</option>
                                    <option value="<">{$module->l('Less than')}</option>
                                    <option value="=">{$module->l('Equal to')}</option>
                                </select>
                                <input type="number" name="flag_condition_value" id="edit_flag_condition_value"
                                       class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{$module->l('Close')}</button>
                    <button type="submit" class="btn btn-primary">{$module->l('Save changes')}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#editFlagModal #edit_flag_condition_enabled').change(function () {
            if ($(this).is(':checked')) {
                $('#editFlagModal .condition-fields').show();
                $('#editFlagModal input[name="flag_condition_value"]').prop('required', true);
            } else {
                $('#editFlagModal .condition-fields').hide();
                $('#editFlagModal input[name="flag_condition_value"]').prop('required', false);
            }
        });

        $('.edit-flag').click(function () {
            var flagId = $(this).data('id');
            var flagName = $(this).data('name');
            var flagCondition = $(this).data('condition');

            $('#edit_flag_id').val(flagId);
            $('#edit_flag_name').val(flagName);

            if (flagCondition) {
                $('#edit_flag_condition_enabled').prop('checked', true);
                $('#editFlagModal .condition-fields').show();
                var conditionParts = flagCondition.match(/(Quantity) ([><=]) (\d+)/);
                if (conditionParts) {
                    $('#edit_flag_condition_mode').val(conditionParts[1]);
                    $('#edit_flag_condition_operator').val(conditionParts[2]);
                    $('#edit_flag_condition_value').val(conditionParts[3]);
                }
            } else {
                $('#edit_flag_condition_enabled').prop('checked', false);
                $('#editFlagModal .condition-fields').hide();
            }
        });

        $('#flag_condition_enabled').change(function () {
            if ($(this).is(':checked')) {
                $('.condition-fields').show();
                $('input[name="flag_condition_value"]').prop('required', true);
            } else {
                $('.condition-fields').hide();
                $('input[name="flag_condition_value"]').prop('required', false);
            }
        });
    });
</script>