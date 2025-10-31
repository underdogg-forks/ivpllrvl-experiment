<div id="headerbar">
    <h1 class="headerbar-title"><?php _trans('projects'); ?></h1>
    
    <div class="headerbar-item pull-right">
        <a class="btn btn-sm btn-primary" href="<?php echo route('projects.create'); ?>">
            <i class="fa fa-plus"></i> <?php _trans('new'); ?>
        </a>
    </div>
</div>

<div id="content">
    <?php if (isset($projects) && $projects->count() > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php _trans('project_name'); ?></th>
                    <th><?php _trans('client'); ?></th>
                    <th><?php _trans('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <td>
                        <a href="<?php echo route('projects.view', ['project' => $project->project_id]); ?>">
                            <?php echo htmlspecialchars($project->project_name ?? ''); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($project->client->client_name ?? ''); ?></td>
                    <td>
                        <a href="<?php echo route('projects.edit', ['project' => $project->project_id]); ?>" 
                           class="btn btn-xs btn-default">
                            <i class="fa fa-edit"></i> <?php _trans('edit'); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (method_exists($projects, 'links')): ?>
            <div class="text-center">
                <?php echo $projects->links(); ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <?php _trans('no_projects'); ?>
        </div>
    <?php endif; ?>
</div>
