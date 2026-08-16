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

### ✅ Entrega 2 (Etapas 3–4 + 5 parcial + 9 base)
El cerebro del bot y las herramientas del catálogo.

- **`WhatsAppBotService`** — arma el prompt (tono + reglas duras + política de
  relevo + reglas del negocio + cierre), corre el bucle de tool-use contra
  Anthropic (Haiku 4.5, `max_tokens` 700, `MAX_TOOL_LOOPS` 3, historial 10),
  con **caché de prompt** en el bloque de sistema. Envía texto + fotos.
- **Respuesta inmediata** vía `app()->terminating()` → `ProcessWhatsAppMessageJob::dispatchSync`
  (hosting compartido, sin worker). El job re-verifica el estado antes de responder.
- **`VehicleSearchService`** (BD local, scopeado por empresa vía categoría):
  `search_vehicles`, `get_vehicle_detail`, `check_vehicle_status`
  (disponible/apartado/vendido + alternativas). Mapea estados de `properties`.
- **`quote_financing`** — solo si la empresa activa el toggle
  `allow_financing_quote`; si no, el prompt obliga a `handoff_to_human`.
- **`HandoffPolicy`** — sección del prompt, disparadores por palabra clave en el
  mensaje entrante, nota de voz → relevo, y **red de seguridad por regex** sobre
  la salida ("te lo aparto", "te confirmo el crédito").
- **Consumo y fusible** — `WhatsappBotUsage` cuenta por conversación (ventana de
  24 h), acumula costo/tokens y `billing()` calcula plan/incluidas/extras/margen.
  Si se agota la cuota o el tope, el bot deja de responder (`isBlocked`).

Regla de oro reforzada en el prompt: el bot solo afirma lo que devuelven las
herramientas.

### ✅ Entrega 3 (Etapa 6)
La UI que administra el **negocio** (no el superadmin): `WhatsappBotSettingController`
en `/admin/whatsapp-config`, submenú "WhatsApp → Configuración".

- **Tono, reglas y cierre** — edición de `training_profile`, `custom_rules` y
  `order_instructions`.
- **Entrenar por capturas** — `BotTrainingService` sube imágenes de chats reales,
  las manda al modelo de **visión** (el configurado, Sonnet por defecto) y destila
  un perfil de tono editable. Las capturas se **borran** tras el análisis y se
  informa el costo aproximado.
- **Cuándo entra una persona** — editor de la política de relevo (`handoff`):
  switches, palabras clave, horas para reanudar y mensaje de relevo.
- **Promociones** — `WhatsappBotPromotion` (activa + rango de fechas); el bot solo
  cita las **vigentes**, inyectadas en el prompt.

### ✅ Entrega 4 (Etapa 7)
Prueba de manejo agendable desde el bot, reutilizando la agenda existente.

- **`schedule_test_drive`** — nueva herramienta del bot. Pide día/hora/nombre y
  crea una cita **tentativa** en la agenda del negocio (`App\Appointment`,
  `type = vehicle_visit`, `status = scheduled`). La confirma un asesor.
- **`TestDriveScheduler`** — valida vehículo disponible, fecha a futuro y dentro
  del horario de atención, evita **choques de horario** (mismo vehículo o asesor),
  asigna un asesor por defecto (company_admin) y enlaza el lead por teléfono si
  existe. Sin asesor asignable → relevo.
- **Contexto temporal** en el prompt (fecha/hora de hoy) para resolver "mañana",
  "el sábado", etc. a ISO 8601.
- **Red de seguridad afinada** — la promesa de cita solo se tolera si de verdad
  se ejecutó `schedule_test_drive`; apartar/crédito siguen forzando relevo.

### ⏳ Próximas entregas
- **Etapa 9 (UI)** — Paneles de facturación/consumo por empresa y periodo.
- **Automatización del CRM** (lo que sigue tras el bot).

## Reglas de oro (se mantienen del origen)
- El bot **nunca inventa** precios, existencias ni datos: solo lo que devuelven
  las herramientas o el prompt.
- Precio/kilometraje/año son datos duros: si no vienen de la herramienta, se
  confirman, no se estiman.
- Financiamiento y recibir un carro como parte de pago → **handoff**.
