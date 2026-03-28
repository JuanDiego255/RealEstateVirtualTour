@extends('admin.main')
@section('title', 'Agenda - Calendario')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-calendar"></i> Agenda</h4>
            <div>
                <a href="{{ route('admin.crm.appointments.today') }}" class="btn btn-info"><i class="fa fa-sun-o"></i> Hoy</a>
                <a href="{{ route('admin.crm.appointments.index', ['view' => 'list']) }}" class="btn btn-secondary"><i class="fa fa-list"></i> Lista</a>
                <a href="{{ route('admin.crm.appointments.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nueva Cita</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    {{-- Modal para ver cita --}}
    <div class="modal fade" id="appointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="appointmentDetails"></div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="appointmentEditLink" class="btn btn-primary"><i class="fa fa-edit"></i> Editar</a>
                    <a href="#" id="appointmentViewLink" class="btn btn-info"><i class="fa fa-eye"></i> Ver Detalles</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales/es.js"></script>

<style>
    #calendar {
        min-height: 600px;
    }
    .fc-event {
        cursor: pointer;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
        },
        navLinks: true,
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,

        events: function(info, successCallback, failureCallback) {
            fetch('{{ route("admin.crm.appointments.calendar-events") }}?start=' + info.startStr + '&end=' + info.endStr)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },

        select: function(info) {
            // Ir a crear cita con fecha seleccionada
            var date = info.startStr.split('T')[0];
            var time = info.startStr.includes('T') ? info.startStr.split('T')[1].substring(0,5) : '09:00';
            window.location.href = '{{ route("admin.crm.appointments.create") }}?date=' + date + '&time=' + time;
        },

        eventClick: function(info) {
            var event = info.event;
            var props = event.extendedProps;

            $('#appointmentTitle').text(event.title);
            $('#appointmentDetails').html(`
                <p><strong>Tipo:</strong> ${getTypeLabel(props.type)}</p>
                <p><strong>Estado:</strong> <span class="badge badge-${getStatusColor(props.status)}">${getStatusLabel(props.status)}</span></p>
                <p><strong>Fecha:</strong> ${formatDate(event.start)}</p>
                <p><strong>Cliente:</strong> ${props.client || '-'}</p>
                ${props.location ? '<p><strong>Ubicación:</strong> ' + props.location + '</p>' : ''}
            `);

            $('#appointmentEditLink').attr('href', '{{ url("admin/crm/appointments") }}/' + event.id + '/edit');
            $('#appointmentViewLink').attr('href', '{{ url("admin/crm/appointments") }}/' + event.id);

            $('#appointmentModal').modal('show');
        },

        eventDrop: function(info) {
            updateEventDates(info.event);
        },

        eventResize: function(info) {
            updateEventDates(info.event);
        }
    });

    calendar.render();

    function updateEventDates(event) {
        fetch('{{ url("admin/crm/appointments") }}/' + event.id + '/update-dates', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                starts_at: event.start.toISOString(),
                ends_at: event.end ? event.end.toISOString() : event.start.toISOString()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                calendar.refetchEvents();
                alert('Error al actualizar la cita');
            }
        })
        .catch(error => {
            calendar.refetchEvents();
            alert('Error al actualizar la cita');
        });
    }

    function formatDate(date) {
        return date.toLocaleDateString('es-CR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function getTypeLabel(type) {
        const types = {
            'property_visit': 'Visita a Propiedad',
            'vehicle_visit': 'Visita a Vehículo',
            'meeting': 'Reunión',
            'call': 'Llamada',
            'video_call': 'Videollamada',
            'signing': 'Firma de Documentos',
            'other': 'Otro'
        };
        return types[type] || type;
    }

    function getStatusLabel(status) {
        const statuses = {
            'scheduled': 'Programada',
            'confirmed': 'Confirmada',
            'in_progress': 'En Progreso',
            'completed': 'Completada',
            'cancelled': 'Cancelada',
            'no_show': 'No Asistió'
        };
        return statuses[status] || status;
    }

    function getStatusColor(status) {
        const colors = {
            'scheduled': 'info',
            'confirmed': 'primary',
            'in_progress': 'warning',
            'completed': 'success',
            'cancelled': 'danger',
            'no_show': 'dark'
        };
        return colors[status] || 'secondary';
    }
});
</script>
@endpush
