<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('tasks') }}</h1>

    <div class="headerbar-item pull-right">
        <a class="btn btn-sm btn-primary" href="<?php echo route('tasks.create'); ?>">
            <i class="fa fa-plus"></i> {{ trans('new') }}
        </a>
    </div>
</div>

<div id="content">
    <?php if (isset($tasks) && $tasks->count() > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('task_name') }}</th>
                    <th>{{ trans('project') }}</th>
                    <th>{{ trans('status') }}</th>
                    <th>{{ trans('actions') }}</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?php echo htmlspecialchars($task->task_name ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($task->project->project_name ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($task->task_status ?? ''); ?></td>
                    <td>
                        <a href="<?php echo route('tasks.edit', ['task' => $task->task_id]); ?>"
                           class="btn btn-xs btn-default">
                            <i class="fa fa-edit"></i> {{ trans('edit') }}
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (method_exists($tasks, 'links')): ?>
            <div class="text-center">
                <?php echo $tasks->links(); ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">
            {{ trans('no_tasks') }}
        </div>
    <?php endif; ?>
</div>
