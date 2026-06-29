# Integración de Tours Virtuales 360 — Guía para el proyecto Real Estate

Esta guía explica cómo el proyecto **Real Estate** (consumidor) consume el API de
**tours virtuales 360** expuesto por el proyecto **RealEstateVirtualTour** (proveedor).

El proveedor expone únicamente tours de **propiedades** (los vehículos no se exponen)
que hayan sido marcadas explícitamente como *disponibles para API externa*.

---

## 1. Autenticación

Todas las peticiones requieren un token enviado en el header:

```
Authorization: Bearer <TOUR_API_TOKEN>
```

(También se acepta `X-API-Token: <TOUR_API_TOKEN>`.)

### Cómo obtener el token

En el servidor del **proveedor**, ejecutar:

```bash
php artisan tour:client "Real Estate"
```

El comando imprime el token **una sola vez**. Cópialo y guárdalo en el `.env`
del proyecto Real Estate:

```env
TOUR_API_URL=https://tours.tudominio.com
TOUR_API_TOKEN=el_token_generado_de_64_caracteres
```

> El token se almacena hasheado (sha256) en la tabla `api_clients` del proveedor.
> Se puede revocar poniendo `is_active = false` en ese registro, o generar otro
> con el mismo comando. El campo `last_used_at` permite auditar el consumo.

---

## 2. Endpoints

Base URL: `{TOUR_API_URL}/api/v1`

### 2.1 Listar tours — `GET /api/v1/tours`

Parámetros de query (todos opcionales):

| Parámetro     | Tipo    | Descripción                                         |
|---------------|---------|-----------------------------------------------------|
| `q`           | string  | Búsqueda por nombre, código, ubicación o dirección  |
| `category_id` | int     | Filtra por categoría                                |
| `sector_id`   | int     | Filtra por sector                                   |
| `per_page`    | int     | Resultados por página (default 12, máx 50)          |
| `page`        | int     | Página actual                                       |

**Ejemplo:**

```bash
curl -H "Authorization: Bearer $TOUR_API_TOKEN" \
  "https://tours.tudominio.com/api/v1/tours?q=casa&per_page=12&page=1"
```

**Respuesta:**

```json
{
  "data": [
    {
      "id": 12,
      "name": "Casa María Residencial",
      "code": "CM-001",
      "type": "house",
      "type_name": "Casa",
      "description": "Residencial frente al mar...",
      "price": 250000,
      "currency": "USD",
      "formatted_price": "$250.000",
      "location": "El Pescadero",
      "address": "Carretera Transpeninsular km 80",
      "latitude": 23.3,
      "longitude": -110.1,
      "main_image": "https://tours.tudominio.com/file/uploads/portada-12.jpg",
      "category": { "id": 3, "name": "Residencial", "slug": "residencial" },
      "scenes_count": 5,
      "tour_url": "https://tours.tudominio.com/virtual-tour/12",
      "share_url": "https://tours.tudominio.com/p/AbC123..."
    }
  ],
  "meta": { "current_page": 1, "last_page": 4, "per_page": 12, "total": 42 }
}
```

### 2.2 Detalle de un tour — `GET /api/v1/tours/{id}`

```bash
curl -H "Authorization: Bearer $TOUR_API_TOKEN" \
  "https://tours.tudominio.com/api/v1/tours/12"
```

**Respuesta:** igual que un item de la lista, más el arreglo `scenes`:

```json
{
  "data": {
    "id": 12,
    "name": "Casa María Residencial",
    "main_image": "https://tours.tudominio.com/file/uploads/portada-12.jpg",
    "tour_url": "https://tours.tudominio.com/virtual-tour/12",
    "scenes_count": 5,
    "scenes": [
      { "id": 101, "title": "Lotes",      "type": "equirectangular", "thumbnail": "https://tours.tudominio.com/file/uploads/sc-101.jpg" },
      { "id": 102, "title": "Vista Norte","type": "equirectangular", "thumbnail": "https://tours.tudominio.com/file/uploads/sc-102.jpg" }
    ]
  }
}
```

### Códigos de error

| Código | Significado                                              |
|--------|---------------------------------------------------------|
| `401`  | Token ausente, inválido o inactivo                      |
| `404`  | Tour inexistente o no marcado como `api_consumable`     |
| `429`  | Límite de tasa (60 req/min)                             |

---

## 3. Campos a crear en el Real Estate (mantenimientos)

En el proyecto Real Estate, agrega un campo para **guardar el enlace del tour**
tanto en **propiedades** como en **proyectos**:

