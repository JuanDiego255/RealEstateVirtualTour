<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Properties;

class VehicleQuote extends Model
{
    protected $fillable = [
        'property_id', 'vehicle_id', 'customer_name', 'customer_email', 'customer_phone',
        'vehicle_price', 'down_payment', 'down_payment_percent', 'term_months',
        'interest_rate', 'monthly_payment', 'total_interest', 'total_amount',
        'currency', 'email_sent', 'pdf_generated', 'pdf_path', 'event_name',
        'payment_frequency'
    ];

    protected $casts = [
        'vehicle_price' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'down_payment_percent' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'email_sent' => 'boolean',
        'pdf_generated' => 'boolean',
    ];

    /**
     * Relacion con property (vehiculo)
     */
    public function property()
    {
        return $this->belongsTo(Properties::class, 'property_id');
    }

    /**
     * Alias para compatibilidad
     */
    public function vehicle()
    {
        return $this->property();
    }

    /**
     * Calcular cuota mensual usando formula de amortizacion francesa
     */
    public static function calculateMonthlyPayment($principal, $annualRate, $months)
    {
        if ($annualRate == 0) {
            return $principal / $months;
        }

        $monthlyRate = ($annualRate / 100) / 12;
        $payment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $months))
                   / (pow(1 + $monthlyRate, $months) - 1);

        return round($payment, 2);
    }

    /**
     * Calcular cuota anual usando formula de amortizacion francesa
     */
    public static function calculateAnnualPayment($principal, $annualRate, $years)
    {
        if ($annualRate == 0) {
            return $principal / $years;
        }

        $rate = $annualRate / 100;
        $payment = $principal * ($rate * pow(1 + $rate, $years))
                   / (pow(1 + $rate, $years) - 1);

        return round($payment, 2);
    }

    /**
     * Calcular total de intereses
     */
    public static function calculateTotalInterest($payment, $periods, $principal)
    {
        return round(($payment * $periods) - $principal, 2);
    }

    /**
     * Generar cotizacion completa
     * @param float $vehiclePrice Precio del vehiculo
     * @param float $downPayment Prima o enganche
     * @param int $termMonths Plazo en meses
     * @param float $interestRate Tasa de interes anual
     * @param string $frequency Frecuencia de pago: 'monthly' o 'annual'
     */
    public static function generateQuote($vehiclePrice, $downPayment, $termMonths, $interestRate, $frequency = 'monthly')
    {
        $principal = $vehiclePrice - $downPayment;
        $downPaymentPercent = ($downPayment / $vehiclePrice) * 100;

        if ($frequency === 'annual') {
            // Calculo anual - metodo frances con periodos anuales
            $years = ceil($termMonths / 12);
            $annualPayment = self::calculateAnnualPayment($principal, $interestRate, $years);
            $totalInterest = self::calculateTotalInterest($annualPayment, $years, $principal);
            $totalAmount = $principal + $totalInterest;

            return [
                'vehicle_price' => $vehiclePrice,
                'down_payment' => $downPayment,
                'down_payment_percent' => round($downPaymentPercent, 2),
                'financed_amount' => $principal,
                'term_months' => $termMonths,
                'term_years' => $years,
                'interest_rate' => $interestRate,
                'monthly_payment' => $annualPayment, // Campo usado para la cuota (anual en este caso)
                'annual_payment' => $annualPayment,
                'total_interest' => $totalInterest,
                'total_amount' => $totalAmount,
                'payment_frequency' => 'annual',
            ];
        }

        // Calculo mensual (por defecto) - metodo frances con periodos mensuales
        $monthlyPayment = self::calculateMonthlyPayment($principal, $interestRate, $termMonths);
        $totalInterest = self::calculateTotalInterest($monthlyPayment, $termMonths, $principal);
        $totalAmount = $principal + $totalInterest;

        return [
            'vehicle_price' => $vehiclePrice,
            'down_payment' => $downPayment,
            'down_payment_percent' => round($downPaymentPercent, 2),
            'financed_amount' => $principal,
            'term_months' => $termMonths,
            'interest_rate' => $interestRate,
            'monthly_payment' => $monthlyPayment,
            'total_interest' => $totalInterest,
            'total_amount' => $totalAmount,
            'payment_frequency' => 'monthly',
        ];
    }

    /**
     * Formato de precio
     */
    public function getFormattedMonthlyPaymentAttribute()
    {
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . number_format($this->monthly_payment, 0, ',', '.');
    }
}
