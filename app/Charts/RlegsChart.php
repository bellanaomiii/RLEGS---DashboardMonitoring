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
