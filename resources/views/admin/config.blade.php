@extends('admin.main')

@section('title', 'Virtual Tour | La nueva era digital')

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert-dismiss">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>{{ $message }}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span class="fa fa-times"></span>
                </button>
            </div>
        </div>
    @endif

    {{-- Panel Spin 360 --}}
    @php
        $spinReady = false;
        $spinActiveVal = false;
        $spinAutoVal = true;
        try {
            if (isset($property) && $property) {
                $sceneWithSpin = $property->scenes()->whereNotNull('spin_id')->first();
                if ($sceneWithSpin && $sceneWithSpin->spin && $sceneWithSpin->spin->status === 'ready') {
                    $spinReady = true;
                    $spinActiveVal = (bool) data_get($property, 'spin_active', false);
                    $spinAutoVal = (bool) data_get($property, 'spin_auto_rotate', true);
                }
            }
        } catch (\Throwable $e) {
            $spinReady = false;
        }
    @endphp
    @if($spinReady)
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="card" style="border-left: 4px solid #007bff;">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <h6 class="mb-1"><i class="fa fa-refresh mr-2"></i>Spin 360 en Landing Page</h6>
                                <small class="text-muted">Configura cómo se muestra el spin en las tarjetas de publicaciones</small>
                            </div>
                            <div class="d-flex align-items-center" style="gap: 20px;">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="spin_active"
                                        {{ $spinActiveVal ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="spin_active">Spin activo</label>
                                </div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="spin_auto_rotate"
                                        {{ $spinAutoVal ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="spin_auto_rotate">Auto-rotate</label>
                                </div>
                                <span id="spinSettingsStatus" class="text-success" style="font-size: 12px; display: none;">
                                    <i class="fa fa-check"></i> Guardado
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12 mt-sm-30 mt-xs-30">
            <div class="card">
                <div class="card-body">
                    <!-- Tab -->
                    <div class="d-flex justify-content-center">
                        <div class="trd-history-tabs">
                            <ul class="nav" role="tablist" id="TabMenu">
                                <li><a class="active" data-toggle="tab" href="#scene" role="tab">Escena</a></li>
                                <li><a data-toggle="tab" href="#hotspot" role="tab">Hotspot</a></li>
                                <li><a data-toggle="tab" href="#polygon" role="tab">Marcadores</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="trad-history mt-4">
                        <div class="tab-content" id="myTabContent">
                            <!-- Scene Tab -->
                            <div class="tab-pane fade show active" id="scene" role="tabpanel">
                                @include('admin.dataScene')
                            </div>

                            <!-- Hotspot Tab -->
                            <div class="tab-pane fade" id="hotspot" role="tabpanel">
                                @include('admin.dataHotspot')
                            </div>

                            <!-- Polygon Tab -->
                            <div class="tab-pane fade" id="polygon" role="tabpanel">
                                @include('admin.dataPolygon')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        function previewImage() {
            var oFReader = new FileReader();
            oFReader.readAsDataURL(document.getElementById("image-upload").files[0]);

            oFReader.onload = function(oFREvent) {
                document.getElementById("image-preview").src = oFREvent.target.result;
            };
        };
    </script>

    <script>
        (function($, DataTable) {
            $.extend(true, DataTable.defaults, {
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 20, -1],
                    [5, 10, 20, 'Todos']
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.10/i18n/Spanish.json'
                }
            });
        })(jQuery, jQuery.fn.dataTable);
    </script>

    <script>
        $(document).ready(function() {
            var propertyId = $('input[name="property_id"]').val();
            var itemType = $('input[name="item_type"]').val() || 'property';
            var table = $('.sceneTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('dataScene') }}",
                    type: 'GET',
                    data: {
                        property_id: propertyId,
                        type: itemType
                    }
                },
                columns: [{
                        data: null,
                        searchable: false,
                        sortable: false,
                        "render": function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'image_ref',
                        render: function(data, type, full, meta) {
                            let fileBase = "{{ route('file', '') }}/";
                            // Solo mostrar miniatura (image_ref); si no existe, placeholder CSS
                            let src = data ? fileBase + data : null;
                            if (src) {
                                return `<img style="height:70px;width:90px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;" src="${src}" />`;
                            }
                            // Placeholder CSS con iniciales y título cuando no hay imagen
                            let title  = full.title || 'Escena';
                            let initials = title.substring(0, 2).toUpperCase();
                            let short    = title.length > 12 ? title.substring(0, 12) + '…' : title;
                            return `<div style="height:70px;width:90px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:4px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;gap:3px;">
                                        <span style="font-size:22px;font-weight:700;line-height:1;">${initials}</span>
                                        <span style="font-size:9px;text-align:center;padding:0 4px;opacity:.85;word-break:break-word;">${short}</span>
                                    </div>`;
                        },
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'demo',
                        name: 'demo',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                'order': []
            });

            $('.sceneTable tbody').on('click', 'input:checkbox', function() {
                var getId = $(this).attr("id");
                var checkedCount = $('.sceneTable tbody input[type=checkbox]:checked').length;
                if (checkedCount > 1) {
                    $(this).prop('checked', false)
                    alert('Solo se permite una escena principal')
                } else {
                    $(this).find('input[name="check"]:not(:checked)').prop('checked', true).val(0);
                    $("#status" + getId).submit();
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            var propertyId = $('input[name="property_id"]').val();
            var itemType = $('input[name="item_type"]').val() || 'property';
            var hotspotTable = $('.hotspotTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('dataHotspot') }}",
                    type: 'GET',
                    data: {
                        property_id: propertyId,
                        type: itemType
                    }
                },
                columns: [{
                        data: 'image',
                        render: function(data, type, full, meta) {
                            const routeBase = "{{ route('file', '') }}/";
                            if (data) {
                                return `<img class='circular' src='${routeBase}${data}' />`;
                            } else {
                                return `<img class='circular' src='{{ url("virtualtour/images/hotspot.png") }}' />`;
                            }
                        },
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'sourceSceneName',
                        name: 'sc1.title'
                    },
                    {
                        data: 'targetSceneName',
                        name: 'sc2.title'
                    },
                    {
                        data: 'type',
                        name: 'hotspots.type'
                    },
                    {
                        data: 'info',
                        name: 'hotspots.info'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                'order': []
            });
        });
    </script>

    {{-- Spin settings AJAX --}}
    <script>
        $(document).ready(function() {
            $('#spin_active, #spin_auto_rotate').on('change', function() {
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val();
                $.ajax({
                    url: '{{ route("updateSpinSettings") }}',
                    type: 'POST',
                    data: {
                        property_id: '{{ $id }}',
                        spin_active: $('#spin_active').is(':checked') ? 1 : 0,
                        spin_auto_rotate: $('#spin_auto_rotate').is(':checked') ? 1 : 0,
                        _token: csrfToken
                    },
                    success: function() {
                        $('#spinSettingsStatus').fadeIn().delay(2000).fadeOut();
                    }
                });
            });
        });
    </script>
@endpush
