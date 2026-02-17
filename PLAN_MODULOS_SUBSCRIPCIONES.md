# Plan de Implementación: Módulos de Suscripciones, Paquetes y Bolsa Inmobiliaria

## Resumen Ejecutivo

Este documento detalla el plan de implementación para transformar el sistema de Virtual Tours en una plataforma completa de gestión inmobiliaria con:
- Sistema de suscripciones y paquetes
- Gestión de empresas/compañías
- Bolsa inmobiliaria con sistema de comisiones
- Landing page público con sectores y categorías

---

## Arquitectura General

### Decisiones de Diseño
- **Multi-tenancy**: Se elimina Stancl/Tenancy, se reemplaza con sistema de empresas en una sola BD
- **Email**: Obligatorio para todos los usuarios (necesario para notificaciones)
- **Sectores**: Organizados por tipo de negocio (Bienes Raíces, Vehículos, Comercial, etc.)
- **Moneda**: Soporte dual CRC/USD seleccionable por el usuario

### Roles de Usuario
| Rol | Descripción |
|-----|-------------|
| `super_admin` | Control total del sistema, crea sectores, gestiona todas las suscripciones |
| `company_admin` | Administrador de empresa, gestiona usuarios y contenido de su empresa |
| `agent` | Usuario de empresa, puede crear inmuebles según permisos del paquete |
| `client` | Solo visualización (futuro, opcional) |

---

## Fases de Implementación

### FASE 1: Fundación (Usuarios, Roles, Empresas)
**Duración estimada: Base del sistema**

#### 1.1 Migración: Actualizar tabla `users`
```sql
ALTER TABLE users ADD:
- email (string, unique, required)
- phone (string, nullable)
- role (enum: super_admin, company_admin, agent) default 'agent'
- company_id (FK companies, nullable)
- avatar (string, nullable)
- status (enum: active, inactive, suspended) default 'active'
- email_verified_at (timestamp, nullable)
```

#### 1.2 Migración: Crear tabla `companies`
```sql
CREATE TABLE companies:
- id (bigint, PK)
- name (string) -- Nombre comercial: "Autos Grecia"
- legal_name (string, nullable) -- Razón social: "Autos Grecia S.A."
- tax_id (string, nullable) -- Cédula jurídica
- description (text, nullable)
- logo (string, nullable)
- cover_image (string, nullable)
- phone (string, nullable)
- email (string, nullable)
- address (text, nullable)
- website (string, nullable)
- owner_id (FK users) -- Usuario dueño de la empresa
- status (enum: active, inactive, suspended) default 'active'
- settings (JSON, nullable) -- Configuraciones adicionales
- created_at, updated_at
```

#### 1.3 Middleware
- `CheckRole` - Verificar rol de usuario
- `CheckCompany` - Verificar pertenencia a empresa
- `CheckSubscription` - Verificar suscripción activa

---

### FASE 2: Paquetes y Suscripciones
**El núcleo del modelo de negocio**

#### 2.1 Migración: Crear tabla `packages`
```sql
CREATE TABLE packages:
- id (bigint, PK)
- name (string) -- "Básico", "Profesional", "Enterprise"
- slug (string, unique)
- description (text)
- price (decimal 10,2) -- Precio base
- currency (enum: CRC, USD) default 'CRC'
- billing_period (enum: monthly, quarterly, yearly)
- max_users (integer) -- Usuarios activos permitidos
- max_categories (integer) -- Categorías/negocios permitidos
- max_posts_per_category (integer) -- Inmuebles por categoría
- tour_price (decimal 10,2, nullable) -- Precio adicional por tour
- allows_tours (boolean) default false -- ¿Puede crear tours virtuales?
- allows_commission (boolean) default false -- ¿Participa en bolsa inmobiliaria?
- features (JSON) -- Features adicionales flexibles
- is_featured (boolean) default false -- Destacar en landing
- is_active (boolean) default true
- sort_order (integer) default 0
- created_at, updated_at
```

