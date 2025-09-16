<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RatingsController extends Controller
{
    public function index()
    {
        try {
            // Возвращаем структуру, которую ожидает фронтенд
            return response()->json([
                'weekly' => [],
                'monthly' => [],
                'all_time' => []
            ]);
            
            $now = Carbon::now();
            
            // Недельные рейтинги (уловы за последние 7 дней)
            $weeklyCatches = $this->getCatchesRating($now->copy()->subDays(7), $now);
            
            // Месячные рейтинги (уловы за последние 30 дней)
            $monthlyCatches = $this->getCatchesRating($now->copy()->subDays(30), $now);
            
            // Годовые рейтинги (уловы за последний год)
            $yearlyCatches = $this->getCatchesRating($now->copy()->subYear(), $now);
            
            // Рейтинги по дням рыбалки
            $fishingDays = $this->getFishingDaysRating($now->copy()->subYear(), $now);
            
            // Рейтинги по разнообразию видов рыб
            $speciesDiversity = $this->getSpeciesDiversityRating($now->copy()->subYear(), $now);
            
            // Рейтинги по общему весу улова
            $totalWeight = $this->getTotalWeightRating($now->copy()->subYear(), $now);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'weekly_catches' => $weeklyCatches,
                    'monthly_catches' => $monthlyCatches,
                    'yearly_catches' => $yearlyCatches,
                    'fishing_days' => $fishingDays,
                    'species_diversity' => $speciesDiversity,
                    'total_weight' => $totalWeight,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении рейтингов',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function getCatchesRating($startDate, $endDate)
    {
        $results = DB::table('catch_records')
            ->join('users', 'catch_records.user_id', '=', 'users.id')
            ->whereBetween('catch_records.created_at', [$startDate, $endDate])
            ->select(
                'users.id',
                'users.name',
                'users.username',
                'users.photo_url',
                'users.crown_icon_url',
                'users.is_premium',
                DB::raw('COUNT(catch_records.id) as score')
            )
            ->groupBy('users.id', 'users.name', 'users.username', 'users.photo_url', 'users.crown_icon_url', 'users.is_premium')
            ->orderBy('score', 'desc')
            ->limit(20)
            ->get();
            
        return $this->addRanks($results);
    }
    
    private function getFishingDaysRating($startDate, $endDate)
    {
        $results = DB::table('catch_records')
            ->join('users', 'catch_records.user_id', '=', 'users.id')
            ->whereBetween('catch_records.created_at', [$startDate, $endDate])
            ->select(
                'users.id',
                'users.name',
                'users.username',
                'users.photo_url',
                'users.crown_icon_url',
                'users.is_premium',
                DB::raw('COUNT(DISTINCT DATE(catch_records.created_at)) as score')
            )
            ->groupBy('users.id', 'users.name', 'users.username', 'users.photo_url', 'users.crown_icon_url', 'users.is_premium')
            ->orderBy('score', 'desc')
            ->limit(20)
            ->get();
            
        return $this->addRanks($results);
    }
    
    private function getSpeciesDiversityRating($startDate, $endDate)
    {
        $results = DB::table('catch_records')
            ->join('users', 'catch_records.user_id', '=', 'users.id')
            ->whereBetween('catch_records.created_at', [$startDate, $endDate])
            ->whereNotNull('catch_records.species')
            ->where('catch_records.species', '!=', '')
            ->select(
                'users.id',
                'users.name',
                'users.username',
                'users.photo_url',
                'users.crown_icon_url',
                'users.is_premium',
                DB::raw('COUNT(DISTINCT catch_records.species) as score')
            )
            ->groupBy('users.id', 'users.name', 'users.username', 'users.photo_url', 'users.crown_icon_url', 'users.is_premium')
            ->orderBy('score', 'desc')
            ->limit(20)
            ->get();
            
        return $this->addRanks($results);
    }
    
    private function getTotalWeightRating($startDate, $endDate)
    {
        $results = DB::table('catch_records')
            ->join('users', 'catch_records.user_id', '=', 'users.id')
            ->whereBetween('catch_records.created_at', [$startDate, $endDate])
            ->whereNotNull('catch_records.weight')
            ->where('catch_records.weight', '>', 0)
            ->select(
                'users.id',
                'users.name',
                'users.username',
                'users.photo_url',
                'users.crown_icon_url',
                'users.is_premium',
                DB::raw('ROUND(SUM(catch_records.weight), 2) as score')
            )
            ->groupBy('users.id', 'users.name', 'users.username', 'users.photo_url', 'users.crown_icon_url', 'users.is_premium')
            ->orderBy('score', 'desc')
            ->limit(20)
            ->get();
            
        return $this->addRanks($results);
    }
    
    private function addRanks($results)
    {
        $rank = 1;
        $prevScore = null;
        $actualRank = 1;
        
        return $results->map(function ($item) use (&$rank, &$prevScore, &$actualRank) {
            if ($prevScore !== null && $item->score < $prevScore) {
                $actualRank = $rank;
            }
            
            $item->rank = $actualRank;
            $prevScore = $item->score;
            $rank++;
            
            return $item;
        });
    }
}