<div class="page-header">
    <h1>
        <?php echo $titleForLayout; ?>
    </h1>
</div>

<h2>Admin functions</h2>
<ul>
    <li>
        <strong>
            <?= $this->Html->link(
                'Manage users',
                [
                    'prefix' => 'admin',
                    'controller' => 'Users',
                    'action' => 'index'
                ]
            ) ?>
        </strong>
        <br />Here, additional admins, consultants, and clients can be added.
    </li>
    <li>
        <strong>
            <?= $this->Html->link(
                'Manage communities',
                [
                    'prefix' => 'admin',
                    'controller' => 'Communities',
                    'action' => 'index'
                ]
            ) ?>
        </strong>
        <br />Here, you can add new communities as clients sign up. (more about adding communities further down)
        <br />This provides a list of communities and shows which have clients associated with them and which the general public can access charts and tables for.
        <br />For client communities, the following links are found under "Actions":
        <ul>
            <li>
                <strong>Progress</strong>
                <br />Shows what step the community is currently on (if 'score' > 0) and what criteria for advancement have been passed.
                <br />Intended to be a guide to help administrators quickly determine whether a community is ready for advancement or not.
                <br />A community's score can be updated through this page.
            </li>
            <li>
                <strong>Clients</strong>
                <br />Lists the clients associated with this community and their email addresses
            </li>
            <li>
                <strong>Performance Charts</strong>
            </li>
            <li>
                <strong>Officials Survey / Organizations Survey</strong>
                <br /> On these survey overview pages, you can
                <ul>
                    <li>
                        View and send <strong>invitations</strong>
                    </li>
                    <li>
                        Import and view <strong>responses</strong>
                    </li>
                    <li>
                        Manage <strong>unapproved respondents</strong>
                    </li>
                    <li>
                        View the community's calculated <strong>alignment</strong>, set its alignment, and set its pass/fail status.
                    </li>
                </ul>
            </li>
            <li>
                <strong>Performance Charts</strong>
            </li>
            <li>
                <strong>Edit</strong>
                <br />This page is where a community's settings, clients, consultants, surveys, and score are all managed.
            </li>
            <li>
                <strong>Delete</strong>
                <br />This is how you would delete a fake community that you set up for testing. This is permanent, so please use this with caution.
            </li>
        </ul>
    </li>
    <li>
        <strong>Adding/editing communities</strong>
        <br />When new user accounts are created, an email automatically goes out to the new user telling them what their password is and where they can log in.
        <br />Once a survey is created in SurveyMonkey, it must be looked up and selected on the community add/edit page. This "links" the community's CRI account and its SurveyMonkey surveys.
        <br />Only CRI surveys can be selected, because the CRI site looks through the selected survey for the specific question used in alignment calculation.
    </li>
</ul>

<h2>
    <a href="http://cri.cberdata.org/client"></a>
    <?= $this->Html->link(
        'Client Home',
        [
            'prefix' => 'client',
            'controller' => 'Communities',
            'action' => 'index'
        ]
    ) ?>
</h2>
<ul>
    <li>
        This is an elaborated version of the 'community progress' page, which shows the community's current stage,
        lists what advancement criteria have been passed, and displays action buttons for making purchases,
        sending invitations, importing responses, etc.
    </li>
    <li>
        Administrators can select a community and view this page as if they were that client.
    </li>
    <li>
        All actions available to clients are also available to administrators, e.g. sending out survey invitations.
    </li>
</ul>

<h2>Features Not Yet Implemented</h2>
<ul>
    <li>A section for consultants</li>
    <li>Automatically alerting administrators of new enrollment applications</li>
    <li>Facilitating/automating reminder emails to invited survey participants who haven't submitted responses</li>
</ul>