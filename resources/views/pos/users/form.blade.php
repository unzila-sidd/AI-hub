<div class="form-group">
    <label>Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
</div>
<div class="form-group">
    <label>Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
</div>
<div class="form-group">
    <label>Role</label>
    <select name="role_id" class="form-control">
        <option value="">No role</option>
        @foreach ($roles as $role)
            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>Password{{ isset($user) ? ' (leave blank to keep current)' : '' }}</label>
    <input type="password" name="password" class="form-control">
    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
</div>
<div class="form-group">
    <label>Confirm Password</label>
    <input type="password" name="password_confirmation" class="form-control">
</div>