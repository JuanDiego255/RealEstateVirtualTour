# Plan de pruebas — Bot de WhatsApp + Automatización del CRM

Guía punto por punto para corroborar que toda la integración funciona. Marcá cada
casilla a medida que verificás. Está ordenado por dependencias: hacé los bloques
en orden.

> Convención: **Precondición** → **Pasos** → **Resultado esperado**.

---

## 0. Preparación del entorno

- [ ] **0.1 Migraciones**
  **Pasos:** `php artisan migrate`
  **Esperado:** corren sin error, incluidas: `company_whatsapp_bots`, `company_ai_settings`,
  `whatsapp_chats`, `whatsapp_conversations`, `whatsapp_bot_settings`, `whatsapp_bot_usages`,
  `whatsapp_bot_promotions`, `company_mail_settings`, `lead_tasks.due_notified_at`,
  `whatsapp_chats.lead_id`, `follow_up_sequences/steps/enrollments`.

- [ ] **0.2 Cron maestro** (para recordatorios y seguimientos)
  **Pasos:** en el servidor, agregá al crontab:
  `* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1`
  **Esperado:** `php artisan schedule:list` muestra `crm:dispatch-reminders`,
  `crm:run-followups`, `crm:run-pipeline-rules`, `subscriptions:check-expiring`.

- [ ] **0.3 Usuarios de prueba**
  **Pasos:** creá una empresa con: 1 `company_admin` y 2 `agent` (status `active`), todos con email real.
  **Esperado:** podés iniciar sesión con cada uno.

- [ ] **0.4 Correo (para emails)**
  Se prueba en el bloque 9. Hasta configurarlo, los avisos llegan igual a la **campana**.

---

## 1. Bot de WhatsApp — Infraestructura

- [ ] **1.1 Credenciales (superadmin)**
  **Pasos:** `admin/companies/{empresa}/whatsapp` → cargá `phone_number_id`, `access_token`,
  `app_secret`, `verify_token`; habilitá el bot. En `admin/companies/{empresa}/ai` cargá la API key de Anthropic y habilitá.
  **Esperado:** se guardan; los secretos no se re-muestran.

- [ ] **1.2 Verificación del webhook**
  **Pasos:** en Meta, configurá el webhook a `https://tudominio/webhook/whatsapp` con el verify token.
  **Esperado:** Meta valida (GET `hub.challenge`). Log en `storage/logs` sin errores.

- [ ] **1.3 Recepción de mensajes**
  **Pasos:** escribí al número desde otro teléfono.
  **Esperado:** aparece el chat en `admin/whatsapp` y el mensaje entrante queda guardado.

---

## 2. Bot — Cerebro y herramientas de catálogo

- [ ] **2.1 Responde con catálogo real**
  **Precondición:** cargá al menos 3 vehículos (properties tipo vehículo) en la empresa.
  **Pasos:** preguntá por WhatsApp: "¿Qué Toyota tienen?"
  **Esperado:** responde con vehículos reales del catálogo (marca/modelo/precio), sin inventar.

- [ ] **2.2 Ficha y fotos**
  **Pasos:** pedí detalle de un vehículo.
  **Esperado:** manda datos + hasta 3 fotos.

- [ ] **2.3 Estado apartado/vendido**
  **Precondición:** marcá un vehículo como `reserved` o `sold`.
  **Pasos:** preguntá por ese vehículo.
  **Esperado:** avisa que está apartado/vendido y ofrece alternativas.

- [ ] **2.4 No inventa precios**
  **Pasos:** preguntá por algo que no está en el catálogo.
  **Esperado:** dice que lo confirma / no lo tiene; no inventa un precio.

- [ ] **2.5 Red de seguridad (apartar)**
  **Pasos:** pedí "apartámelo".
  **Esperado:** el chat pasa a **una persona** (bot pausado, badge "Pidió persona") en vez de prometerlo.

---

## 3. Config del negocio (menú WhatsApp → Configuración)

- [ ] **3.1 Tono**
  **Pasos:** `admin/whatsapp-config` → escribí un perfil de tono → guardá → probá el bot.
  **Esperado:** el bot responde en ese tono.

- [ ] **3.2 Entrenar por capturas**
  **Pasos:** subí 2-3 capturas de chats reales → "Generar perfil de tono".
  **Esperado:** genera un perfil editable, informa costo aprox., y **borra** las imágenes.

- [ ] **3.3 Promociones**
  **Pasos:** cargá una promo vigente → preguntá al bot por promociones.
  **Esperado:** menciona la promo. Desactivala → deja de mencionarla.

- [ ] **3.4 Financiamiento (toggle)**
  **Pasos:** con el toggle **apagado** (superadmin whatsapp), preguntá por financiamiento.
  **Esperado:** toma datos y hace **handoff**. Con el toggle **encendido**, da una cuota estimada con disclaimer.

