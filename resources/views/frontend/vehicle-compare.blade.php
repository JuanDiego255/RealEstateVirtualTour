@extends('frontend.front')

@push('styles')
<style>
        /* ===== COMPARADOR DE VEHICULOS - ESTILOS ===== */
        :root {
            --vc-primary: #c2ac1f;
            --vc-primary-dark: #9a8a19;
            --vc-success: #28a745;
            --vc-danger: #dc3545;
            --vc-warning: #ffc107;
            --vc-info: #17a2b8;
            --vc-dark: #343a40;
            --vc-light: #f8f9fa;
            --vc-border: #e0e0e0;
            --vc-shadow: 0 4px 20px rgba(0,0,0,0.08);
            --vc-shadow-lg: 0 8px 40px rgba(0,0,0,0.12);
            --vc-radius: 12px;
            --vc-radius-lg: 16px;
        }

        .vc-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
            padding-bottom: 60px;
            margin-top: 0;
        }

        .vc-hero {
            background: linear-gradient(135deg, var(--vc-dark) 0%, #1a1a2e 100%);
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .vc-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .vc-hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: #fff;
        }

        .vc-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #fff;
        }

        .vc-hero h1 i {
            color: var(--vc-primary);
            margin-right: 15px;
        }

        .vc-hero p {
            font-size: 1.1rem;
            opacity: 0.85;
            max-width: 600px;
            margin: 0 auto;
            color: #fff;
        }

        /* Selectores */
        .vc-selectors {
            background: #fff;
            border-radius: var(--vc-radius-lg);
            box-shadow: var(--vc-shadow-lg);
            padding: 30px;
            margin-top: -40px;
            position: relative;
            z-index: 10;
        }

        .vc-selector-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
        }

        .vc-selector-item {
            flex: 1;
            min-width: 200px;
        }

        .vc-selector-item label {
            display: block;
            font-weight: 600;
            color: var(--vc-dark);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .vc-selector-item label i {
            color: var(--vc-primary);
            margin-right: 6px;
        }

        .vc-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--vc-border);
            border-radius: var(--vc-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
            cursor: pointer;
        }

        .vc-select:focus {
            outline: none;
            border-color: var(--vc-primary);
            box-shadow: 0 0 0 4px rgba(194, 172, 31, 0.15);
        }

        .vc-select:disabled {
            background: var(--vc-light);
            cursor: not-allowed;
        }

        /* Slots de vehículos */
        .vc-slots-container {
            margin-top: 40px;
        }

        .vc-slots-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .vc-slots-header h3 {
            font-weight: 700;
            color: var(--vc-dark);
            margin: 0;
        }

        .vc-slots-header h3 i {
            color: var(--vc-primary);
            margin-right: 10px;
        }

        .vc-slots-actions {
            display: flex;
            gap: 10px;
        }

        .vc-btn {
            padding: 10px 20px;
            border-radius: var(--vc-radius);
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .vc-btn-primary {
            background: var(--vc-primary);
            color: #fff;
        }

        .vc-btn-primary:hover {
            background: var(--vc-primary-dark);
            transform: translateY(-2px);
        }

        .vc-btn-outline {
            background: transparent;
            border: 2px solid var(--vc-border);
            color: var(--vc-dark);
        }

        .vc-btn-outline:hover {
            border-color: var(--vc-primary);
            color: var(--vc-primary);
        }

        .vc-btn-danger {
            background: var(--vc-danger);
            color: #fff;
        }

        .vc-btn-danger:hover {
            background: #c82333;
        }

        .vc-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Slots */
        .vc-slots {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .vc-slot {
            background: #fff;
            border-radius: var(--vc-radius-lg);
            box-shadow: var(--vc-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .vc-slot:hover {
            transform: translateY(-4px);
            box-shadow: var(--vc-shadow-lg);
        }

        .vc-slot-empty {
            border: 3px dashed var(--vc-border);
            background: var(--vc-light);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 320px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .vc-slot-empty:hover {
            border-color: var(--vc-primary);
            background: rgba(194, 172, 31, 0.05);
        }

        .vc-slot-empty i {
            font-size: 3rem;
            color: var(--vc-border);
            margin-bottom: 15px;
            transition: color 0.3s ease;
        }

        .vc-slot-empty:hover i {
            color: var(--vc-primary);
        }

        .vc-slot-empty span {
            font-weight: 600;
            color: #999;
        }

        .vc-slot-number {
            position: absolute;
            top: 12px;
            left: 12px;
            width: 32px;
            height: 32px;
            background: var(--vc-primary);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            z-index: 5;
        }

        .vc-slot-remove {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            background: rgba(220, 53, 69, 0.9);
            color: #fff;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 5;
        }

        .vc-slot-remove:hover {
            background: var(--vc-danger);
            transform: scale(1.1);
        }

        .vc-slot-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: var(--vc-light);
        }

        .vc-slot-body {
            padding: 20px;
        }

        .vc-slot-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--vc-dark);
            margin-bottom: 5px;
        }

        .vc-slot-subtitle {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 12px;
        }

        .vc-slot-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--vc-primary);
        }

        .vc-slot-condition {
            display: inline-block;
            padding: 4px 12px;
            background: var(--vc-light);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #666;
            margin-left: 10px;
        }

        .vc-slot-tour-btn {
            display: block;
            width: 100%;
            margin-top: 15px;
            padding: 10px;
            background: linear-gradient(135deg, var(--vc-info) 0%, #138496 100%);
            color: #fff;
            border: none;
            border-radius: var(--vc-radius);
            font-weight: 600;
            font-size: 0.9rem;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .vc-slot-tour-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
            color: #fff;
            text-decoration: none;
        }

        /* Tabla comparativa */
        .vc-comparison-section {
            margin-top: 50px;
        }

        .vc-comparison-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .vc-comparison-header h2 {
            font-weight: 700;
            color: var(--vc-dark);
        }

        .vc-comparison-header h2 i {
            color: var(--vc-primary);
            margin-right: 10px;
        }

        .vc-comparison-table-wrapper {
            background: #fff;
            border-radius: var(--vc-radius-lg);
            box-shadow: var(--vc-shadow-lg);
            overflow: hidden;
        }

        .vc-comparison-table {
            width: 100%;
            border-collapse: collapse;
        }

        .vc-comparison-table th,
        .vc-comparison-table td {
            padding: 16px 20px;
            text-align: center;
            border-bottom: 1px solid var(--vc-border);
        }

        .vc-comparison-table th {
            background: var(--vc-dark);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .vc-comparison-table th:first-child {
            text-align: left;
            background: #2d3339;
        }

        .vc-comparison-table td:first-child {
            text-align: left;
            font-weight: 600;
            color: var(--vc-dark);
            background: var(--vc-light);
        }

        .vc-comparison-table td:first-child i {
            color: var(--vc-primary);
            margin-right: 8px;
            width: 20px;
        }

        .vc-comparison-table tr:last-child td {
            border-bottom: none;
        }

        .vc-comparison-table tr:hover td {
            background: rgba(194, 172, 31, 0.03);
        }

        .vc-comparison-table tr:hover td:first-child {
            background: #f0f0f0;
        }

        .vc-value-best {
            background: rgba(40, 167, 69, 0.1) !important;
            color: var(--vc-success);
            font-weight: 700;
            position: relative;
        }

        .vc-value-best::after {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            font-size: 0.7rem;
            margin-left: 6px;
        }

        .vc-value-worst {
            background: rgba(220, 53, 69, 0.05) !important;
            color: #999;
        }

        /* Modal de selección de vehículo */
        .vc-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .vc-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .vc-modal {
            background: #fff;
            border-radius: var(--vc-radius-lg);
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            overflow: hidden;
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s ease;
        }

        .vc-modal-overlay.active .vc-modal {
            transform: scale(1) translateY(0);
        }

        .vc-modal-header {
            background: var(--vc-dark);
            color: #fff;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .vc-modal-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .vc-modal-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .vc-modal-close:hover {
            opacity: 1;
        }

        .vc-modal-body {
            padding: 20px 25px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .vc-modal-search {
            margin-bottom: 20px;
        }

        .vc-modal-search input {
            width: 100%;
            padding: 14px 20px;
            border: 2px solid var(--vc-border);
            border-radius: var(--vc-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .vc-modal-search input:focus {
            outline: none;
            border-color: var(--vc-primary);
        }

        .vc-vehicle-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .vc-vehicle-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid var(--vc-border);
            border-radius: var(--vc-radius);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .vc-vehicle-item:hover {
            border-color: var(--vc-primary);
            background: rgba(194, 172, 31, 0.05);
        }

        .vc-vehicle-item img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }

        .vc-vehicle-item-info {
            flex: 1;
        }

        .vc-vehicle-item-name {
            font-weight: 600;
            color: var(--vc-dark);
            margin-bottom: 4px;
        }

        .vc-vehicle-item-price {
            color: var(--vc-primary);
            font-weight: 700;
        }

        .vc-vehicle-item-badge {
            padding: 4px 10px;
            background: var(--vc-light);
            border-radius: 20px;
            font-size: 0.75rem;
            color: #666;
        }

        .vc-loading {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .vc-loading i {
            font-size: 2rem;
            margin-bottom: 10px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .vc-empty {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .vc-empty i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Export actions */
        .vc-export-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .vc-hero h1 {
                font-size: 1.8rem;
            }

            .vc-selector-group {
                flex-direction: column;
            }

            .vc-selector-item {
                width: 100%;
            }

            .vc-slots {
                grid-template-columns: 1fr;
            }

            .vc-comparison-table-wrapper {
                overflow-x: auto;
            }

            .vc-comparison-table {
                min-width: 600px;
            }

            .vc-slots-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .vc-export-actions {
                flex-direction: column;
            }

            .vc-export-actions .vc-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Print styles */
        @media print {
            .vc-hero,
            .vc-selectors,
            .vc-slots-container,
            .vc-export-actions,
            .vc-slot-remove,
            .vc-slot-tour-btn,
            .navbar,
            footer {
                display: none !important;
            }

            .vc-comparison-section {
                margin-top: 0;
            }

            .vc-page {
                background: #fff;
                padding: 0;
            }
        }

        /* Animaciones */
        .vc-fade-in {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')
    <div class="vc-page" id="vehicleCompareApp">
        <!-- Hero Section -->
        <div class="vc-hero">
            <div class="container vc-hero-content">
                <h1><i class="fa fa-balance-scale"></i> Comparador de Vehículos</h1>
                <p>Compara hasta 4 vehículos lado a lado. Analiza especificaciones, precios y encuentra el auto perfecto para ti.</p>
            </div>
        </div>

        <div class="container">
            <!-- Selectores -->
            <div class="vc-selectors vc-fade-in">
                <div class="vc-selector-group">
                    <div class="vc-selector-item">
                        <label><i class="fa fa-building"></i> Sucursal</label>
                        <select class="vc-select" id="categorySelect" v-model="selectedCategory" @change="onCategoryChange">
                            <option value="">-- Selecciona una sucursal --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="vc-selector-item">
                        <label><i class="fa fa-tags"></i> Subcategoría (opcional)</label>
                        <select class="vc-select" id="subcategorySelect" v-model="selectedSubcategory" @change="loadVehicles" :disabled="!selectedCategory || subcategories.length === 0">
                            <option value="">-- Todas las subcategorías --</option>
                            <option v-for="sub in subcategories" :key="sub.id" :value="sub.id">@{{ sub.name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Slots de Vehículos -->
            <div class="vc-slots-container" v-if="selectedCategory">
                <div class="vc-slots-header">
                    <h3><i class="fa fa-car"></i> Vehículos a Comparar (@{{ selectedVehicles.length }}/4)</h3>
                    <div class="vc-slots-actions">
                        <button class="vc-btn vc-btn-outline" @click="clearAllVehicles" :disabled="selectedVehicles.length === 0">
                            <i class="fa fa-trash"></i> Limpiar Todo
                        </button>
                    </div>
                </div>

                <div class="vc-slots">
                    <!-- Slots existentes -->
                    <div v-for="(vehicle, index) in selectedVehicles" :key="vehicle.id" class="vc-slot vc-fade-in">
                        <span class="vc-slot-number">@{{ index + 1 }}</span>
                        <button class="vc-slot-remove" @click="removeVehicle(index)" title="Quitar vehículo">
                            <i class="fa fa-times"></i>
                        </button>
                        <img :src="vehicle.image" :alt="vehicle.name" class="vc-slot-image">
                        <div class="vc-slot-body">
                            <div class="vc-slot-title">@{{ vehicle.name }}</div>
                            <div class="vc-slot-subtitle">@{{ vehicle.subcategory || 'Sin categoría' }}</div>
                            <div>
                                <span class="vc-slot-price">@{{ vehicle.price_formatted }}</span>
                                <span class="vc-slot-condition">@{{ vehicle.condition_label }}</span>
                            </div>
                            <a v-if="vehicle.has_virtual_tour" :href="vehicle.virtual_tour_url" target="_blank" class="vc-slot-tour-btn">
                                <i class="fa fa-eye"></i> Ver Tour Virtual 360°
                            </a>
                        </div>
                    </div>

                    <!-- Slots vacíos -->
                    <div v-for="n in (4 - selectedVehicles.length)" :key="'empty-' + n" class="vc-slot vc-slot-empty" @click="openVehicleModal">
                        <i class="fa fa-plus-circle"></i>
                        <span>Agregar Vehículo</span>
                    </div>
                </div>
            </div>

            <!-- Mensaje inicial -->
            <div v-else class="vc-empty" style="margin-top: 60px;">
                <i class="fas fa-hand-point-up"></i>
                <h4>Selecciona una sucursal para comenzar</h4>
                <p class="text-muted">Elige una sucursal del sector automotriz para ver sus vehículos disponibles y comenzar a comparar.</p>
            </div>

            <!-- Tabla Comparativa -->
            <div class="vc-comparison-section" v-if="selectedVehicles.length >= 2">
                <div class="vc-comparison-header">
                    <h2><i class="fas fa-chart-bar"></i> Tabla Comparativa</h2>
                </div>

                <div class="vc-comparison-table-wrapper vc-fade-in">
                    <table class="vc-comparison-table">
                        <thead>
                            <tr>
                                <th>Especificación</th>
                                <th v-for="vehicle in selectedVehicles" :key="'th-' + vehicle.id">
                                    @{{ vehicle.brand }} @{{ vehicle.model }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Precio -->
                            <tr>
                                <td><i class="fa fa-tag"></i> Precio</td>
                                <td v-for="vehicle in selectedVehicles" :key="'price-' + vehicle.id"
                                    :class="getCellClass('price', vehicle.id, vehicle.price)">
                                    @{{ vehicle.price_formatted }}
                                </td>
                            </tr>
                            <!-- Año -->
                            <tr>
                                <td><i class="fa fa-calendar-alt"></i> Año</td>
                                <td v-for="vehicle in selectedVehicles" :key="'year-' + vehicle.id"
                                    :class="getCellClass('year', vehicle.id, vehicle.year)">
                                    @{{ vehicle.year }}
                                </td>
                            </tr>
                            <!-- Especificaciones dinámicas -->
                            <tr v-for="spec in specificationKeys" :key="spec.key">
                                <td><i :class="'fas ' + spec.icon"></i> @{{ spec.label }}</td>
                                <td v-for="vehicle in selectedVehicles" :key="spec.key + '-' + vehicle.id"
                                    :class="getCellClass(spec.key, vehicle.id, getSpecValue(vehicle, spec.key))">
                                    @{{ formatSpecValue(vehicle, spec.key) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Acciones de exportación -->
                <div class="vc-export-actions">
                    <button class="vc-btn vc-btn-primary" @click="printComparison">
                        <i class="fa fa-print"></i> Imprimir Comparación
                    </button>
                    <button class="vc-btn vc-btn-outline" @click="shareComparison">
                        <i class="fa fa-share-alt"></i> Compartir
                    </button>
                    <button class="vc-btn vc-btn-outline" @click="downloadComparison">
                        <i class="fa fa-download"></i> Descargar PDF
                    </button>
                </div>
            </div>

            <!-- Mensaje para agregar más vehículos -->
            <div v-else-if="selectedVehicles.length === 1" class="vc-empty" style="margin-top: 40px;">
                <i class="fa fa-info-circle"></i>
                <h5>Agrega al menos un vehículo más</h5>
                <p class="text-muted">Necesitas seleccionar al menos 2 vehículos para ver la tabla comparativa.</p>
            </div>
        </div>

        <!-- Modal de selección de vehículo -->
        <div class="vc-modal-overlay" :class="{ active: showModal }" @click.self="closeModal">
            <div class="vc-modal">
                <div class="vc-modal-header">
                    <h4><i class="fa fa-car"></i> Seleccionar Vehículo</h4>
                    <button class="vc-modal-close" @click="closeModal">&times;</button>
                </div>
                <div class="vc-modal-body">
                    <div class="vc-modal-search">
                        <input type="text" v-model="vehicleSearch" placeholder="Buscar por marca, modelo o año..." @input="filterVehicles">
                    </div>

                    <div v-if="loadingVehicles" class="vc-loading">
                        <i class="fa fa-spinner"></i>
                        <p>Cargando vehículos...</p>
                    </div>

                    <div v-else-if="filteredVehicles.length === 0" class="vc-empty">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>No se encontraron vehículos disponibles</p>
                    </div>

                    <div v-else class="vc-vehicle-list">
                        <div v-for="vehicle in filteredVehicles" :key="vehicle.id" class="vc-vehicle-item" @click="selectVehicle(vehicle)">
                            <img :src="vehicle.image" :alt="vehicle.name">
                            <div class="vc-vehicle-item-info">
                                <div class="vc-vehicle-item-name">@{{ vehicle.name }}</div>
                                <div class="vc-vehicle-item-price">@{{ vehicle.price_formatted }}</div>
                            </div>
                            <span class="vc-vehicle-item-badge">@{{ vehicle.condition_label }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Volver -->
    <div class="container mt-4 mb-5 text-center">
        <a href="{{ route('sector.show', $sector->slug) }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left mr-2"></i> Volver al Sector Automotriz
        </a>
    </div>

    @include('frontend.footer')

    <script src="{{ asset('virtualtour/js/jquery.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/popper.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>

    <script>
        new Vue({
            el: '#vehicleCompareApp',
            data: {
                selectedCategory: '',
                selectedSubcategory: '',
                subcategories: [],
                vehicles: [],
                selectedVehicles: [],
                showModal: false,
                vehicleSearch: '',
                loadingVehicles: false,
                comparison: {},
                specificationKeys: [
                    { key: 'mileage_km', label: 'Kilometraje', icon: 'fa-tachometer-alt', unit: 'km', comparison: 'lower_better' },
                    { key: 'engine_cc', label: 'Motor', icon: 'fa-cog', unit: 'cc', comparison: 'neutral' },
                    { key: 'fuel_tank_capacity', label: 'Tanque', icon: 'fa-gas-pump', unit: 'L', comparison: 'higher_better' },
                    { key: 'fuel_type', label: 'Combustible', icon: 'fa-fire', unit: '', comparison: 'neutral' },
                    { key: 'doors', label: 'Puertas', icon: 'fa-door-open', unit: '', comparison: 'neutral' },
                    { key: 'passengers', label: 'Pasajeros', icon: 'fa-users', unit: '', comparison: 'higher_better' },
                    { key: 'transmission', label: 'Transmisión', icon: 'fa-cogs', unit: '', comparison: 'neutral' },
                    { key: 'drivetrain', label: 'Tracción', icon: 'fa-car', unit: '', comparison: 'neutral' },
                    { key: 'condition', label: 'Condición', icon: 'fa-star', unit: '', comparison: 'neutral' },
                ]
            },
            computed: {
                filteredVehicles() {
                    if (!this.vehicleSearch) {
                        return this.vehicles.filter(v => !this.selectedVehicles.find(sv => sv.id === v.id));
                    }
                    const search = this.vehicleSearch.toLowerCase();
                    return this.vehicles.filter(v =>
                        !this.selectedVehicles.find(sv => sv.id === v.id) &&
                        (v.name.toLowerCase().includes(search) ||
                         v.brand.toLowerCase().includes(search) ||
                         v.model.toLowerCase().includes(search) ||
                         String(v.year).includes(search))
                    );
                }
            },
            methods: {
                onCategoryChange() {
                    this.selectedSubcategory = '';
                    this.subcategories = [];
                    this.vehicles = [];
                    this.selectedVehicles = [];

                    if (this.selectedCategory) {
                        this.loadSubcategories();
                        this.loadVehicles();
                    }
                },

                loadSubcategories() {
                    fetch(`/api/vehicle-compare/subcategories/${this.selectedCategory}`)
                        .then(res => res.json())
                        .then(data => {
                            this.subcategories = data;
                        });
                },

                loadVehicles() {
                    this.loadingVehicles = true;
                    let url = `/api/vehicle-compare/vehicles?category_id=${this.selectedCategory}`;
                    if (this.selectedSubcategory) {
                        url += `&subcategory_id=${this.selectedSubcategory}`;
                    }

                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            this.vehicles = data;
                            this.loadingVehicles = false;
                        });
                },

                openVehicleModal() {
                    if (this.selectedVehicles.length >= 4) {
                        alert('Solo puedes comparar hasta 4 vehículos.');
                        return;
                    }
                    this.vehicleSearch = '';
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                },

                selectVehicle(vehicle) {
                    // Obtener detalles completos del vehículo
                    fetch(`/api/vehicle-compare/vehicle/${vehicle.id}`)
                        .then(res => res.json())
                        .then(data => {
                            this.selectedVehicles.push(data);
                            this.closeModal();
                            this.updateComparison();
                        });
                },

                removeVehicle(index) {
                    this.selectedVehicles.splice(index, 1);
                    this.updateComparison();
                },

                clearAllVehicles() {
                    if (confirm('¿Estás seguro de quitar todos los vehículos?')) {
                        this.selectedVehicles = [];
                        this.comparison = {};
                    }
                },

                updateComparison() {
                    if (this.selectedVehicles.length < 2) {
                        this.comparison = {};
                        return;
                    }

                    // Calcular comparaciones localmente
                    this.comparison = {};
                    const specs = ['price', 'year', 'mileage_km', 'fuel_tank_capacity', 'passengers'];
                    const compTypes = {
                        price: 'lower_better',
                        year: 'higher_better',
                        mileage_km: 'lower_better',
                        fuel_tank_capacity: 'higher_better',
                        passengers: 'higher_better'
                    };

                    specs.forEach(spec => {
                        const values = {};
                        this.selectedVehicles.forEach(v => {
                            let val = spec === 'price' || spec === 'year' ? v[spec] : this.getSpecValue(v, spec);
                            if (val !== null && val !== '' && !isNaN(parseFloat(val))) {
                                values[v.id] = parseFloat(val);
                            }
                        });

                        if (Object.keys(values).length >= 2) {
                            const vals = Object.values(values);
                            const min = Math.min(...vals);
                            const max = Math.max(...vals);

                            if (compTypes[spec] === 'lower_better') {
                                this.comparison[spec] = {
                                    best: Object.keys(values).find(k => values[k] === min),
                                    worst: Object.keys(values).find(k => values[k] === max)
                                };
                            } else if (compTypes[spec] === 'higher_better') {
                                this.comparison[spec] = {
                                    best: Object.keys(values).find(k => values[k] === max),
                                    worst: Object.keys(values).find(k => values[k] === min)
                                };
                            }
                        }
                    });
                },

                getSpecValue(vehicle, key) {
                    if (vehicle.specs && vehicle.specs[key]) {
                        return vehicle.specs[key].value;
                    }
                    return vehicle[key] || null;
                },

                formatSpecValue(vehicle, key) {
                    const spec = this.specificationKeys.find(s => s.key === key);
                    let value = this.getSpecValue(vehicle, key);

                    if (value === null || value === '' || value === undefined) {
                        return 'N/A';
                    }

                    // Para condición, mostrar display_value
                    if (key === 'condition' && vehicle.specs && vehicle.specs[key] && vehicle.specs[key].display_value) {
                        return vehicle.specs[key].display_value;
                    }

                    if (spec && spec.unit && typeof value === 'number') {
                        return value.toLocaleString() + ' ' + spec.unit;
                    }

                    return value;
                },

                getCellClass(key, vehicleId, value) {
                    if (!this.comparison[key] || value === null || value === '') {
                        return '';
                    }

                    if (String(this.comparison[key].best) === String(vehicleId)) {
                        return 'vc-value-best';
                    }
                    if (String(this.comparison[key].worst) === String(vehicleId)) {
                        return 'vc-value-worst';
                    }
                    return '';
                },

                printComparison() {
                    window.print();
                },

                shareComparison() {
                    const ids = this.selectedVehicles.map(v => v.id).join(',');
                    const url = `${window.location.origin}/comparar-vehiculos?ids=${ids}&cat=${this.selectedCategory}`;

                    if (navigator.share) {
                        navigator.share({
                            title: 'Comparación de Vehículos',
                            text: 'Mira esta comparación de vehículos',
                            url: url
                        });
                    } else {
                        // Copiar al portapapeles
                        navigator.clipboard.writeText(url).then(() => {
                            alert('Enlace copiado al portapapeles');
                        });
                    }
                },

                downloadComparison() {
                    const ids = this.selectedVehicles.map(v => v.id);
                    window.open(`/api/vehicle-compare/export-pdf?vehicle_ids=${ids.join(',')}`, '_blank');
                },

                filterVehicles() {
                    // El filtrado se hace en el computed property
                }
            },
            mounted() {
                // Verificar si hay parámetros en la URL para precargar
                const urlParams = new URLSearchParams(window.location.search);
                const categoryId = urlParams.get('cat');
                const vehicleIds = urlParams.get('ids');

                if (categoryId) {
                    this.selectedCategory = categoryId;
                    this.loadSubcategories();
                    this.loadVehicles();

                    if (vehicleIds) {
                        const ids = vehicleIds.split(',');
                        ids.forEach(id => {
                            fetch(`/api/vehicle-compare/vehicle/${id}`)
                                .then(res => res.json())
                                .then(data => {
                                    if (!this.selectedVehicles.find(v => v.id === data.id)) {
                                        this.selectedVehicles.push(data);
                                        this.updateComparison();
                                    }
                                });
                        });
                    }
                }
            }
        });
    </script>
@endsection
