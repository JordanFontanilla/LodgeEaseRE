<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Booking;
use App\Models\Room;
use App\Models\ActivityLog;
use App\Services\FirebaseService;
use Carbon\Carbon;

class BusinessAnalyticsController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Display the business analytics dashboard
     */
    public function index()
    {
        try {
            $analytics = $this->getAnalyticsData();
            return view('admin.analytics.analytics', compact('analytics'));
        } catch (\Exception $e) {
            \Log::error('Analytics index error: ' . $e->getMessage());
            return view('admin.analytics.analytics', ['analytics' => $this->getEmptyAnalytics()]);
        }
    }

    /**
     * Get analytics data for API endpoints
     */
    public function getAnalyticsData()
    {
        try {
            // Get comprehensive analytics data from Firebase
            $firebaseData = $this->firebaseService->getAnalyticsData();
            
            // Transform Firebase data to include KPIs structure expected by the template
            return $this->transformFirebaseDataForTemplate($firebaseData);
        } catch (\Exception $e) {
            \Log::error('Failed to get analytics data: ' . $e->getMessage());
            return $this->getEmptyAnalytics();
        }
    }

    /**
     * Transform Firebase data to include KPIs structure for template compatibility
     */
    private function transformFirebaseDataForTemplate($firebaseData)
    {
        // Extract summary stats from Firebase data
        $summaryStats = $firebaseData['summary_stats'] ?? [];
        
        // Create KPIs structure expected by the template
        $kpis = [
            'total_sales' => [
                'value' => $summaryStats['total_revenue'] ?? 0,
                'period' => 'Last 30 days',
                'change' => 0 // Could be calculated if we have historical data
            ],
            'current_occupancy' => [
                'value' => $summaryStats['current_occupancy_rate'] ?? 0,
                'period' => 'Current',
                'target' => 85 // Target occupancy rate
            ],
            'avg_sales_per_booking' => [
                'value' => $summaryStats['average_booking_value'] ?? 0,
                'period' => 'Average per booking',
                'change' => 0 // Could be calculated if we have historical data
            ],
            'seasonal_score' => [
                'value' => $this->calculateSeasonalScore($summaryStats),
                'period' => 'Current season performance',
                'status' => $this->getSeasonalStatus($summaryStats)
            ]
        ];
        
        // Add KPIs to the Firebase data structure
        $firebaseData['kpis'] = $kpis;
        
        // Generate booking trends if we have booking data
        if (isset($firebaseData['bookings']) && is_array($firebaseData['bookings'])) {
            $bookings = collect($firebaseData['bookings']);
            $firebaseData['booking_trends'] = $this->getBookingTrends($bookings);
        } elseif (!isset($firebaseData['booking_trends'])) {
            // Use empty booking trends structure if not present
            $firebaseData['booking_trends'] = [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Monthly Bookings',
                        'data' => [0],
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ],
                'insufficient_data' => true,
                'message' => 'At least 1 month of booking data is required to display booking trends. Start by processing bookings to see meaningful trend analysis.'
            ];
        }
        
        return $firebaseData;
    }

    /**
     * Calculate seasonal score based on current performance
     */
    private function calculateSeasonalScore($summaryStats)
    {
        $occupancyRate = $summaryStats['current_occupancy_rate'] ?? 0;
        $currentMonth = now()->month;
        
        // Seasonal expectations (higher scores for peak seasons)
        $seasonalMultipliers = [
            12 => 1.2, 1 => 1.2, 2 => 0.8, // Winter (holiday season high, post-holiday low)
            3 => 0.9, 4 => 1.0, 5 => 1.1,   // Spring
            6 => 1.2, 7 => 1.3, 8 => 1.2,   // Summer (peak season)
            9 => 1.0, 10 => 1.0, 11 => 1.1  // Fall
        ];
        
        $expectedMultiplier = $seasonalMultipliers[$currentMonth] ?? 1.0;
        $baseExpectation = 70; // Base expected occupancy
        $expectedOccupancy = $baseExpectation * $expectedMultiplier;
        
        $score = $expectedOccupancy > 0 ? min(100, ($occupancyRate / $expectedOccupancy) * 100) : 0;
        return round($score, 2);
    }

    /**
     * Get seasonal status description
     */
    private function getSeasonalStatus($summaryStats)
    {
        $occupancyRate = $summaryStats['current_occupancy_rate'] ?? 0;
        
        if ($occupancyRate >= 80) {
            return 'Excellent performance';
        } elseif ($occupancyRate >= 60) {
            return 'Good performance';
        } elseif ($occupancyRate >= 40) {
            return 'Average performance';
        } else {
            return 'Below expectations';
        }
    }

    /**
     * Get real-time analytics data via API
     */
    public function getAnalyticsApi()
    {
        try {
            $analytics = $this->firebaseService->getAnalyticsData();
            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            \Log::error('Analytics API error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load analytics data',
                'data' => $this->getEmptyAnalytics()
            ], 500);
        }
    }

    /**
     * Get empty analytics data structure
     */
    private function getEmptyAnalytics()
    {
        return [
            'kpis' => [
                'total_sales' => [
                    'value' => 0,
                    'period' => 'Last 30 days',
                    'change' => 0
                ],
                'current_occupancy' => [
                    'value' => 0,
                    'period' => 'Current',
                    'target' => 85
                ],
                'avg_sales_per_booking' => [
                    'value' => 0,
                    'period' => 'Average per booking',
                    'change' => 0
                ],
                'seasonal_score' => [
                    'value' => 0,
                    'period' => 'Current season performance',
                    'status' => 'No data available'
                ]
            ],
            'occupancy_rate' => [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Overall Occupancy Rate (%)',
                        'data' => [0],
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ],
                'insufficient_data' => true,
                'message' => 'At least 1 month of booking data is required to display occupancy trends. Start by processing bookings to see meaningful analytics.'
            ],
            'revenue_analytics' => [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Monthly Revenue (₱)',
                        'data' => [0],
                        'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                        'borderColor' => 'rgb(16, 185, 129)',
                        'borderWidth' => 2
                    ]
                ],
                'insufficient_data' => true,
                'message' => 'No revenue data available'
            ],
            'booking_sources' => [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'data' => [1],
                        'backgroundColor' => ['rgba(107, 114, 128, 0.8)'],
                        'borderWidth' => 2
                    ]
                ],
                'insufficient_data' => true,
                'message' => 'No booking source data available'
            ],
            'room_performance' => [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Total Bookings',
                        'data' => [0],
                        'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                        'yAxisID' => 'y'
                    ]
                ],
                'insufficient_data' => true,
                'message' => 'No room performance data available'
            ],
            'seasonal_trends' => [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Bookings by Season',
                        'data' => [0],
                        'backgroundColor' => ['rgba(107, 114, 128, 0.8)'],
                        'borderWidth' => 2
                    ]
                ],
                'insufficient_data' => true,
                'message' => 'No seasonal trend data available'
            ],
            'guest_demographics' => [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Number of Bookings',
                        'data' => [0],
                        'backgroundColor' => 'rgba(168, 85, 247, 0.8)',
                        'borderColor' => 'rgb(168, 85, 247)',
                        'borderWidth' => 2
                    ]
                ],
                'insufficient_data' => true,
                'message' => 'No guest data available'
            ],
            'booking_trends' => [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Monthly Bookings',
                        'data' => [0],
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ],
                'insufficient_data' => true,
                'message' => 'At least 1 month of booking data is required to display booking trends. Start by processing bookings to see meaningful trend analysis.'
            ],
            'total_sales' => [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Daily Sales (₱)',
                        'data' => [0],
                        'borderColor' => 'rgb(16, 185, 129)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ],
                'insufficient_data' => true,
                'message' => 'No sales data available'
            ],
            'summary_stats' => [
                'total_bookings' => 0,
                'total_revenue' => 0,
                'current_occupancy_rate' => 0,
                'average_booking_value' => 0,
                'occupied_rooms' => 0,
                'total_rooms' => 0,
                'available_rooms' => 0
            ]
        ];
    }

    private function calculateKPIs($bookings, $rooms, $today, $lastMonth)
    {
        // Filter bookings for last 30 days
        $recentBookings = $bookings->filter(function($booking) use ($lastMonth) {
            return Carbon::parse($booking['created_at'])->gte($lastMonth);
        });

        // Calculate total sales
        $totalSales = $recentBookings->where('payment_status', 'paid')->sum('total_amount');
        
        // Calculate previous month sales for comparison
        $previousMonth = $lastMonth->copy()->subMonth();
        $previousBookings = $bookings->filter(function($booking) use ($previousMonth, $lastMonth) {
            $bookingDate = Carbon::parse($booking['created_at']);
            return $bookingDate->gte($previousMonth) && $bookingDate->lt($lastMonth);
        });
        $previousSales = $previousBookings->where('payment_status', 'paid')->sum('total_amount');
        
        $salesChange = $previousSales > 0 ? (($totalSales - $previousSales) / $previousSales) : 0;

        // Calculate occupancy
        $totalRooms = $rooms->count();
        $occupiedRooms = $rooms->where('status', 'occupied')->count();
        $occupancyRate = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0;

        // Calculate average sales per booking
        $avgSalesPerBooking = $recentBookings->count() > 0 ? $totalSales / $recentBookings->count() : 0;

        // Calculate total bookings for period
        $totalBookings = $recentBookings->count();
        $previousTotalBookings = $previousBookings->count();
        $bookingsChange = $previousTotalBookings > 0 ? (($totalBookings - $previousTotalBookings) / $previousTotalBookings) : 0;

        // Calculate seasonal performance score
        $currentMonth = $today->month;
        $seasonalMultipliers = [
            12 => 1.2, 1 => 1.2, 2 => 0.8, // Winter (holiday season high, post-holiday low)
            3 => 0.9, 4 => 1.0, 5 => 1.1,   // Spring
            6 => 1.2, 7 => 1.3, 8 => 1.2,   // Summer (peak season)
            9 => 1.0, 10 => 1.0, 11 => 1.1  // Fall
        ];
        $expectedMultiplier = $seasonalMultipliers[$currentMonth] ?? 1.0;
        $actualPerformance = $totalRooms > 0 ? $occupancyRate / 100 : 0;
        $seasonalScore = $expectedMultiplier > 0 ? ($actualPerformance / $expectedMultiplier) * 100 : 0;

        return [
            'total_sales' => [
                'value' => $totalSales,
                'currency' => 'PHP',
                'period' => 'Last 30 Days',
                'change' => $salesChange,
                'trend' => $salesChange >= 0 ? 'up' : 'down'
            ],
            'current_occupancy' => [
                'value' => round($occupancyRate, 2),
                'unit' => '%',
                'period' => 'Today',
                'target' => 90,
                'status' => $occupancyRate >= 80 ? 'high' : ($occupancyRate >= 50 ? 'medium' : 'low')
            ],
            'avg_sales_per_booking' => [
                'value' => round($avgSalesPerBooking, 2),
                'currency' => 'PHP',
                'period' => 'Last 30 Days',
                'change' => $salesChange,
                'trend' => $salesChange >= 0 ? 'up' : 'down'
            ],
            'total_bookings' => [
                'value' => $totalBookings,
                'unit' => 'bookings',
                'period' => 'Last 30 Days',
                'change' => $bookingsChange,
                'trend' => $bookingsChange >= 0 ? 'up' : 'down'
            ],
            'seasonal_score' => [
                'value' => round($seasonalScore, 2),
                'period' => 'Current Month',
                'status' => $seasonalScore >= 90 ? 'excellent' : ($seasonalScore >= 70 ? 'good' : ($seasonalScore >= 50 ? 'average' : 'needs_improvement')),
                'trend' => $seasonalScore >= 80 ? 'up' : 'down'
            ]
        ];
    }

    private function getRevenueData($bookings)
    {
        // Group bookings by month for the last 12 months
        $revenueByMonth = [];
        $months = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $months[] = $month->format('M Y');
            
            $monthlyRevenue = $bookings->filter(function($booking) use ($month) {
                $bookingDate = Carbon::parse($booking['created_at']);
                return $bookingDate->year === $month->year && 
                       $bookingDate->month === $month->month &&
                       $booking['payment_status'] === 'paid';
            })->sum('total_amount');
            
            $revenueByMonth[] = $monthlyRevenue;
        }

        return [
            'monthly' => [
                'labels' => $months,
                'data' => $revenueByMonth
            ],
            'total_ytd' => $bookings->where('payment_status', 'paid')
                                  ->filter(function($booking) {
                                      return Carbon::parse($booking['created_at'])->year === Carbon::now()->year;
                                  })->sum('total_amount')
        ];
    }

    private function getOccupancyData($bookings, $rooms)
    {
        try {
            $totalRooms = $rooms->count();
            
            if ($totalRooms == 0) {
                return $this->getInsufficientOccupancyData('No rooms available to calculate occupancy rate.');
            }
            
            // Get occupancy data for the last 6 months (minimum 1 month requirement)
            $monthlyOccupancy = [];
            $labels = [];
            $currentDate = now();
            
            // Generate last 6 months of data
            for ($i = 5; $i >= 0; $i--) {
                $monthDate = $currentDate->copy()->subMonths($i);
                $monthLabel = $monthDate->format('M Y');
                
                // Get bookings for this month
                $monthBookings = $bookings->filter(function($booking) use ($monthDate) {
                    $checkIn = \Carbon\Carbon::parse($booking['check_in_date']);
                    $checkOut = \Carbon\Carbon::parse($booking['check_out_date']);
                    
                    // Check if booking overlaps with this month
                    $monthStart = $monthDate->copy()->startOfMonth();
                    $monthEnd = $monthDate->copy()->endOfMonth();
                    
                    return $booking['status'] === 'confirmed' &&
                           $checkIn->lte($monthEnd) && $checkOut->gte($monthStart);
                });
                
                // Calculate average occupancy for the month
                $daysInMonth = $monthDate->daysInMonth;
                $totalOccupancyDays = 0;
                
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = $monthDate->copy()->day($day);
                    
                    $occupiedCount = $monthBookings->filter(function($booking) use ($date) {
                        $checkIn = \Carbon\Carbon::parse($booking['check_in_date']);
                        $checkOut = \Carbon\Carbon::parse($booking['check_out_date']);
                        return $checkIn->lte($date) && $checkOut->gt($date);
                    })->count();
                    
                    $totalOccupancyDays += min($occupiedCount, $totalRooms);
                }
                
                $averageOccupancy = $totalRooms > 0 ? 
                    round(($totalOccupancyDays / ($daysInMonth * $totalRooms)) * 100, 2) : 0;
                
                $labels[] = $monthLabel;
                $monthlyOccupancy[] = $averageOccupancy;
            }
            
            // Check if we have at least 1 month of meaningful data
            $totalBookings = $bookings->where('status', 'confirmed')->count();
            $monthsWithData = count(array_filter($monthlyOccupancy));
            
            if ($totalBookings < 1 || $monthsWithData < 1) {
                return $this->getInsufficientOccupancyData('At least 1 month of booking data is required to display occupancy trends. Start by processing bookings to see meaningful analytics.');
            }
            
            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Overall Occupancy Rate (%)',
                        'data' => $monthlyOccupancy,
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ],
                'sufficient_data' => true,
                'months_of_data' => $monthsWithData,
                'total_bookings' => $totalBookings
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error generating occupancy data: ' . $e->getMessage());
            return $this->getInsufficientOccupancyData('Error generating occupancy data. Please try again later.');
        }
    }

    private function getInsufficientOccupancyData($message)
    {
        return [
            'labels' => ['No Data'],
            'datasets' => [
                [
                    'label' => 'Overall Occupancy Rate (%)',
                    'data' => [0],
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.4,
                    'fill' => true
                ]
            ],
            'insufficient_data' => true,
            'message' => $message
        ];
    }

    private function getBookingTrends($bookings)
    {
        try {
            // Get bookings from the last 12 months grouped by month
            $monthlyBookings = [];
            $labels = [];
            $currentDate = now();
            
            // Generate last 12 months of data
            for ($i = 11; $i >= 0; $i--) {
                $monthDate = $currentDate->copy()->subMonths($i);
                $monthKey = $monthDate->format('Y-m');
                $monthLabel = $monthDate->format('M Y');
                
                $monthBookings = $bookings->filter(function($booking) use ($monthDate) {
                    $bookingDate = \Carbon\Carbon::parse($booking['created_at']);
                    return $bookingDate->year == $monthDate->year && 
                           $bookingDate->month == $monthDate->month;
                });
                
                $labels[] = $monthLabel;
                $monthlyBookings[] = $monthBookings->count();
            }
            
            // Check if we have at least 1 month of meaningful data
            $totalBookings = array_sum($monthlyBookings);
            $monthsWithData = count(array_filter($monthlyBookings));
            
            if ($totalBookings < 1 || $monthsWithData < 1) {
                return [
                    'labels' => ['No Data'],
                    'datasets' => [
                        [
                            'label' => 'Monthly Bookings',
                            'data' => [0],
                            'borderColor' => 'rgb(59, 130, 246)',
                            'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                            'tension' => 0.4,
                            'fill' => true
                        ]
                    ],
                    'insufficient_data' => true,
                    'message' => 'At least 1 month of booking data is required to display booking trends. Start by processing bookings to see meaningful trend analysis.'
                ];
            }
            
            // Group bookings by status for additional analysis
            $confirmedBookings = [];
            $pendingBookings = [];
            $cancelledBookings = [];
            
            for ($i = 11; $i >= 0; $i--) {
                $monthDate = $currentDate->copy()->subMonths($i);
                
                $monthBookings = $bookings->filter(function($booking) use ($monthDate) {
                    $bookingDate = \Carbon\Carbon::parse($booking['created_at']);
                    return $bookingDate->year == $monthDate->year && 
                           $bookingDate->month == $monthDate->month;
                });
                
                $confirmedBookings[] = $monthBookings->where('status', 'confirmed')->count();
                $pendingBookings[] = $monthBookings->where('status', 'pending')->count();
                $cancelledBookings[] = $monthBookings->where('status', 'cancelled')->count();
            }
            
            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Total Bookings',
                        'data' => $monthlyBookings,
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ],
                    [
                        'label' => 'Confirmed Bookings',
                        'data' => $confirmedBookings,
                        'borderColor' => 'rgb(16, 185, 129)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.4,
                        'fill' => false
                    ],
                    [
                        'label' => 'Cancelled Bookings',
                        'data' => $cancelledBookings,
                        'borderColor' => 'rgb(239, 68, 68)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'tension' => 0.4,
                        'fill' => false
                    ]
                ],
                'sufficient_data' => true,
                'months_of_data' => $monthsWithData,
                'total_bookings' => $totalBookings
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error generating booking trends: ' . $e->getMessage());
            
            return [
                'labels' => ['Error'],
                'datasets' => [
                    [
                        'label' => 'Monthly Bookings',
                        'data' => [0],
                        'borderColor' => 'rgb(239, 68, 68)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)'
                    ]
                ],
                'insufficient_data' => true,
                'message' => 'Error generating booking trends data. Please try again later.'
            ];
        }
    }

    private function getRoomPerformance($bookings, $rooms)
    {
        $roomPerformance = [];
        
        foreach ($rooms as $room) {
            $roomBookings = $bookings->where('room_id', $room['id']);
            $revenue = $roomBookings->where('payment_status', 'paid')->sum('total_amount');
            $bookingCount = $roomBookings->count();
            
            $roomPerformance[] = [
                'room_number' => $room['number'],
                'room_type' => $room['type'],
                'revenue' => $revenue,
                'bookings' => $bookingCount,
                'avg_rate' => $bookingCount > 0 ? $revenue / $bookingCount : 0,
                'status' => $room['status']
            ];
        }

        // Sort by revenue descending
        usort($roomPerformance, function($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        return array_slice($roomPerformance, 0, 10); // Top 10 rooms
    }

    private function getTopRoomTypes($bookings, $rooms)
    {
        $roomTypes = $rooms->groupBy('type');
        $typePerformance = [];

        foreach ($roomTypes as $type => $roomsOfType) {
            $typeBookings = $bookings->filter(function($booking) use ($roomsOfType) {
                return $roomsOfType->pluck('id')->contains($booking['room_id']);
            });

            $typePerformance[] = [
                'type' => $type,
                'bookings' => $typeBookings->count(),
                'revenue' => $typeBookings->where('payment_status', 'paid')->sum('total_amount'),
                'rooms_count' => $roomsOfType->count()
            ];
        }

        // Sort by revenue descending
        usort($typePerformance, function($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        return $typePerformance;
    }

    /**
     * Export analytics data
     */
    public function exportData(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:json,csv',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);

        try {
            $analytics = $this->getAnalyticsData();
            
            // Log the export
            ActivityLog::log(
                'analytics_exported',
                'Exported analytics data in ' . $validated['format'] . ' format',
                session('admin_id')
            );

            if ($validated['format'] === 'json') {
                return response()->json($analytics);
            } else {
                // Convert to CSV format
                $csvData = $this->convertToCsv($analytics);
                return response($csvData)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="analytics_' . date('Y-m-d') . '.csv"');
            }
        } catch (\Exception $e) {
            \Log::error('Analytics export error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export analytics data'], 500);
        }
    }

    private function convertToCsv($analytics)
    {
        $csv = "Analytics Report - " . date('Y-m-d H:i:s') . "\n\n";
        
        // KPIs section
        $csv .= "Key Performance Indicators\n";
        $csv .= "Metric,Value,Period,Change\n";
        foreach ($analytics['kpis'] as $key => $kpi) {
            $csv .= "{$key},{$kpi['value']},{$kpi['period']},{$kpi['change']}\n";
        }
        
        $csv .= "\nRoom Performance\n";
        $csv .= "Room Number,Room Type,Revenue,Bookings,Average Rate,Status\n";
        foreach ($analytics['room_performance'] as $room) {
            $csv .= "{$room['room_number']},{$room['room_type']},{$room['revenue']},{$room['bookings']},{$room['avg_rate']},{$room['status']}\n";
        }
        
        return $csv;
    }

    /**
     * Get real-time dashboard data
     */
    public function getDashboardData()
    {
        try {
            $stats = [
                'today_checkins' => Booking::getTodaysCheckIns()->count(),
                'today_checkouts' => Booking::getTodaysCheckOuts()->count(),
                'pending_payments' => Booking::getPendingBookings()->count(),
                'available_rooms' => Room::getAvailableRooms()->count(),
                'occupied_rooms' => Room::getOccupiedRooms()->count(),
                'maintenance_rooms' => Room::getMaintenanceRooms()->count()
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            \Log::error('Dashboard data error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get dashboard data'], 500);
        }
    }
}