- [ ] **3.5 Relevo por palabra clave / nota de voz**
  **Pasos:** mandá una palabra clave de relevo (ej. "reclamo") o una nota de voz.
  **Esperado:** el bot no responde; pasa a persona.

---

## 4. Prueba de manejo

- [ ] **4.1 Agenda una cita**
  **Pasos:** "Quiero probar el {vehículo} mañana a las 3pm, soy Juan".
  **Esperado:** confirma la cita tentativa y aparece en `admin/crm/appointments` (visita de vehículo, estado `scheduled`).

- [ ] **4.2 Choque de horario**
  **Pasos:** pedí otra prueba a la misma hora/mismo vehículo.
  **Esperado:** propone otro horario (no duplica).

- [ ] **4.3 Fuera de horario**
  **Precondición:** configurá horario de atención del bot.
  **Pasos:** pedí una prueba fuera de ese horario.
  **Esperado:** ofrece otro horario.

- [ ] **4.4 Efecto en el lead** (ver bloque 6 primero)
  **Esperado:** el lead pasa a **Calificado** y se registran la actividad y las tareas de plantilla.

---

## 5. Facturación / consumo (superadmin)

- [ ] **5.1 Panel de facturación**
  **Pasos:** menú WhatsApp → **Facturación** (`admin/whatsapp-billing`). Elegí el mes.
  **Esperado:** tabla por empresa con conversaciones, facturado, costo real y **margen**; totales arriba.

- [ ] **5.2 Detalle por empresa**
  **Pasos:** clic en "Detalle".
  **Esperado:** lista de conversaciones del mes con tokens y costo IA.

- [ ] **5.3 Fusible (tope)**
  **Precondición:** poné un `overage_cap_usd` bajo y `allow_overage` apagado con cupo agotado.
  **Esperado:** el estado marca "Cupo agotado/Tope" y el bot deja de responder.

- [ ] **5.4 Consumo (negocio)**
  **Pasos:** en `admin/whatsapp-config` mirá la tarjeta "Consumo del mes".
  **Esperado:** barra de progreso del cupo + extras + aviso si está pausado.

---

## 6. Bot → Lead (captura automática)

- [ ] **6.1 Crea lead**
  **Pasos:** escribí al bot desde un número **nuevo**.
  **Esperado:** aparece un lead en `admin/crm/leads` con **fuente WhatsApp** y actividad "Primer contacto por WhatsApp".

- [ ] **6.2 Dedup**
  **Pasos:** volvé a escribir desde el mismo número.
  **Esperado:** **no** se duplica; solo se actualiza el último contacto.

- [ ] **6.3 Enlace chat ↔ lead**
  **Pasos:** abrí el chat en `admin/whatsapp/{chat}`.
  **Esperado:** botón **"Ver lead"** que abre la ficha.

- [ ] **6.4 Captura aunque el bot esté apagado**
  **Precondición:** apagá el bot (o pausá el chat).
  **Pasos:** escribí desde un número nuevo.
  **Esperado:** el lead **igual se crea** (la captura es independiente del bot).

---

## 7. Asignación automática (round-robin)

- [ ] **7.1 Reparte al menos cargado**
  **Precondición:** 2+ agentes activos.
  **Pasos:** generá varios leads (varios números).
  **Esperado:** se reparten entre los agentes, priorizando al que tiene **menos leads abiertos** (no todos al admin).

- [ ] **7.2 Sin agentes**
  **Precondición:** sin usuarios `agent`.
  **Esperado:** cae al `company_admin`.

---

## 8. Notificaciones a asesores + recordatorios que se envían

- [ ] **8.1 Lead asignado**
  **Pasos:** generá un lead del bot (o reasigná un lead a otro agente en `admin/crm/leads`).
  **Esperado:** el asesor recibe aviso en la **campana** (y email si hay SMTP). No te avisa si te lo asignás a vos mismo.

- [ ] **8.2 Recordatorio vencido**
  **Pasos:** creá un recordatorio con fecha pasada (`admin/crm/reminders`) → `php artisan crm:dispatch-reminders`.
  **Esperado:** llega a la campana/email y **no** se repite en la siguiente corrida.

- [ ] **8.3 Aviso de cita**
  **Pasos:** creá/tené una cita para dentro de <60 min → `php artisan crm:dispatch-reminders`.
  **Esperado:** el asesor de la cita recibe el aviso.

- [ ] **8.4 Tarea vencida**
  **Pasos:** creá una tarea (`admin/crm/tasks`) con `due_at` pasado → `php artisan crm:dispatch-reminders`.
  **Esperado:** el asignado recibe "Tarea pendiente" (una sola vez).