**Ejemplo de packages:**
| Paquete | Precio | Usuarios | Categorías | Posts | Tours | Comisión |
|---------|--------|----------|------------|-------|-------|----------|
| Básico | ₡15,000/mes | 2 | 1 | 20 | No | No |
| Profesional | ₡35,000/mes | 5 | 3 | 50 | Sí (+₡25,000 c/u) | Sí |
| Enterprise | ₡75,000/mes | Ilimitado | 10 | 200 | Sí (incluido) | Sí |

#### 2.2 Migración: Crear tabla `subscriptions`
```sql
CREATE TABLE subscriptions:
- id (bigint, PK)
- company_id (FK companies)
- package_id (FK packages)
- starts_at (date)
- ends_at (date)
- status (enum: pending, active, expired, cancelled, suspended)
- payment_method (enum: transfer, sinpe, card) -- Método preferido
- auto_renew (boolean) default false
- notes (text, nullable)
- created_by (FK users) -- Quién creó (admin o usuario)
- approved_by (FK users, nullable) -- Quién aprobó
- approved_at (timestamp, nullable)
- created_at, updated_at
```

#### 2.3 Migración: Crear tabla `subscription_payments`
```sql
CREATE TABLE subscription_payments:
- id (bigint, PK)
- subscription_id (FK subscriptions)
- amount (decimal 10,2)
- currency (enum: CRC, USD)
- payment_method (enum: transfer, sinpe, card)
- payment_reference (string, nullable) -- Número de comprobante
- proof_image (string, nullable) -- Imagen del comprobante
- payment_date (date)
- status (enum: pending, approved, rejected)
- reviewed_by (FK users, nullable)
- reviewed_at (timestamp, nullable)
- rejection_reason (text, nullable)
- notes (text, nullable)
- created_at, updated_at
```

#### 2.4 Vistas Admin
- `/admin/packages` - CRUD de paquetes (super_admin)
- `/admin/subscriptions` - Gestión de todas las suscripciones (super_admin)
- `/admin/subscriptions/pending` - Suscripciones pendientes de aprobación
- `/admin/my-subscription` - Mi suscripción (company_admin)

#### 2.5 Landing Page
- `/pricing` - Página de precios/paquetes
- `/subscribe/{package}` - Formulario de suscripción

---

### FASE 3: Sectores y Categorías
**Estructura del contenido público**

#### 3.1 Migración: Crear tabla `sectors`
```sql
CREATE TABLE sectors:
- id (bigint, PK)
- name (string) -- "Bienes Raíces", "Vehículos", "Comercial"
- slug (string, unique)
- description (text, nullable)
- icon (string, nullable) -- Clase de ícono (fa-home, fa-car)
- image (string, nullable)
- color (string, nullable) -- Color tema del sector
- is_active (boolean) default true
- sort_order (integer) default 0
- created_at, updated_at
```

**Sectores predefinidos:**
- Bienes Raíces (Casas, Apartamentos, Lotes)
- Vehículos (Autos, Motos, Maquinaria)
- Comercial (Locales, Oficinas, Bodegas)
- Alquileres (Temporal, Largo plazo)

#### 3.2 Migración: Crear tabla `categories`
```sql
CREATE TABLE categories:
- id (bigint, PK)
- company_id (FK companies)
- sector_id (FK sectors)
- name (string) -- "Condominio Corteza", "Autos Grecia"
- slug (string)
- description (text, nullable)
- logo (string, nullable)
- cover_image (string, nullable)
- contact_name (string, nullable)
- contact_email (string, nullable)
- contact_phone (string, nullable)
- contact_whatsapp (string, nullable)
- address (text, nullable)
- latitude (decimal 10,8, nullable)
- longitude (decimal 11,8, nullable)
- website (string, nullable)
- social_facebook (string, nullable)
- social_instagram (string, nullable)
- share_token (string, unique) -- Token para enlace compartible
- is_active (boolean) default true
- views_count (integer) default 0
- created_at, updated_at

UNIQUE(company_id, slug)
```

#### 3.3 Vistas
- `/admin/sectors` - CRUD sectores (super_admin only)
- `/admin/categories` - Mis categorías (company_admin/agent)
- `/sector/{slug}` - Landing: listado de categorías del sector
- `/c/{share_token}` - Enlace compartible de categoría

