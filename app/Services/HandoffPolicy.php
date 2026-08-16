<?php

namespace App\Services;

/**
 * Política de relevo humano: construye la sección del prompt "cuándo pasar a una
 * persona", detecta disparadores en el mensaje entrante y ofrece la red de
 * seguridad por regex sobre la salida del bot (promesas sin herramienta).
 */
class HandoffPolicy
{
    /**
     * Texto para el prompt de sistema según la configuración de relevo.
     */
    public static function promptSection(array $cfg): string
    {
        $lines = ['CUÁNDO PASAR A UNA PERSONA (llamá handoff_to_human):'];
        if (!empty($cfg['asks_for_human']))   $lines[] = '- Si el cliente pide hablar con una persona.';
        if (!empty($cfg['complaint']))        $lines[] = '- Si se queja o hace un reclamo.';
        if (!empty($cfg['asks_past_order']))  $lines[] = '- Si pregunta por algo que ya compró o gestionó (no ves historiales).';
        if (!empty($cfg['sends_receipt']))    $lines[] = '- Si manda un comprobante de pago (nunca confirmes pagos).';
        if (!empty($cfg['sends_voice_note'])) $lines[] = '- Si insiste con notas de voz (no las escuchás).';
        if (!empty($cfg['not_found']))        $lines[] = '- Si no encontrás lo que pide y no hay alternativas.';
        $lines[] = '- Nunca cotices financiamiento ni tases un vehículo de parte de pago: tomá los datos y pasá el chat.';
        return implode("\n", $lines);
    }

    /**
     * ¿El mensaje entrante dispara relevo por palabra clave?
     */
    public static function inboundTriggersHandoff(string $text, array $cfg): bool
    {
        $keywords = array_filter(array_map('trim', explode(',', (string) ($cfg['keywords'] ?? ''))));
        if (empty($keywords)) {
            return false;
        }
        $haystack = self::normalize($text);
        foreach ($keywords as $kw) {
            $kw = self::normalize($kw);
            if ($kw !== '' && mb_strpos($haystack, $kw) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Red de seguridad: ¿el bot prometió algo que requiere acción humana sin
     * haber llamado la herramienta correspondiente? Las promesas de cita solo
     * se toleran si efectivamente se ejecutó schedule_test_drive.
     *
     * @param  string[]  $usedTools  Nombres de herramientas ejecutadas en el turno.
     */
    public static function botPromiseWithoutTool(string $text, array $usedTools = []): bool
    {
        // Apartar/reservar y aprobación de crédito nunca son acciones del bot.
        $always = '/(te (lo )?(aparto|reservo|separo)'
            . '|qued[oó] (apartado|reservado|separado)'
            . '|te confirmo (el|tu) cr[eé]dito'
            . '|cr[eé]dito (aprobado|confirmado))/iu';
        if (preg_match($always, $text)) {
            return true;
        }

        // Promesa de cita: solo es válida si se usó la herramienta de agenda.
        if (!in_array('schedule_test_drive', $usedTools, true)) {
            $appointment = '/(te (confirmo|confirmamos) la cita'
                . '|qued[oó] agendad[ao]'
                . '|revis[oa] la agenda'
                . '|agend[eé] tu (cita|prueba))/iu';
            if (preg_match($appointment, $text)) {
                return true;
            }
        }

        return false;
    }

    private static function normalize(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
        return $s;
    }
}
