@extends('layouts.main')

@section('title', 'Dashboard Monitoring')

@section('content')
    <div class="container mx-auto px-4 sm:px-8 py-6" style="margin-left: 15px;">
        <h1 class="text-2xl font-semibold text-[#1C2955]" style="font-family: 'Poppins', sans-serif;">Dashboard Monitoring</h1>

        <div class="mt-4 p-6 bg-white shadow-md rounded-lg">
            <!-- Form Upload Data -->
            <form action="{{ route('upload.data') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                @csrf
                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                    <input type="file" name="file" class="border p-2 rounded-lg" />
                    <button type="submit" class="flex h-12 px-4 py-2 justify-center items-center gap-2 rounded-lg bg-[#F4F4F4] text-[#1C2955] hover:bg-[#1C2955] hover:text-white transition-all">
                        Upload Data
                    </button>
                </div>
            </form>

            <!-- Data Table -->
            <div class="overflow-auto mt-6">
                @if(isset($data) && $data->isNotEmpty())
                    <table class="min-w-full border border-gray-300 text-sm">
                        <thead class="bg-gray-100">
                            <tr class="border-b">
                                @foreach ([
                                    'NIPNAS', 'Corporate Customer', 'Segmen', 'TREG HO', 'GROUP KONGLO', 'NIK_AM_Jan', 'NAMA_AM_Jan',
                                    'NIK_AM_Feb', 'NAMA_AM_Feb', 'NIK_AM_Mar', 'NAMA_AM_Mar', 'NIK_AM_Apr', 'NAMA_AM_Apr', 'NIK_AM_Mei',
                                    'NAMA_AM_Mei', 'NIK_AM_Jun', 'NAMA_AM_Jun', 'NIK_AM_Jul', 'NAMA_AM_Jul', 'NIK_AM_Ags', 'NAMA_AM_Ags',
                                    'NIK_AM_Sep', 'NAMA_AM_Sep', 'NIK_AM_Okt', 'NAMA_AM_Okt', 'NIK_AM_Nov', 'NAMA_AM_Nov', 'NIK_AM_Des',
                                    'NAMA_AM_Des', 'PROPORSI', 'WITEL HO', 'WITEL ID', 'DIVISI', 'AREA', 'NIK MGR AREA', 'MGR AREA',
                                    'T_Sust_Jan', 'T_Sust_Feb', 'T_Sust_Mar', 'T_Sust_Apr', 'T_Sust_Mei', 'T_Sust_Jun', 'T_Sust_Jul',
                                    'T_Sust_Ags', 'T_Sust_Sep', 'T_Sust_Okt', 'T_Sust_Nov', 'T_Sust_Des', 'T_Total_Sustain',
                                    'T_Scal_Jan', 'T_Scal_Feb', 'T_Scal_Mar', 'T_Scal_Apr', 'T_Scal_Mei', 'T_Scal_Jun', 'T_Scal_Jul',
                                    'T_Scal_Ags', 'T_Scal_Sep', 'T_Scal_Okt', 'T_Scal_Nov', 'T_Scal_Des', 'T_Total_Scaling',
                                    'T_Revenue_Jan', 'T_Revenue_Feb', 'T_Revenue_Mar', 'T_Revenue_Apr', 'T_Revenue_Mei', 'T_Revenue_Jun',
                                    'T_Revenue_Jul', 'T_Revenue_Ags', 'T_Revenue_Sep', 'T_Revenue_Okt', 'T_Revenue_Nov', 'T_Revenue_Des',
                                    'Total_Target Revenue', 'T_NGTMA_Jan', 'T_NGTMA_Feb', 'T_NGTMA_Mar', 'T_NGTMA_Apr', 'T_NGTMA_Mei',
                                    'T_NGTMA_Jun', 'T_NGTMA_Jul', 'T_NGTMA_Ags', 'T_NGTMA_Sep', 'T_NGTMA_Okt', 'T_NGTMA_Nov', 'T_NGTMA_Des',
                                    'Total_Target NGTMA', 'Est. Sust. Jan POTS', 'Est. Sust. Feb POTS', 'Est. Sust. Mar POTS',
                                    'Est. Sust. Apr POTS', 'Est. Sust. Mei POTS', 'Est. Sust. Jun POTS', 'Est. Sust. Jul POTS',
                                    'Est. Sust. Agu POTS', 'Est. Sust. Sep POTS', 'Est. Sust. Okt POTS', 'Est. Sust. Nov POTS',
                                    'Est. Sust. Des POTS', 'TOTAL Est. Sust POTS', 'Est. Sust. Jan NP', 'Est. Sust. Feb NP',
                                    'Est. Sust. Mar NP', 'Est. Sust. Apr NP', 'Est. Sust. Mei NP', 'Est. Sust. Jun NP', 'Est. Sust. Jul NP',
                                    'Est. Sust. Agu NP', 'Est. Sust. Sep NP', 'Est. Sust. Okt NP', 'Est. Sust. Nov NP', 'Est. Sust. Des NP',
                                    'TOTAL Est. Sust NP', 'Real_Jan', 'Real_Feb', 'Real_Mar', 'Real_Apr', 'Real_Mei', 'Real_Jun',
                                    'Real_Jul', 'Real_Ags', 'Real_Sep', 'Real_Okt', 'Real_Nov', 'Real_Des', 'Real Total', 'Real_NGTMA Jan',
                                    'Real_NGTMA Feb', 'Real_NGTMA Mar', 'Real_NGTMA Apr', 'Real_NGTMA Mei', 'Real_NGTMA Jun', 'Real_NGTMA Jul',
                                    'Real_NGTMA Ags', 'Real_NGTMA Sept', 'Real_NGTMA Okt', 'Real_NGTMA Nov', 'Real_NGTMA Des', 'Real_NGTMA',
                                    'BC_Jan', 'BC_Feb', 'BC_Mar', 'BC_Apr', 'BC_Mei', 'BC_Jun', 'BC_Jul', 'BC_Agu', 'BC_Sep', 'BC_Okt',
                                    'BC_Nov', 'BC_Des', 'TOTAL_BC'
                                ] as $column)
                                    <th class="px-4 py-2 text-center border">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $row)
                                <tr class="border-b">
                                    @foreach ($row->toArray() as $cell)
                                        <td class="px-4 py-2 border text-center">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-center text-gray-600">Belum ada data yang masuk. Silakan unggah file untuk melihat data.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
