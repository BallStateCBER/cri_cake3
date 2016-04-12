<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?php if ($this->request->prefix == 'admin'): ?>
        <?= $this->Html->link(
            '<span class="glyphicon glyphicon-arrow-left"></span> Back to Survey Overview',
            [
                'prefix' => 'admin',
                'controller' => 'Surveys',
                'action' => 'view',
                $community->id,
                $survey->type
            ],
            [
                'class' => 'btn btn-default',
                'escape' => false
            ]
        ) ?>
    <?php else: ?>
        <?= $this->Html->link(
            '<span class="glyphicon glyphicon-arrow-left"></span> Back to Client Home',
            [
                'prefix' => 'client',
                'controller' => 'Communities',
                'action' => 'index'
            ],
            [
                'class' => 'btn btn-default',
                'escape' => false
            ]
        ) ?>
    <?php endif; ?>
</p>

<?php if (empty($unresponsive)): ?>
    <p class="alert alert-success">
        Good news! Everyone who has been sent an invitation to participate
        in this survey has submitted a response, so no reminders
        are necessary.
    </p>
<?php else: ?>
    <ul id="reminders">
        <li>
            Sending a reminder will re-send survey invitation emails.
        </li>
        <li>
            <a href="#" id="toggleUnresponsiveList">
                <?= $unresponsiveCount ?> <?= __n('person', 'people', $unresponsiveCount) ?>
            </a>
            <?= __n('has', 'have', $unresponsiveCount) ?>
            been sent <?= __n('an invitation', 'invitations', $unresponsiveCount) ?>
            to complete this survey and
            <?= __n('hasn\'t', 'haven\'t', $unresponsiveCount) ?>
            responded yet.
            <div class="well" id="unresponsiveList">
                <ul>
                    <?php foreach ($unresponsive as $person): ?>
                        <li>
                            <?php if ($person->name): ?>
                                <?= $person->name ?>
                            <?php else: ?>
                                (no name)
                            <?php endif; ?>

                            <?php if ($person->title): ?>
                                <span class="title">
                                    (<?= $person->title ?>)
                                </span>
                            <?php endif; ?>

                            <a href="mailto:<?= $person->email ?>" class="email">
                                <?= $person->email ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </li>
        <li>
            <?php if ($survey->reminder_sent): ?>
                A reminder was last sent for this survey on
                <strong>
                    <?= $survey->reminder_sent->format('F j, Y') ?>.
                </strong>
            <?php else: ?>
                No reminder has been sent for this survey yet.
            <?php endif; ?>
        </li>
    </ul>

    <p>
        <?= $this->Form->postLink(
            $survey->reminder_sent ? 'Send another reminder' : 'Send reminder',
            [
                'controller' => 'Surveys',
                'action' => 'remind',
                $this->request->prefix == 'admin' ? $survey->id : $survey->type
            ],
            ['class' => 'btn btn-primary']
        ) ?>
    </p>

    <?php $this->append('buffered'); ?>
        $('#reminders #toggleUnresponsiveList').click(function (event) {
            event.preventDefault();
            $('#unresponsiveList').slideToggle();
        });
    <?php $this->end(); ?>
<?php endif; ?>