---

### FASE 4: Inmuebles/Listados Mejorados
**Evolución de la tabla properties**

#### 4.1 Migración: Actualizar tabla `properties`
```sql
ALTER TABLE properties ADD:
- category_id (FK categories, nullable)
- user_id (FK users) -- Creador
- property_type (enum: house, apartment, land, vehicle, commercial, other)
- description (text, nullable)
- location (string, nullable) -- Ubicación general
- address (string, nullable) -- Dirección exacta
- latitude (decimal 10,8, nullable)
- longitude (decimal 11,8, nullable)
- currency (enum: CRC, USD) default 'CRC'
- status (enum: available, reserved, negotiating, sold, rented, inactive)
- is_exclusive (boolean) default true -- ¿Exclusivo del propietario?
- commission_percentage (decimal 5,2, nullable) -- % comisión para externos
- commission_notes (text, nullable) -- Notas sobre comisión
- has_virtual_tour (boolean) default false
- is_featured (boolean) default false
- views_count (integer) default 0
- published_at (timestamp, nullable)
- sold_at (timestamp, nullable)
```

#### 4.2 Migración: Crear tabla `property_images`
```sql
CREATE TABLE property_images:
- id (bigint, PK)
- property_id (FK properties)
- image (string)
- caption (string, nullable)
- sort_order (integer) default 0
- is_primary (boolean) default false
- created_at, updated_at
```

#### 4.3 Migración: Crear tabla `property_features`
```sql
CREATE TABLE property_features:
- id (bigint, PK)
- property_id (FK properties)
- feature_name (string)
- feature_value (string, nullable)
- created_at, updated_at
```

---

### FASE 5: Bolsa Inmobiliaria (Sistema de Comisiones)
**El diferenciador del sistema**

#### 5.1 Migración: Crear tabla `commission_requests`
```sql
CREATE TABLE commission_requests:
- id (bigint, PK)
- property_id (FK properties)
- requester_id (FK users) -- Agente que quiere vender
- requester_company_id (FK companies)
- owner_id (FK users) -- Dueño del inmueble
- owner_company_id (FK companies)
- proposed_commission (decimal 5,2) -- % propuesto
- final_commission (decimal 5,2, nullable) -- % acordado
- status (enum: pending, negotiating, accepted, rejected, expired, completed, cancelled)
- initial_message (text) -- Mensaje inicial
- responded_at (timestamp, nullable)
- expires_at (timestamp, nullable) -- Expiración de la solicitud
- completed_at (timestamp, nullable)
- created_at, updated_at
```

#### 5.2 Migración: Crear tabla `commission_negotiations`
```sql
CREATE TABLE commission_negotiations:
- id (bigint, PK)
- commission_request_id (FK commission_requests)
- from_user_id (FK users)
- to_user_id (FK users)
- proposed_percentage (decimal 5,2, nullable)
- message (text)
- is_counter_offer (boolean) default false
- is_read (boolean) default false
- read_at (timestamp, nullable)
- created_at, updated_at
```

#### 5.3 Migración: Crear tabla `sales`
```sql
CREATE TABLE sales:
- id (bigint, PK)
- property_id (FK properties)
- seller_id (FK users) -- Quien vendió
- seller_company_id (FK companies)
- external_agent_id (FK users, nullable) -- Si fue con comisión
- external_company_id (FK companies, nullable)
- commission_request_id (FK commission_requests, nullable)
- sale_price (decimal 15,2)
- currency (enum: CRC, USD)
- total_commission_amount (decimal 15,2)
- owner_share_percentage (decimal 5,2)
- owner_share_amount (decimal 15,2)
- agent_share_percentage (decimal 5,2, nullable)
- agent_share_amount (decimal 15,2, nullable)
- buyer_name (string)
- buyer_phone (string, nullable)
- buyer_email (string, nullable)
- sale_date (date)
- notes (text, nullable)
- status (enum: pending, confirmed, cancelled)
- confirmed_by (FK users, nullable)
- confirmed_at (timestamp, nullable)
- created_at, updated_at
```

