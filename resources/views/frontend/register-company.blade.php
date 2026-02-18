@extends('frontend.front')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <body>
        <div class="hero-wrap ftco-degree-bg" style="background-image: url('{{ url('virtualtour/images/bg_1.png') }}'); min-height: 400px;"
            data-stellar-background-ratio="0.5">
            <div class="overlay"></div>
            <div class="container">
                <div class="row no-gutters slider-text justify-content-center align-items-center" style="min-height: 400px;">
                    <div class="col-lg-8 col-md-6 ftco-animate d-flex align-items-end">
                        <div class="text text-center">
                            <h1 class="mb-4">
                                Registre su Empresa
                            </h1>
                            <p style="font-size: 18px">
                                Complete el formulario para crear su cuenta empresarial
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="ftco-section" style="padding-top: 4em;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-10 col-lg-8 ftco-animate">

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa fa-exclamation-circle mr-2"></i> {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('register.company.store') }}" method="POST">
                            @csrf

                            {{-- Paso 1: Selección de Plan --}}
                            <div class="card mb-4" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                <div class="card-header text-white" style="background: linear-gradient(135deg, #343a40, #495057); border-radius: 12px 12px 0 0;">
                                    <h5 class="mb-0"><i class="fa fa-cube mr-2"></i> 1. Seleccione su Plan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach ($packages as $package)
                                            <div class="col-md-{{ count($packages) <= 2 ? '6' : '4' }} mb-3">
                                                <label class="d-block mb-0" style="cursor: pointer;">
                                                    <input type="radio" name="package_id" value="{{ $package->id }}"
                                                        class="d-none package-radio"
                                                        {{ ($selectedPackage && $selectedPackage->id == $package->id) || old('package_id') == $package->id ? 'checked' : '' }}>
                                                    <div class="package-card p-3 text-center"
                                                        style="border: 2px solid #dee2e6; border-radius: 10px; transition: all 0.3s;
                                                               {{ $package->is_featured ? 'border-color: #c2ac1f;' : '' }}">

                                                        @if ($package->is_featured)
                                                            <span class="badge mb-2" style="background: #c2ac1f; color: #fff;">
                                                                <i class="fa fa-star mr-1"></i> Popular
                                                            </span><br>
                                                        @endif

                                                        <h6 class="mb-1 font-weight-bold">{{ $package->name }}</h6>
                                                        <div class="mb-2" style="font-size: 1.3rem; font-weight: 700; color: #c2ac1f;">
                                                            {{ $package->formatted_price }}
                                                            <small class="text-muted" style="font-size: 0.7rem;">/ {{ $package->billing_period_name }}</small>
                                                        </div>
                                                        <small class="text-muted d-block">
                                                            {{ $package->max_users }} usuarios &middot;
                                                            {{ $package->max_categories }} categorías
                                                        </small>
                                                        @if ($package->trial_days > 0)
                                                            <small class="d-block mt-1" style="color: #c2ac1f;">
                                                                <i class="fa fa-gift mr-1"></i> {{ $package->trial_days }} días gratis
                                                            </small>
                                                        @endif
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('package_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Paso 2: Datos de la Empresa --}}
                            <div class="card mb-4" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                <div class="card-header text-white" style="background: linear-gradient(135deg, #343a40, #495057); border-radius: 12px 12px 0 0;">
                                    <h5 class="mb-0"><i class="fa fa-building mr-2"></i> 2. Datos de la Empresa</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="company_name">Nombre de la Empresa <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                                id="company_name" name="company_name" value="{{ old('company_name') }}"
                                                placeholder="Ej: Inmobiliaria CR">
                                            @error('company_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="tax_id">Cédula Jurídica</label>
                                            <input type="text" class="form-control @error('tax_id') is-invalid @enderror"
                                                id="tax_id" name="tax_id" value="{{ old('tax_id') }}"
                                                placeholder="Ej: 3-101-123456">
                                            @error('tax_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="company_phone">Teléfono de la Empresa <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('company_phone') is-invalid @enderror"
                                                id="company_phone" name="company_phone" value="{{ old('company_phone') }}"
                                                placeholder="Ej: 2222-3333">
                                            @error('company_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="company_email">Correo de la Empresa <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('company_email') is-invalid @enderror"
                                                id="company_email" name="company_email" value="{{ old('company_email') }}"
                                                placeholder="Ej: info@inmobiliaria.com">
                                            @error('company_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="company_address">Dirección</label>
                                            <input type="text" class="form-control @error('company_address') is-invalid @enderror"
                                                id="company_address" name="company_address" value="{{ old('company_address') }}"
                                                placeholder="Ej: San José, Costa Rica">
                                            @error('company_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Paso 3: Datos del Administrador --}}
                            <div class="card mb-4" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                <div class="card-header text-white" style="background: linear-gradient(135deg, #343a40, #495057); border-radius: 12px 12px 0 0;">
                                    <h5 class="mb-0"><i class="fa fa-user mr-2"></i> 3. Su Cuenta de Administrador</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name') }}"
                                                placeholder="Ej: Juan Pérez">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone">Teléfono Personal</label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                                id="phone" name="phone" value="{{ old('phone') }}"
                                                placeholder="Ej: 8888-9999">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="email">Correo Electrónico <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                id="email" name="email" value="{{ old('email') }}"
                                                placeholder="Ej: juan@inmobiliaria.com">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="password">Contraseña <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                                id="password" name="password"
                                                placeholder="Mínimo 6 caracteres">
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="password_confirmation">Confirmar Contraseña <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control"
                                                id="password_confirmation" name="password_confirmation"
                                                placeholder="Repita la contraseña">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Botones --}}
                            <div class="text-center mb-5">
                                <button type="submit" class="btn btn-lg px-5 py-3"
                                    style="background: #c2ac1f; color: #fff; border-radius: 30px; font-weight: 600; font-size: 1.1rem;">
                                    <i class="fa fa-check-circle mr-2"></i> Registrar Empresa
                                </button>
                                <p class="text-muted mt-3">
                                    ¿Ya tiene una cuenta?
                                    <a href="{{ route('login') }}" style="color: #c2ac1f;">Ingresar aquí</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- loader -->
        <div id="ftco-loader" class="show fullscreen">
            <svg class="circular" width="48px" height="48px">
                <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
                <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00" />
            </svg>
        </div>
        @include('frontend.footer')
        <script src="{{ asset('virtualtour/js/jquery.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery-migrate-3.0.1.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/popper.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.easing.1.3.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.waypoints.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.stellar.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/owl.carousel.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.magnific-popup.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/aos.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.animateNumber.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/bootstrap-datepicker.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.timepicker.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/scrollax.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/main.js') }}"></script>

        <style>
            .package-radio:checked + .package-card {
                border-color: #c2ac1f !important;
                background: rgba(194, 172, 31, 0.05);
                box-shadow: 0 0 0 3px rgba(194, 172, 31, 0.2);
            }
        </style>
    </body>

    </html>
@endsection
