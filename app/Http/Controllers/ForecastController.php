<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use App\Http\Controllers\Controller;

class ForecastController extends Controller
{
    public function fetch(Request $request)
    {
        $type = $request->query('type', 'fnd');
        $url = "https://data.weather.gov.hk/weatherAPI/opendata/weather.php?dataType={$type}&lang=en";
        $response = Http::get($url);

        return $response->json();
    }

    public function generate(Request $request){
        $type = $request->query('type', '9day');

        $url = $type === 'current'
            ? 'https://data.weather.gov.hk/weatherAPI/opendata/weather.php?dataType=rhrread&lang=en'
            : 'https://data.weather.gov.hk/weatherAPI/opendata/weather.php?dataType=fnd&lang=en';

        $response = Http::get($url);
        $data = $response->json();

        $filename = "forecast_{$type}_" . now()->format('Ymd_His') . ".csv";
        $csv = $this->convertToCSV($data, $type);

        // Just upload it — let your bucket policy handle public visibility
        Storage::disk('s3')->put($filename, $csv);

        $s3_url = Storage::disk('s3')->url($filename);

        $report = Report::create([
            'type' => $type,
            'filename' => $filename,
            's3_url' => $s3_url,
        ]);

        return response()->json($report);
    }

    private function convertToCSV($data, $type){
        $lines = [];

        if ($type === '9day') {
            $lines[] = "Date,Weather,Max Temp (°F),Min Temp (°F)";
            foreach ($data['weatherForecast'] as $day) {
                // Convert the forecast date format to YYYY-MM-DD
                $forecastDate = \Carbon\Carbon::createFromFormat('Ymd', $day['forecastDate'])->format('Y-m-d');

                $maxC = $day['forecastMaxtemp']['value'];
                $minC = $day['forecastMintemp']['value'];

                $maxF = round(($maxC * 9 / 5) + 32, 1);
                $minF = round(($minC * 9 / 5) + 32, 1);

                $lines[] = "{$forecastDate},\"{$day['forecastWeather']}\",{$maxF},{$minF}";
            }
        } else {
            $lines[] = "Place,Temperature (°F),Humidity (%)";
            foreach ($data['temperature']['data'] as $i => $entry) {
                $tempC = $entry['value'];
                $tempF = round(($tempC * 9 / 5) + 32, 1);
                $humidity = $data['humidity']['data'][$i]['value'] ?? 'N/A';
                $lines[] = "{$entry['place']},{$tempF},$humidity";
            }
        }

        return implode("\n", $lines);
    }



    public function logs(Request $request){
        $logs = Report::orderBy('created_at', 'desc')->paginate(10); 
        return response()->json($logs);
    }

}
