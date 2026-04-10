<?php

namespace App\Services;

use Carbon\Carbon;

class CreditCalculator
{
    public const DEPOSIT_PERCENT = 20;

    /**
     * Calculate credit terms and generate amortization schedule.
     *
     * @param  int    $price      Product price in UGX (integer)
     * @param  int    $months     Credit term: 3 or 6
     * @param  float  $apr        Annual percentage rate: 0, 5, 9.99, 14.99
     * @return array{
     *   deposit: int,
     *   financed_amount: int,
     *   monthly_payment: int,
     *   total_payable: int,
     *   total_interest: int,
     *   schedule: array
     * }
     */
    public function calculate(int $price, int $months, float $apr): array
    {
        $deposit = (int) round($price * self::DEPOSIT_PERCENT / 100);
        $financed = $price - $deposit;

        if ($apr == 0 || $months == 0) {
            $monthlyPayment = (int) round($financed / $months);
            $schedule = $this->buildZeroInterestSchedule($financed, $months, $monthlyPayment);
        } else {
            $monthlyRate = $apr / 100 / 12;
            $factor = pow(1 + $monthlyRate, $months);
            $monthlyPayment = (int) round($financed * ($monthlyRate * $factor) / ($factor - 1));
            $schedule = $this->buildAmortizationSchedule($financed, $months, $monthlyRate, $monthlyPayment);
        }

        $totalPayable = $deposit + array_sum(array_column($schedule, 'amount'));
        $totalInterest = array_sum(array_column($schedule, 'interest'));

        return [
            'deposit'          => $deposit,
            'financed_amount'  => $financed,
            'monthly_payment'  => $monthlyPayment,
            'total_payable'    => $totalPayable,
            'total_interest'   => $totalInterest,
            'schedule'         => $schedule,
        ];
    }

    private function buildAmortizationSchedule(int $principal, int $months, float $rate, int $payment): array
    {
        $schedule = [];
        $balance = $principal;
        $startDate = Carbon::now()->addMonth();

        for ($i = 1; $i <= $months; $i++) {
            $interest = (int) round($balance * $rate);
            $principalPart = $payment - $interest;

            // Last payment: clear remaining balance
            if ($i === $months) {
                $principalPart = $balance;
                $payment = $balance + $interest;
            }

            $balance -= $principalPart;
            if ($balance < 0) {
                $balance = 0;
            }

            $schedule[] = [
                'installment'       => $i,
                'due_date'          => $startDate->copy()->addMonths($i - 1)->toDateString(),
                'amount'            => $payment,
                'principal'         => $principalPart,
                'interest'          => $interest,
                'remaining_balance' => $balance,
                'paid'              => false,
                'paid_date'         => null,
                'paid_amount'       => 0,
            ];
        }

        return $schedule;
    }

    private function buildZeroInterestSchedule(int $principal, int $months, int $payment): array
    {
        $schedule = [];
        $balance = $principal;
        $startDate = Carbon::now()->addMonth();

        for ($i = 1; $i <= $months; $i++) {
            $actualPayment = ($i === $months) ? $balance : $payment;
            $balance -= $actualPayment;
            if ($balance < 0) {
                $balance = 0;
            }

            $schedule[] = [
                'installment'       => $i,
                'due_date'          => $startDate->copy()->addMonths($i - 1)->toDateString(),
                'amount'            => $actualPayment,
                'principal'         => $actualPayment,
                'interest'          => 0,
                'remaining_balance' => $balance,
                'paid'              => false,
                'paid_date'         => null,
                'paid_amount'       => 0,
            ];
        }

        return $schedule;
    }
}