```php
// database/migrations/xxxx_add_virtual_tour_url.php
Schema::table('properties', function (Blueprint $table) {
    $table->string('virtual_tour_url')->nullable()->after('description');
});
Schema::table('projects', function (Blueprint $table) {
    $table->string('virtual_tour_url')->nullable()->after('description');
});
```

En los formularios de mantenimiento agrega el input correspondiente. El valor a
pegar es el `tour_url` que devuelve el API (p. ej. `https://tours.tudominio.com/virtual-tour/12`).

> Recomendado: además de pegar la URL manualmente, puedes ofrecer un selector que
> consuma `GET /api/v1/tours` y permita elegir el tour; al seleccionarlo se guarda
> su `tour_url` automáticamente.

---

## 4. Lectura de imágenes

**No se requiere descargar ni re-alojar imágenes.** El API devuelve **URLs
absolutas** ya listas para usar directamente en el atributo `src`:

```blade
<img src="{{ $tour['main_image'] }}" alt="{{ $tour['name'] }}">
```

Lo mismo aplica a `scenes[].thumbnail`. Requisito: el proveedor debe tener
`APP_URL` configurado con su dominio público (las URLs se generan con `route('file', ...)`).

---

## 5. Grilla de tours `/tours` (consumidor)

Crea una ruta `/tours` que consuma el endpoint de listado con el HTTP client de Laravel:

```php
// routes/web.php
Route::get('/tours', [ToursController::class, 'index'])->name('tours.index');

// app/Http/Controllers/ToursController.php
use Illuminate\Support\Facades\Http;

public function index(Request $request)
{
    $resp = Http::withToken(config('services.tours.token'))
        ->get(rtrim(config('services.tours.url'), '/') . '/api/v1/tours', [
            'q'        => $request->input('q'),
            'page'     => $request->input('page', 1),
            'per_page' => 12,
        ]);

    $payload = $resp->successful() ? $resp->json() : ['data' => [], 'meta' => []];

    return view('tours.index', [
        'tours' => $payload['data'] ?? [],
        'meta'  => $payload['meta'] ?? [],
        'q'     => $request->input('q'),
    ]);
}
```

```php
// config/services.php
'tours' => [
    'url'   => env('TOUR_API_URL'),
    'token' => env('TOUR_API_TOKEN'),
],
```

La vista incluye un **filtro de búsqueda** (input que reenvía `q`) y una **grilla
de cards**. Cada card abre el tour en **una pestaña nueva** mediante `tour_url`.

Ver el snippet completo de la grilla y los estilos en la **Sección 7 (Design System)**.

---

## 6. Card de tour en el detalle (propiedad / proyecto)

Cuando una propiedad o proyecto tenga `virtual_tour_url`, muestra en su página de
detalle (landing) un **card simple**: imagen principal del tour + botón para abrirlo.
**No** se muestran especificaciones (esas las maneja el propio Real Estate).

```blade
@if($property->virtual_tour_url)
<div class="vt-tour-card">
    <div class="vt-tour-card-media">
        {{-- Si guardaste también la imagen del tour, úsala; si no, una genérica --}}
        <img src="{{ $property->virtual_tour_image ?? asset('img/tour-cover.jpg') }}"
             alt="Tour Virtual 360">
        <span class="vt-tour-badge"><i class="fa fa-vr-cardboard"></i> Tour 360</span>
    </div>
    <div class="vt-tour-card-body">
        <h4>Recorrido Virtual 360°</h4>
        <p>Explora esta propiedad en un tour inmersivo.</p>
        <a href="{{ $property->virtual_tour_url }}" target="_blank" rel="noopener"
           class="vt-tour-btn">
            <i class="fa fa-play"></i> Abrir Tour Virtual
        </a>
    </div>
</div>
@endif
```

> Para mostrar la imagen principal del tour en el card de detalle, guarda también
> el `main_image` del API junto al `virtual_tour_url` al momento de asociar el tour,
> o haz un `GET /api/v1/tours/{id}` puntual para traerla.

---

## 7. Design System (replicar el look del landing de tours)

Mantén el mismo lenguaje visual *glass-morphism* del proveedor.

### Fuentes

```html
<link href="https://fonts.googleapis.com/css?family=Fahkwang:400,500,600,700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600&display=swap" rel="stylesheet">
```

- Títulos / etiquetas: **Fahkwang**, en MAYÚSCULAS, `letter-spacing: 2.5px`.
- Texto general: **Open Sans**.

### Paleta

