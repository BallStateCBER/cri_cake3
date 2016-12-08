<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?= $this->element('Communities/admin_header', [
    'adminHeader' => $adminHeader,
    'communityId' => $community->id,
    'surveyId' => null
]) ?>

<div id="CommunityAdminProgressForm">
    <?php
        echo $this->Form->create($community);

        function score_radio_input($step, $score, $view) {
            $retval = $view->Form->radio(
                'score',
                [$step => "Step $step"],
                ['hiddenField' => false]
            );
            return $retval;
        }


        $steps = [1, 2, 3, 4];
        foreach ($steps as $step) {
            echo score_radio_input($step, $community->score, $this);
            if (isset($criteria[$step])) {
                echo '<ul>';
                foreach ($criteria[$step] as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    list($criterion, $passed) = $item;
                    if ($passed) {
                        echo '<li class="pass"><span class="glyphicon glyphicon-ok"></span>';
                    } else {
                        echo '<li class="fail"><span class="glyphicon glyphicon-remove"></span>';
                    }
                    echo $criterion.'</li>';
                }
                echo '</ul>';
            }
        }
    ?>

    <?= score_radio_input(5, $community->score, $this) ?>

    <?= $this->Form->button(
        'Update',
        ['class' => 'btn btn-primary']
    ) ?>
    <?= $this->Form->end() ?>
</div>