#### 5.4 Funcionalidades Bolsa Inmobiliaria

**Para propietarios (quien lista el inmueble):**
- Marcar inmueble como "no exclusivo" con % de comisión
- Recibir solicitudes de otros agentes
- Negociar comisión mediante mensajes
- Aceptar/rechazar solicitudes
- Registrar ventas y calcular distribución

**Para agentes externos:**
- Ver inmuebles disponibles para comisión (solo con paquete que lo permita)
- Solicitar permiso para vender
- Negociar comisión
- Ver estado de solicitudes

**Cálculo automático de comisiones:**
```
Ejemplo:
- Precio venta: ₡50,000,000
- Comisión total: 5% = ₡2,500,000
- Acuerdo: Propietario 60% / Agente externo 40%
- Propietario recibe: ₡1,500,000
- Agente externo recibe: ₡1,000,000
```

#### 5.5 Vistas Bolsa Inmobiliaria
- `/admin/bolsa` - Dashboard bolsa inmobiliaria
- `/admin/bolsa/available` - Inmuebles disponibles para comisión
- `/admin/bolsa/my-requests` - Mis solicitudes enviadas
- `/admin/bolsa/incoming` - Solicitudes recibidas
- `/admin/bolsa/negotiations/{id}` - Chat de negociación
- `/admin/sales` - Historial de ventas
- `/admin/sales/register/{property}` - Registrar venta

---

### FASE 6: Notificaciones
**Sistema de alertas**

#### 6.1 Usar sistema de notificaciones de Laravel
```sql
-- Laravel notifications table (built-in)
CREATE TABLE notifications:
- id (uuid, PK)
- type (string)
- notifiable_type (string)
- notifiable_id (bigint)
- data (JSON)
- read_at (timestamp, nullable)
- created_at, updated_at
```

#### 6.2 Tipos de notificaciones
| Evento | Email | In-App |
|--------|-------|--------|
| Nueva solicitud de comisión | ✓ | ✓ |
| Respuesta a solicitud | ✓ | ✓ |
| Contra-oferta recibida | ✓ | ✓ |
| Solicitud aceptada/rechazada | ✓ | ✓ |
| Suscripción por vencer | ✓ | ✓ |
| Pago aprobado/rechazado | ✓ | ✓ |
| Nueva venta registrada | ✓ | ✓ |

#### 6.3 Vistas
- Campana de notificaciones en navbar admin
- `/admin/notifications` - Listado completo
- Dropdown con últimas 5 notificaciones

---

### FASE 7: Landing Page Rediseñado
**Experiencia pública**

#### 7.1 Nuevas rutas públicas
```
GET /                      -> Landing principal con sectores destacados
GET /sector/{slug}         -> Categorías del sector
GET /c/{share_token}       -> Categoría específica (enlace compartible)
GET /property/{id}         -> Detalle de inmueble
GET /virtual-tour/{id}     -> Tour virtual (existente)
GET /pricing               -> Planes y precios
GET /subscribe/{package}   -> Formulario suscripción
GET /search                -> Búsqueda de inmuebles
```

#### 7.2 Componentes Landing
- Hero con buscador
- Sectores destacados (cards con íconos)
- Categorías/empresas destacadas
- Inmuebles destacados
- Planes de suscripción
- Footer con info de contacto

---

## Estructura de Archivos a Crear

### Migraciones (12 archivos)
```
database/migrations/
├── 2026_02_17_000001_update_users_table.php
├── 2026_02_17_000002_create_companies_table.php
├── 2026_02_17_000003_create_packages_table.php
├── 2026_02_17_000004_create_subscriptions_table.php
├── 2026_02_17_000005_create_subscription_payments_table.php
├── 2026_02_17_000006_create_sectors_table.php
├── 2026_02_17_000007_create_categories_table.php
├── 2026_02_17_000008_update_properties_table.php
├── 2026_02_17_000009_create_property_images_table.php
├── 2026_02_17_000010_create_commission_requests_table.php
├── 2026_02_17_000011_create_commission_negotiations_table.php
└── 2026_02_17_000012_create_sales_table.php
```