| Uso                         | Valor                          |
|-----------------------------|--------------------------------|
| Fondo glass (paneles)       | `rgba(14, 11, 8, 0.72)` + `backdrop-filter: blur(20px)` |
| Botones flotantes           | `rgba(8, 8, 12, 0.72)`         |
| Acento activo / CTA         | `#e91e63`                      |
| Glow del acento             | `rgba(233, 30, 99, 0.5)`       |
| Texto primario              | `rgba(255,255,255,0.9)`        |
| Texto secundario            | `rgba(255,255,255,0.6)`        |
| Divisores                   | `rgba(255,255,255,0.13)`       |

### CSS base de la grilla y el card

```css
.tours-page {
    background: #0e0b08;
    min-height: 100vh;
    padding: 40px 24px;
    font-family: 'Open Sans', sans-serif;
}
.tours-header h1 {
    font-family: 'Fahkwang', sans-serif;
    text-transform: uppercase;
    letter-spacing: 2.5px;
    color: #fff;
}
.tours-filter input {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.13);
    color: #fff;
    border-radius: 8px;
    padding: 12px 16px;
    width: 100%;
    max-width: 420px;
}
.tours-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 28px;
}

/* Card de tour */
.tour-card {
    background: rgba(14, 11, 8, 0.72);
    backdrop-filter: blur(20px);
    border: 2px solid transparent;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s cubic-bezier(.4,0,.2,1), border-color 0.2s;
}
.tour-card:hover {
    transform: scale(1.03);
    border-color: rgba(255,255,255,0.5);
}
.tour-card-media {
    position: relative;
    aspect-ratio: 16 / 10;
    overflow: hidden;
}
.tour-card-media img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.tour-card-media .tour-title {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 24px 14px 12px;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    color: #fff;
    font-family: 'Fahkwang', sans-serif;
    font-size: 14px;
    letter-spacing: 1px;
}
.tour-badge {
    position: absolute; top: 10px; left: 10px;
    background: #e91e63;
    box-shadow: 0 0 12px rgba(233,30,99,0.5);
    color: #fff;
    font-size: 10px; font-weight: 600;
    padding: 4px 10px; border-radius: 20px;
    text-transform: uppercase; letter-spacing: 1px;
}
.tour-card-body { padding: 14px; }
.tour-card-body .tour-location {
    color: rgba(255,255,255,0.6);
    font-size: 13px;
    margin-bottom: 12px;
}
.tour-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(8,8,12,0.72);
    border: 1px solid rgba(255,255,255,0.13);
    color: rgba(255,255,255,0.9);
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.2s;
}
.tour-btn:hover { background: #e91e63; color: #fff; border-color: #e91e63; }
```

### Markup de la grilla

```blade
@extends('layouts.app')
@section('content')
<div class="tours-page">
    <div class="tours-header">
        <h1>Tours Virtuales 360</h1>
        <form class="tours-filter" method="GET" action="{{ route('tours.index') }}">
            <input type="text" name="q" value="{{ $q }}"
                   placeholder="Buscar tour por nombre o ubicación...">
        </form>
    </div>

    <div class="tours-grid">
        @forelse($tours as $tour)
            <div class="tour-card">
                <div class="tour-card-media">
                    <img src="{{ $tour['main_image'] }}" alt="{{ $tour['name'] }}">
                    <span class="tour-badge"><i class="fa fa-vr-cardboard"></i> Tour 360</span>
                    <div class="tour-title">{{ $tour['name'] }}</div>
                </div>
                <div class="tour-card-body">
                    <div class="tour-location">
                        <i class="fa fa-map-marker"></i> {{ $tour['location'] ?? 'Ubicación no disponible' }}
                    </div>
                    <a href="{{ $tour['tour_url'] }}" target="_blank" rel="noopener" class="tour-btn">
                        <i class="fa fa-play"></i> Ver Tour 360
                    </a>
                </div>
            </div>
        @empty
            <p style="color:rgba(255,255,255,0.6)">No se encontraron tours.</p>
        @endforelse
    </div>
</div>
@endsection
```

---

## 8. Checklist de integración

- [ ] Generar token con `php artisan tour:client "Real Estate"` (en el proveedor).
- [ ] Configurar `TOUR_API_URL` y `TOUR_API_TOKEN` en el `.env` del Real Estate.
- [ ] Confirmar `APP_URL` público en el proveedor (para imágenes absolutas).
- [ ] Activar `api_consumable` en las propiedades con tour (admin del proveedor).
- [ ] Migración `virtual_tour_url` en `properties` y `projects` del Real Estate.
- [ ] Ruta + controlador + vista `/tours` con filtro.
- [ ] Card de tour en el detalle de propiedad/proyecto.
- [ ] Replicar el design system glass-morphism.
