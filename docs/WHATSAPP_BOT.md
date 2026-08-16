# Bot de WhatsApp + IA (Anthropic) — este proyecto

Port de la lógica del e-commerce (SafeWor) a este proyecto de vehículos.
Adaptaciones clave a **este** repo:

- **Multi-tenant por BD** (columna `company_id`), no `stancl/tenancy`. Todo se
  scopea por empresa; **solo el superadmin** configura credenciales, modelo y
  API de WhatsApp, **por empresa**.
- **Solo vehículos** (no propiedades). El catálogo vive aquí (`properties` con
  `property_type = vehicle`); el bot consultará la BD local.
- **Solo inbound**: no hay salida de mensajes iniciada por la empresa (sin
  kiosko saliente ni plantillas). El cliente siempre abre la ventana de 24 h.
- **Hosting compartido**: la respuesta del bot correrá vía `app()->terminating()`
  (Etapa 3), no por worker. El modo "esperar N minutos" queda supeditado a un
  cron; por defecto se responde de inmediato.
- **Financiamiento → handoff**: el bot nunca cotiza una cuota; toma los datos y
  pasa el chat a una persona.

## Entregas

### ✅ Entrega 1 (Etapas 0–2) — esta
Base de datos, configuración del superadmin por empresa, webhook (recibe,
firma, idempotencia, guarda) y panel de conversaciones con respuesta manual.

**Esquema** (`company_id` en todo):
- `company_whatsapp_bots` — credenciales Meta, plan, activación (superadmin).
- `company_ai_settings` — API key Anthropic + modelo + plan (superadmin).
- `whatsapp_conversations` — cada mensaje (idempotente por `wam_id`).
- `whatsapp_chats` — estado del chat: `bot_paused`, relevo humano.
- `whatsapp_bot_settings` — tono aprendido, reglas, cierre, handoff (negocio).

**Webhook** `GET|POST /webhook/whatsapp` (público, CSRF exento):
- Resuelve empresa por `phone_number_id`.
- Valida firma HMAC (`X-Hub-Signature-256`) con el `app_secret` de esa empresa
  sobre el cuerpo **crudo**.
- Idempotencia por `wam_id`.
- **Siempre responde 200** (un 500 haría que Meta reintente sin parar).
- Guarda el mensaje y actualiza el chat. (La respuesta del bot llega en Etapa 3.)

**Config superadmin** (rutas dentro de `admin/companies`, `role:super_admin`):
- `/{company}/whatsapp` — conexión Cloud API, plan, cuándo responde.
- `/{company}/ai` — API key Anthropic, modelo, plan.
- Secretos: solo se reemplazan si se envían. Modelo validado contra catálogo.

**Panel de conversaciones** (`/admin/whatsapp`, `canAccessModule('whatsapp')`):
- Lista de chats, hilo, "tomar control" (pausa el bot), respuesta manual por la
  Cloud API (marca saliente humano y pausa el bot), polling cada 5 s.

**Config previa (una vez):**
1. `.env`: `WHATSAPP_VERIFY_TOKEN=` (cadena larga), `WHATSAPP_GRAPH_VERSION=v21.0`.
2. `php artisan migrate`.
3. Superadmin carga credenciales de Meta por empresa (ver la guía in-app en la
   pantalla de WhatsApp de la empresa) y suscribe el webhook al campo `messages`.

### ⏳ Próximas entregas
- **Etapa 3** — Bot sin herramientas: `WhatsAppBotService` + Anthropic (Haiku),
  caché de prompt, `terminating()`, tope de vueltas y de historial.
- **Etapa 4** — Herramientas de catálogo: `VehicleSearchService`,
  `search_vehicles`, `get_vehicle_detail`, `check_vehicle_status` (BD local).
- **Etapa 5** — Relevo humano: `HandoffPolicy` + red de seguridad por regex
  ("te lo aparto", "te confirmo el crédito").
- **Etapa 6** — Entrenamiento por capturas (Sonnet visión → perfil de tono),
  reglas y cierre; promociones.
- **Etapa 7** — Prueba de manejo (`schedule_test_drive`).
- **Etapa 9** — Cuotas/costos: ventana de 24 h, consumo por conversación, tope
  de gasto (fusible que apaga el bot).

## Reglas de oro (se mantienen del origen)
- El bot **nunca inventa** precios, existencias ni datos: solo lo que devuelven
  las herramientas o el prompt.
- Precio/kilometraje/año son datos duros: si no vienen de la herramienta, se
  confirman, no se estiman.
- Financiamiento y recibir un carro como parte de pago → **handoff**.
