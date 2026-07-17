# API de Vehículos — Guía de consumo (sistema externo)

API autenticado por token (misma tabla `api_clients` / header `Authorization: Bearer`)
para consumir **todos los vehículos** del sistema, tengan o no tour virtual.

## Autenticación

```
Authorization: Bearer <TOUR_API_TOKEN>
```

El token se genera en el proveedor con `php artisan tour:client "Nombre del sistema"`.

Base URL: `{TOUR_API_URL}/api/v1`

---

## Endpoints

### 1. `GET /api/v1/vehicles` — Listado paginado

**Filtros (query params, todos opcionales):**

| Param          | Tipo                     | Descripción                                        |
|----------------|--------------------------|----------------------------------------------------|
| `brand`        | string \| lista (coma)   | Marca(s). Ej: `Toyota` o `Toyota,Nissan`           |
| `model`        | string \| lista          | Modelo(s)                                          |
| `year`         | int \| lista             | Año(s) exacto(s)                                   |
| `year_min`     | int                      | Año mínimo                                         |
| `year_max`     | int                      | Año máximo                                         |
| `color`        | string \| lista          | Color(es)                                          |
| `transmission` | string \| lista          | Transmisión                                        |
| `fuel_type`    | string \| lista          | Combustible                                        |
| `condition`    | string \| lista          | Condición (nuevo/usado…)                           |
| `price_min`    | number                   | Precio mínimo                                      |
| `price_max`    | number                   | Precio máximo                                      |
| `has_tour`     | bool (`1`/`0`)           | Solo vehículos con tour virtual                    |
| `category_id`  | int                      | Filtra por categoría                               |
| `q`            | string                   | Texto libre (marca, modelo, nombre, código, placa) |
| `sort`         | enum                     | `price_asc`, `price_desc`, `year_desc`, `year_asc` |
| `per_page`     | int (1–50, default 12)   | Resultados por página                              |
| `page`         | int                      | Página                                             |

**Ejemplo:**

```bash
curl -H "Authorization: Bearer $TOUR_API_TOKEN" \
  "$TOUR_API_URL/api/v1/vehicles?brand=Toyota&year_min=2018&sort=price_asc&per_page=12"
```

**Respuesta:**

```json
{
  "data": [
    {
      "id": 45,
      "name": "Corolla XLI",
      "title": "Toyota Corolla 2020",
      "code": "V-045",
      "brand": "Toyota",
      "model": "Corolla",
      "year": 2020,
      "color": "Blanco",
      "mileage_km": "45000",
      "fuel_type": "Gasolina",
      "transmission": "Automática",
      "condition": "Usado",
      "doors": "4",
      "passengers": "5",
      "engine_cc": "1800",
      "drivetrain": "FWD",
      "plate": "ABC123",
      "price": 15000,
      "currency": "USD",
      "formatted_price": "$15.000",
      "description": "…",
      "location": "San José",
      "status": "available",
      "status_name": "Disponible",
      "main_image": "https://proveedor.com/file/uploads/v45.jpg",
      "category": { "id": 3, "name": "Agencia X", "slug": "agencia-x" },
      "has_virtual_tour": true,
      "scenes_count": 4,
      "tour_url": "https://proveedor.com/virtual-tour/45",
      "share_url": "https://proveedor.com/p/<token>"
    }
  ],
  "meta": { "current_page": 1, "last_page": 6, "per_page": 12, "total": 68 }
}
```

> `tour_url` es `null` cuando el vehículo no tiene tour. `has_virtual_tour`
> se calcula por la existencia de escenas activas.

### 2. `GET /api/v1/vehicles/{id}` — Detalle

Igual que un item del listado, más:
- `images`: galería `[{ url, caption, is_primary }]`
- `scenes`: escenas del tour `[{ id, title, type, thumbnail }]`

### 3. `GET /api/v1/vehicles/filters` — Opciones para los dropdowns

Devuelve los valores disponibles para armar los filtros. Soporta narrowing en
cascada: pasar `brand` acota `models`; pasar `brand`+`model` acota `years`.

```bash
curl -H "Authorization: Bearer $TOUR_API_TOKEN" \
  "$TOUR_API_URL/api/v1/vehicles/filters?brand=Toyota"
```

```json
{
  "brands": ["Toyota", "Nissan", "Hyundai"],
  "models": ["Corolla", "Hilux", "Yaris"],
  "years": [2023, 2022, 2021, 2020],
  "colors": ["Blanco", "Negro", "Gris", "Rojo"],
  "transmissions": ["Automática", "Manual"],
  "fuel_types": ["Gasolina", "Diésel", "Híbrido"],
  "conditions": ["Nuevo", "Usado"],
  "price_range": { "min": 6500, "max": 48000 }
}
```

---

## Notas

- **Imágenes:** URLs absolutas listas para `<img src>`. Requiere `APP_URL`
  público en el proveedor.
- **Alcance:** se exponen todos los vehículos excepto los de estado `inactive`.
- **Códigos de error:** `401` token inválido/ausente, `404` vehículo no
  encontrado, `429` límite de tasa (60 req/min).