- [ ] **8.5 Pipeline rules (fix)**
  **Pasos:** creá una `PipelineRule` activa y un lead que la dispare → `php artisan crm:run-pipeline-rules`.
  **Esperado:** corre **sin error**, crea actividad/recordatorio bien formados (antes tronaba).

---

## 9. Configuración de correo SMTP por empresa

- [ ] **9.1 Guardar SMTP**
  **Pasos:** menú **Correo (SMTP)** (`admin/mail-settings`, como admin) → preset Gmail → usuario + **clave de aplicación** → habilitar → guardar.
  **Esperado:** se guarda; la clave no se re-muestra (placeholder "sin cambios").

- [ ] **9.2 Correo de prueba**
  **Pasos:** botón "Enviar correo de prueba".
  **Esperado:** llega el correo; el badge queda en **OK**. Si falla, muestra el error.

- [ ] **9.3 Envío desde la cuenta de la empresa**
  **Pasos:** dispará una notificación con email (bloque 8) para un usuario de esa empresa.
  **Esperado:** el correo sale **desde la dirección configurada** de la empresa.

- [ ] **9.4 Sin SMTP configurado**
  **Precondición:** empresa sin SMTP propio.
  **Esperado:** los correos usan el mailer por defecto (`.env`) sin romperse; la campana funciona igual.

> Nota Gmail: requiere verificación en 2 pasos activada para generar la clave de aplicación.

---

## 10. Bandeja "sin atender" (menú CRM → Sin atender)

- [ ] **10.1 Vista del agente**
  **Pasos:** logueado como agente, abrí `admin/crm/inbox`.
  **Esperado:** ves **tus** leads sin contactar (ordenados por score), tareas vencidas y recordatorios vencidos, con contadores.

- [ ] **10.2 Vista del admin**
  **Pasos:** como admin, abrí la bandeja.
  **Esperado:** ves lo de **toda la empresa** (con columna de asesor).

- [ ] **10.3 Vacío**
  **Esperado:** mensajes "Nada sin contactar 🎉" cuando corresponde.

---

## 11. Secuencias de seguimiento (nurturing) — menú CRM → Seguimientos

- [ ] **11.1 Crear secuencia**
  **Pasos:** `admin/crm/follow-ups` → Nueva → nombre + disparo "Al crear el lead" → agregá 2 pasos:
  paso 1 (0 h, email, mensaje "Hola {{nombre}}...") y paso 2 (48 h, whatsapp, mensaje propio). Activá y guardá.
  **Esperado:** aparece en la lista con "2 pasos".

- [ ] **11.2 Inscripción automática**
  **Pasos:** creá un lead nuevo (con email).
  **Esperado:** queda inscrito (se crea una `follow_up_enrollment` activa).

- [ ] **11.3 Envío del paso 1 (email)**
  **Pasos:** `php artisan crm:run-followups`.
  **Esperado:** llega el email del paso 1; queda como actividad en el lead; la inscripción avanza al paso 2.

- [ ] **11.4 WhatsApp fuera de ventana de 24 h**
  **Precondición:** lead sin mensaje entrante en las últimas 24 h.
  **Pasos:** que corra un paso de WhatsApp.
  **Esperado:** **no** manda texto libre; crea una **tarea "Seguimiento manual"** al asesor.

- [ ] **11.5 WhatsApp dentro de ventana**
  **Precondición:** el lead escribió hace <24 h.
  **Esperado:** el paso de WhatsApp **sí** se envía y queda en el hilo.

- [ ] **11.6 Detener si responde**
  **Precondición:** secuencia con "Detener si responde".
  **Pasos:** el lead responde por WhatsApp → corré el cron.
  **Esperado:** la inscripción queda **detenida** (`stopped`, motivo `lead_respondio`).

- [ ] **11.7 Cierre del lead**
  **Pasos:** marcá el lead como Ganado/Perdido → corré el cron.
  **Esperado:** la inscripción se **detiene** (`lead_cerrado`).

---

## 12. Regresión rápida

- [ ] **12.1** El panel de conversaciones (`admin/whatsapp`) sigue permitiendo tomar control / devolver al bot.
- [ ] **12.2** El CRM existente (leads, pipeline, agenda, reportes) sigue funcionando.
- [ ] **12.3** `php artisan schedule:list` no muestra errores.
- [ ] **12.4** Revisá `storage/logs/laravel.log` y el canal `whatsapp` sin excepciones nuevas.

---

### Comandos útiles

```bash
php artisan migrate                 # aplicar esquema
php artisan crm:dispatch-reminders  # recordatorios/citas/tareas
php artisan crm:run-followups       # secuencias de seguimiento
php artisan crm:run-pipeline-rules  # reglas de pipeline
php artisan schedule:list           # ver el cron
```
