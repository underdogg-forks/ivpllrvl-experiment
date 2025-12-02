<div id="headerbar">
    <h1 class="headerbar-title">
        <?php echo isset($project->project_id) && $project->project_id ? _trans('edit_project') : _trans('new_project'); ?>
    </h1>
</div>

<div id="content">
    <form method="post" action="<?php echo isset($project->project_id) && $project->project_id ? route('projects.update', ['project' => $project->project_id]) : route('projects.store'); ?>">
        <?php _csrf_field(); ?>
        <?php if (isset($project->project_id) && $project->project_id): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="form-group">
            <label for="project_name">{{ trans('project_name') }} *</label>
            <input type="text"
                   name="project_name"
                   id="project_name"
                   class="form-control"
                   value="<?php echo htmlspecialchars($project->project_name ?? ''); ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="client_id">{{ trans('client') }} *</label>
            <select name="client_id" id="client_id" class="form-control" required>
                <option value="">{{ trans('select_client') }}</option>
                <?php if (isset($clients)): ?>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client->client_id; ?>"
                                <?php echo (isset($project->client_id) && $project->client_id == $client->client_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($client->client_name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> {{ trans('save') }}
            </button>
            <a href="<?php echo route('projects.index'); ?>" class="btn btn-default">
                <i class="fa fa-times"></i> {{ trans('cancel') }}
            </a>
        </div>
    </form>
</div>
