<div id="headerbar">
    <h1 class="headerbar-title">
        <?php echo isset($task->task_id) && $task->task_id ? _trans('edit_task') : _trans('new_task'); ?>
    </h1>
</div>

<div id="content">
    <form method="post" action="<?php echo isset($task->task_id) && $task->task_id ? route('tasks.update', ['task' => $task->task_id]) : route('tasks.store'); ?>">
        <?php _csrf_field(); ?>
        <?php if (isset($task->task_id) && $task->task_id): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="task_name"><?php _trans('task_name'); ?> *</label>
            <input type="text" 
                   name="task_name" 
                   id="task_name" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($task->task_name ?? ''); ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="project_id"><?php _trans('project'); ?></label>
            <select name="project_id" id="project_id" class="form-control">
                <option value=""><?php _trans('select_project'); ?></option>
                <?php if (isset($projects)): ?>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project->project_id; ?>" 
                                <?php echo (isset($task->project_id) && $task->project_id == $project->project_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($project->project_name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="task_status"><?php _trans('status'); ?></label>
            <select name="task_status" id="task_status" class="form-control">
                <?php if (isset($task_statuses)): ?>
                    <?php foreach ($task_statuses as $status_id => $status): ?>
                        <option value="<?php echo $status_id; ?>" 
                                <?php echo (isset($task->task_status) && $task->task_status == $status_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status['label'] ?? $status); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="task_finish_date"><?php _trans('finish_date'); ?></label>
            <input type="date" 
                   name="task_finish_date" 
                   id="task_finish_date" 
                   class="form-control" 
                   value="<?php echo $task->task_finish_date ?? ''; ?>">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> <?php _trans('save'); ?>
            </button>
            <a href="<?php echo route('tasks.index'); ?>" class="btn btn-default">
                <i class="fa fa-times"></i> <?php _trans('cancel'); ?>
            </a>
        </div>
    </form>
</div>
