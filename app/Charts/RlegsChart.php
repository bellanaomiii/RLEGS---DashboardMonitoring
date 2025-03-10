<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;

class RLEGSChart
{
    public function buildLineChart(): LarapexChart
    {
        return (new LarapexChart)
            ->setTitle('Revenue Witel')
            ->setSubtitle('Total Revenue Witel')
            ->setType('line') 
            ->setXAxis(['Jan', 'Feb', 'Mar', 'Apr'])
            ->setHeight(280)
            ->setDataset([
                [
                    'name' => 'Pengguna Baru',
                    'data' => [5, 15, 10, 20]
                ],
                [
                    'name' => 'Pengguna Lama',
                    'data' => [10, 30, 17, 15]
                ]
            ]);
    }

    public function buildRadialChart(): LarapexChart
    {
        return (new LarapexChart)
            ->setTitle('Passing effectiveness')
            ->setSubtitle('Barcelona city vs Madrid sports')
            ->setType('radialBar')
            ->setHeight(280)
            ->setDataset([75, 60])
            ->setLabels(['Barcelona city', 'Madrid sports'])
            ->setColors(['#D32F2F', '#03A9F4']);
    }
}
