<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;

class RLEGSChart
{
    public function buildLineChart(): LarapexChart
    {
        return (new LarapexChart)
            ->setTitle('Revenue Witel')
            ->setType('line') 
            ->setXAxis(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Juni', 'Juli','Ags', 'Sept', 'Okt', 'Nov', 'Des'])
            ->setHeight(280)
            ->setDataset([
                [
                    'name' => 'Real Revenue',
                    'data' => [200000000, 400000000, 600000000, 800000000, 1000000000]
                ],
                [
                    'name' => 'Target Revenue',
                    'data' => [300000000, 200000000, 500000000, 900000000, 800000000]
                ],
            ]);
    }

    public function buildComparisonBarChart(): LarapexChart
    {
        return (new LarapexChart)
            ->setTitle('San Francisco vs Boston')
            ->setSubtitle('Wins during season 2021')
            ->setType('bar')
            ->setHeight(280)
            ->setDataset([
                [
                    'name' => 'San Francisco',
                    'data' => [6, 9, 3, 4, 10, 8]
                ],
                [
                    'name' => 'Boston',
                    'data' => [7, 3, 8, 2, 6, 4]
                ]
            ])
            ->setXAxis(['January', 'February', 'March', 'April', 'May', 'June'])
            ->setGrid(false);
    }

    public function buildDonutChart(): LarapexChart
    {
        return (new LarapexChart)
            ->setTitle('Top 3 Scorers of the Team')
            ->setSubtitle('Season 2021')
            ->setType('donut') // Menentukan tipe chart
            ->setDataset([20, 24, 30]) // Menggunakan setDataset() yang benar
            ->setLabels(['Player 7', 'Player 10', 'Player 9']);
    }
    
}