### Modelos (10 archivos)
```
app/Models/
├── User.php (actualizar)
├── Company.php
├── Package.php
├── Subscription.php
├── SubscriptionPayment.php
├── Sector.php
├── Category.php
├── Properties.php (actualizar)
├── PropertyImage.php
├── CommissionRequest.php
├── CommissionNegotiation.php
└── Sale.php
```

### Controladores (11 archivos)
```
app/Http/Controllers/
├── Admin/
│   ├── CompanyController.php
│   ├── PackageController.php
│   ├── SubscriptionController.php
│   ├── SectorController.php
│   ├── CategoryController.php
│   ├── BolsaController.php
│   └── SaleController.php
├── LandingController.php
├── SubscribeController.php
└── NotificationController.php
```

### Middleware (3 archivos)
```
app/Http/Middleware/
├── CheckRole.php
├── CheckSubscription.php
└── CheckCompanyAccess.php
```

### Vistas (~30 archivos)
```
resources/views/
├── admin/
│   ├── companies/
│   ├── packages/
│   ├── subscriptions/
│   ├── sectors/
│   ├── categories/
│   ├── bolsa/
│   ├── sales/
│   └── layouts/sidebar.blade.php (actualizar)
├── landing/
│   ├── index.blade.php
│   ├── sector.blade.php
│   ├── category.blade.php
│   ├── property.blade.php
│   ├── pricing.blade.php
│   └── subscribe.blade.php
└── emails/
    ├── commission-request.blade.php
    ├── commission-response.blade.php
    └── subscription-*.blade.php
```

---

## Ideas Adicionales para Robustez

### 1. Dashboard Analytics
- Gráficos de ventas por período
- Comisiones generadas
- Inmuebles más vistos
- Conversión de solicitudes

### 2. Sistema de Favoritos
- Agentes pueden guardar inmuebles de interés
- Lista de favoritos en su dashboard

### 3. Historial de Actividad
- Log de cambios en inmuebles
- Historial de negociaciones
- Auditoría de acciones

### 4. Comparador de Inmuebles
- Seleccionar hasta 3 inmuebles
- Vista comparativa lado a lado

### 5. Verificación de Empresas
- Badge "Verificado" para empresas validadas
- Proceso de verificación con documentos

### 6. Sistema de Calificaciones (futuro)
- Rating entre agentes después de transacciones
- Reseñas públicas

### 7. Integración WhatsApp
- Botón directo a WhatsApp en inmuebles
- Notificaciones via WhatsApp (opcional)

### 8. Exportación de Datos
- Exportar listado de inmuebles a Excel
- Reportes de ventas en PDF

### 9. Programación de Publicaciones
- Programar fecha de publicación de inmuebles
- Auto-despublicación por fecha

### 10. Sistema de Ofertas
- Compradores pueden hacer ofertas
- Notificación al vendedor
- Historial de ofertas

---

## Preguntas Pendientes

Antes de iniciar implementación, confirmar:

1. **SINPE Móvil**: ¿Se requiere integración automática o solo comprobante manual?
2. **Verificación de email**: ¿Los usuarios deben verificar email antes de usar el sistema?
3. **Período de prueba**: ¿Los paquetes tendrán período de prueba gratis?
4. **Inmuebles existentes**: ¿Qué hacemos con los properties actuales? ¿Se migran a una categoría default?
5. **Super admin inicial**: ¿Cuál usuario existente será el super_admin?

---

## Orden de Implementación Sugerido

1. **Sprint 1**: Fase 1 (Users, Companies) + Fase 2 (Packages, Subscriptions)
2. **Sprint 2**: Fase 3 (Sectors, Categories) + Fase 4 (Properties mejorado)
3. **Sprint 3**: Fase 5 (Bolsa Inmobiliaria)
4. **Sprint 4**: Fase 6 (Notificaciones) + Fase 7 (Landing)
5. **Sprint 5**: Refinamiento, testing, features adicionales

---

**¿Aprobamos este plan para comenzar la implementación?**
