<div class="row">
    <div class="form-group col-md-6">
        <label>Nombre completo <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required
               value="{{ old('name', $targetUser->name ?? '') }}">
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label>Usuario (login) <span class="text-danger">*</span></label>
        <input type="text" name="username" class="form-control" required
               value="{{ old('username', $targetUser->username ?? '') }}">
        @error('username') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
</div>

<div class="row">
    <div class="form-group col-md-6">
        <label>Correo electrónico <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" required
               value="{{ old('email', $targetUser->email ?? '') }}">
        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label>Teléfono</label>
        <input type="text" name="phone" class="form-control"
               value="{{ old('phone', $targetUser->phone ?? '') }}" placeholder="+506 8888-8888">
        @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
</div>

<div class="row">
    <div class="form-group col-md-6">
        <label>Contraseña @if(!isset($targetUser))<span class="text-danger">*</span>@else<small class="text-muted">(dejar vacío para mantener)</small>@endif</label>
        <input type="password" name="password" class="form-control" {{ !isset($targetUser) ? 'required' : '' }}>
        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label>Confirmar contraseña</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>
</div>

<div class="row">
    <div class="form-group col-md-4">
        <label>Rol <span class="text-danger">*</span></label>
        <select name="role" class="form-control" required>
            @foreach($roles as $value => $label)
                <option value="{{ $value }}" {{ old('role', $targetUser->role ?? '') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('role') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    @if(Auth::user()->isSuperAdmin() && isset($companies) && $companies->count())
        <div class="form-group col-md-4">
            <label>Empresa <span class="text-danger">*</span></label>
            <select name="company_id" class="form-control" required>
                <option value="">Seleccionar empresa...</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ old('company_id', $targetUser->company_id ?? '') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
            @error('company_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
    @endif

    @if(isset($targetUser))
        <div class="form-group col-md-4">
            <label>Estado <span class="text-danger">*</span></label>
            <select name="status" class="form-control" required>
                <option value="active" {{ old('status', $targetUser->status) == 'active' ? 'selected' : '' }}>Activo</option>
                <option value="inactive" {{ old('status', $targetUser->status) == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                <option value="suspended" {{ old('status', $targetUser->status) == 'suspended' ? 'selected' : '' }}>Suspendido</option>
            </select>
            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
    @endif
</div>

<div class="alert alert-info small mt-3">
    <i class="fa fa-info-circle"></i>
    <strong>Roles:</strong><br>
    <ul class="mb-0 pl-3">
        @if(Auth::user()->isSuperAdmin())
            <li><strong>Super Administrador:</strong> Acceso total al sistema, gestión de empresas y paquetes.</li>
        @endif
        <li><strong>Administrador de Empresa:</strong> Gestiona usuarios, categorías y propiedades de su empresa.</li>
        <li><strong>Agente:</strong> Puede crear propiedades y participar en la bolsa inmobiliaria.</li>
    </ul>
</div>
