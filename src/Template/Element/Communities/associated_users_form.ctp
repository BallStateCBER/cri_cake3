<div class="panel panel-default" id="<?= $role ?>_interface">
    <div class="panel-heading">
        <h2 class="panel-title">
            <?= ucwords($role) ?>s
        </h2>

        <a href="#" class="btn btn-default btn-xs toggle_add" data-user-type="<?= $role ?>">
            Add new <?= $role ?>
        </a>

        <?php if (! empty($users)): ?>
            <a href="#" class="btn btn-default btn-xs toggle_select" data-user-type="<?= $role ?>">
                Add existing <?= $role ?>
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($errors) && ! empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">
                    &times;
                </span>
            </button>
            <?php if (count($errors) > 1): ?>
                <ul>
                    <?php foreach ($errors as $errMsg): ?>
                        <li>
                            <?php if (is_array($errMsg)): ?>
                                <?= implode('<br />', array_values($errMsg)) ?>
                            <?php else: ?>
                                <?= $errMsg ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <?= $errors[0] ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="panel-body">
        <div id="<?= $role ?>_select" class="form-group well">
            <?= $this->Form->input(
                $role.'_id',
                [
                    'class' => 'form-control',
                    'data-user-type' => $role,
                    'empty' => true,
                    'label' => 'Select '.$role,
                    'options' => $users
                ]
            ) ?>
        </div>
        <div id="<?= $role ?>_add" class="well">
            <table class="table">
                <?php
                    $this->Form->templates($tableTemplate);
                    echo $this->Form->input(
                        'new_'.$role.'s_entry.name',
                        [
                            'class' => 'form-control',
                            'div' => ['class' => 'form-group'],
                            'label' => 'New '.$role.' name',
                            'required' => false
                        ]
                    );
                    echo $this->Form->input(
                        'new_'.$role.'s_entry.title',
                        [
                            'class' => 'form-control',
                            'div' => ['class' => 'form-group'],
                            'label' => 'Job Title'
                        ]
                    );
                    echo $this->Form->input(
                        'new_'.$role.'s_entry.organization',
                        [
                            'class' => 'form-control',
                            'div' => ['class' => 'form-group']
                        ]
                    );
                    echo $this->Form->input(
                        'new_'.$role.'s_entry.email',
                        [
                            'class' => 'form-control',
                            'div' => ['class' => 'form-group'],
                            'type' => 'email'
                        ]
                    );
                    echo $this->Form->input(
                        'new_'.$role.'s_entry.phone',
                        [
                            'class' => 'form-control',
                            'div' => ['class' => 'form-group']
                        ]
                    );
                    echo $this->Form->input(
                        'new_'.$role.'s_entry.password',
                        [
                            'class' => 'form-control',
                            'div' => ['class' => 'form-group'],
                            'type' => 'text'
                        ]
                    );
                    $this->Form->templates('bootstrap_form');
                ?>
            </table>
            <button class="add btn btn-default" data-user-type="<?= $role ?>">
                Add new <?= $role ?>
            </button>
        </div>
    </div>
</div>