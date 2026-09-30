<div class="form-group">
    <label>Role Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $role->name ?? '') }}" required>
    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
</div>
<div class="form-group">
    <label>Description</label>
    <input type="text" name="description" class="form-control" value="{{ old('description', $role->description ?? '') }}">
</div>
<hr>
<h5>Permissions</h5>
<p class="text-muted">Each action is a separate setting that can be granted or revoked independently (e.g. Payments are split into <code>Add</code>, <code>Edit</code> and <code>Delete</code>).</p>

@foreach ($permissions as $group => $perms)
    <div class="mb-3">
        <h6 class="text-primary text-uppercase small">{{ ucfirst($group) }}</h6>
        <div class="row">
            @foreach ($perms as $permission)
                <div class="col-md-6">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="perm-{{ $permission->id }}"
                               name="permissions[]"
                               value="{{ $permission->id }}"
                               @if((old('permissions') !== null && in_array($permission->id, old('permissions'))) || (old('permissions') === null && isset($role) && $role->permissions->contains('id', $permission->id))) checked @endif>
                        <label class="custom-control-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach