<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Vehicle;

class VehicleQuote extends Model
{
    protected $fillable = [
        'vehicle_id', 'customer_name', 'customer_email', 'customer_phone',
        'vehicle_price', 'down_payment', 'down_payment_percent', 'term_months',
        'interest_rate', 'monthly_payment', 'total_interest', 'total_amount',
        'currency', 'email_sent', 'pdf_generated', 'pdf_path', 'event_name'
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

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Calcular cuota mensual usando fórmula de amortización francesa
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
     * Calcular total de intereses
     */
    public static function calculateTotalInterest($monthlyPayment, $months, $principal)
    {
        return round(($monthlyPayment * $months) - $principal, 2);
    }

    /**
     * Generar cotización completa
     */
    public static function generateQuote($vehiclePrice, $downPayment, $termMonths, $interestRate)
    {
        $principal = $vehiclePrice - $downPayment;
        $monthlyPayment = self::calculateMonthlyPayment($principal, $interestRate, $termMonths);
        $totalInterest = self::calculateTotalInterest($monthlyPayment, $termMonths, $principal);
        $totalAmount = $principal + $totalInterest;
        $downPaymentPercent = ($downPayment / $vehiclePrice) * 100;

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
