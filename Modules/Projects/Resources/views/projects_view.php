<div id="headerbar">
    <h1 class="headerbar-title"><?php echo htmlspecialchars($project->project_name ?? 'Project'); ?></h1>
    
    <div class="headerbar-item pull-right">
        <a class="btn btn-sm btn-primary" href="<?php echo route('projects.edit', ['project' => $project->project_id]); ?>">
            <i class="fa fa-edit"></i> <?php _trans('edit'); ?>
        </a>
    </div>
</div>

<div id="content">
    <div class="panel panel-default">
        <div class="panel-heading"><?php _trans('project_details'); ?></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt><?php _trans('project_name'); ?>:</dt>
                <dd><?php echo htmlspecialchars($project->project_name ?? ''); ?></dd>
                
                <dt><?php _trans('client'); ?>:</dt>
                <dd><?php echo htmlspecialchars($project->client->client_name ?? ''); ?></dd>
            </dl>
        </div>
    </div>
    
    <?php if (isset($tasks) && count($tasks) > 0): ?>
    <div class="panel panel-default">
        <div class="panel-heading"><?php _trans('tasks'); ?></div>
        <div class="panel-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?php _trans('task_name'); ?></th>
                        <th><?php _trans('status'); ?></th>
                        <th><?php _trans('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task->task_name ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($task->task_status ?? ''); ?></td>
                        <td>
                            <a href="<?php echo route('tasks.edit', ['task' => $task->task_id]); ?>" 
                               class="btn btn-xs btn-default">
                                <i class="fa fa-edit"></i> <?php _trans('edit'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
