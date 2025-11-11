@extends('layouts.app')

@section('title', 'Dashboard - MLOOK')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex">
    <!-- Sidebar -->
    <x-sidebar />

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-72">
        <!-- Top Navigation Bar -->
        <nav class="sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 backdrop-blur-sm bg-opacity-90 dark:bg-opacity-90">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Left Side -->
                    <div class="flex items-center space-x-4">
                        <!-- Mobile Menu Button -->
                        <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <x-icon name="bars-3" class="w-6 h-6" />
                        </button>
                        <!-- Page Title -->
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Dashboard</h1>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-4">
                        <x-notifications />
                        <x-dark-mode-toggle />
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="px-4 sm:px-6 lg:px-8 py-8">
        @php
            $documentsHomeRoute = auth()->user()->isStudent()
                ? route('documents.my-documents')
                : route('documents.inbox');
        @endphp
        <!-- Welcome Card -->
        <div class="bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8 mb-6 sm:mb-8 border border-orange-200 dark:border-orange-800/30">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Welcome back, {{ auth()->user()->first_name }}!
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-medium">{{ auth()->user()->university_id }}</span> • 
                        <span class="capitalize">{{ auth()->user()->usertype }}</span>
                    </p>
                </div>
                <div class="w-full sm:w-auto">
                    <a href="{{ $documentsHomeRoute }}" class="btn-primary w-full sm:w-auto flex items-center justify-center gap-2">
                        <x-icon name="document-plus" class="w-5 h-5" />
                        <span class="hidden sm:inline">View Documents</span>
                        <span class="sm:hidden">Documents</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards Row -->
        @if(!empty($documentStats))
        @if(auth()->user()->isAdmin())
            <!-- System Overview (Admin Only) -->
            <div class="mb-6 sm:mb-8">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-4">System Overview</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                    @php
                        $systemCards = [
                            ['label' => 'Total Documents', 'value' => $documentStats['total_documents'] ?? 0, 'icon' => 'document-text', 'color' => 'blue'],
                            ['label' => 'Pending', 'value' => $documentStats['pending_documents'] ?? 0, 'icon' => 'clock', 'color' => 'yellow'],
                            ['label' => 'Completed', 'value' => $documentStats['completed_documents'] ?? 0, 'icon' => 'check-circle', 'color' => 'green'],
                            ['label' => 'Urgent', 'value' => $documentStats['urgent_documents'] ?? 0, 'icon' => 'exclamation-triangle', 'color' => 'red'],
                            ['label' => 'Today', 'value' => $documentStats['documents_today'] ?? 0, 'icon' => 'calendar', 'color' => 'orange'],
                        ];
                        if(isset($userStats['total_users'])) {
                            array_unshift($systemCards, ['label' => 'Total Users', 'value' => $userStats['total_users'] ?? 0, 'icon' => 'users', 'color' => 'purple']);
                        }
                    @endphp
                    @foreach($systemCards as $card)
                        <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                            <div class="absolute top-5 sm:top-6 right-5 sm:right-6 transition-transform duration-200 group-hover:scale-110">
                                <x-icon name="{{ $card['icon'] }}" class="w-6 h-6 sm:w-7 sm:h-7 text-gray-500 dark:text-gray-400 transition-colors duration-200" />
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">{{ $card['label'] }}</p>
                            <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ $card['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- My Documents (Admin Personal) -->
            <div class="mb-6 sm:mb-8">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-4">My Documents</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                    @php
                        $myDocCards = [
                            ['label' => 'My Documents', 'value' => $documentStats['my_documents'] ?? 0, 'icon' => 'document-text', 'color' => 'blue'],
                            ['label' => 'Inbox', 'value' => $documentStats['incoming_documents'] ?? 0, 'icon' => 'inbox-arrow-down', 'color' => 'indigo'],
                            ['label' => 'Sent', 'value' => $documentStats['pending_actions'] ?? 0, 'icon' => 'paper-airplane', 'color' => 'yellow'],
                            ['label' => 'Completed', 'value' => $documentStats['my_completed'] ?? 0, 'icon' => 'check-circle', 'color' => 'green'],
                            ['label' => 'Archived', 'value' => $documentStats['archived_documents'] ?? 0, 'icon' => 'archive-box', 'color' => 'gray'],
                        ];
                    @endphp
                    @foreach($myDocCards as $card)
                        <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                            <div class="absolute top-5 sm:top-6 right-5 sm:right-6 transition-transform duration-200 group-hover:scale-110">
                                <x-icon name="{{ $card['icon'] }}" class="w-6 h-6 sm:w-7 sm:h-7 text-gray-500 dark:text-gray-400 transition-colors duration-200" />
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">{{ $card['label'] }}</p>
                            <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ $card['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Non-Admin Overview -->
            <div class="mb-6 sm:mb-8">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-4">Overview</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                    @php
                        $docCards = [
                            ['label' => 'My Documents', 'value' => $documentStats['my_documents'] ?? 0, 'icon' => 'document-text', 'color' => 'blue'],
                            ['label' => 'Inbox', 'value' => $documentStats['incoming_documents'] ?? 0, 'icon' => 'inbox-arrow-down', 'color' => 'indigo'],
                            ['label' => 'Sent', 'value' => $documentStats['pending_actions'] ?? 0, 'icon' => 'paper-airplane', 'color' => 'yellow'],
                            ['label' => 'Completed', 'value' => $documentStats['completed_documents'] ?? 0, 'icon' => 'check-circle', 'color' => 'green'],
                            ['label' => 'Archived', 'value' => $documentStats['archived_documents'] ?? 0, 'icon' => 'archive-box', 'color' => 'gray'],
                        ];
                        if(isset($documentStats['department_documents'])) {
                            $docCards[] = ['label' => 'Department Docs', 'value' => $documentStats['department_documents'], 'icon' => 'building-office'];
                        } elseif(isset($documentStats['college_documents'])) {
                            $docCards[] = ['label' => 'College Docs', 'value' => $documentStats['college_documents'], 'icon' => 'building-library'];
                        }
                    @endphp
                    @foreach($docCards as $card)
                        <div class="bg-white dark:bg-gray-900 rounded-lg p-5 sm:p-6 border border-gray-200 dark:border-gray-700 relative transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-600 hover:-translate-y-1 cursor-pointer">
                            <div class="absolute top-5 sm:top-6 right-5 sm:right-6 transition-transform duration-200 group-hover:scale-110">
                                <x-icon name="{{ $card['icon'] }}" class="w-6 h-6 sm:w-7 sm:h-7 text-gray-500 dark:text-gray-400 transition-colors duration-200" />
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-300 mb-3 transition-colors duration-200">{{ $card['label'] }}</p>
                            <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-200">{{ $card['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @endif

        

        <!-- Analytics Charts Section -->
        @if(isset($statusDistribution) && isset($urgencyDistribution))
        <div class="mb-6 sm:mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Analytics & Reports</h2>
            </div>
            
            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @if(!empty($userStats))
                <!-- User Distribution (Doughnut) -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-700 hover:-translate-y-1 cursor-pointer">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 transition-colors duration-200">User Distribution</h3>
                    <div class="h-64 sm:h-72 md:h-80">
                        <canvas id="userDistributionChart"></canvas>
                    </div>
                </div>
                @endif

                <!-- Document Type Distribution (Bar Chart) -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-700 hover:-translate-y-1 cursor-pointer">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 transition-colors duration-200">Document Types</h3>
                    <div class="h-64 sm:h-72 md:h-80">
                        <canvas id="documentTypeChart"></canvas>
                    </div>
                </div>

                <!-- Status Distribution (Pie Chart) -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-700 hover:-translate-y-1 cursor-pointer">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 transition-colors duration-200">Status Distribution</h3>
                    <div class="h-64 sm:h-72 md:h-80">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Urgency Level Distribution (Doughnut Chart) -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-700 hover:-translate-y-1 cursor-pointer">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 transition-colors duration-200">Urgency Levels</h3>
                    <div class="h-64 sm:h-72 md:h-80">
                        <canvas id="urgencyChart"></canvas>
                    </div>
                </div>

                <!-- Weekly Activity (Line Chart) -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-700 hover:-translate-y-1 cursor-pointer">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 transition-colors duration-200">Weekly Activity</h3>
                    <div class="h-64 sm:h-72 md:h-80">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>

                <!-- Monthly Activity (Area Chart) -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-700 hover:-translate-y-1 cursor-pointer">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 transition-colors duration-200">Monthly Trend (Last 6 Months)</h3>
                    <div class="h-64 sm:h-72 md:h-80">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Popular Tags -->
            <div class="mt-4 sm:mt-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-700 hover:-translate-y-1 cursor-pointer">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 transition-colors duration-200">Popular Tags</h3>
                    <div class="h-64 sm:h-72 md:h-80">
                        <canvas id="tagAnalyticsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Charts if data exists
    @if(isset($statusDistribution) && isset($urgencyDistribution))
    // Chart instances storage
    let chartInstances = {};
    
    // Helper function to get current theme state
    const getIsDarkMode = () => document.documentElement.classList.contains('dark');
    
    // Helper function to get grid color based on current theme
    const getGridColor = () => {
        return getIsDarkMode() 
            ? 'rgba(255, 255, 255, 0.1)' 
            : 'rgba(0, 0, 0, 0.05)';
    };
    
    // Chart.js default configuration
    Chart.defaults.color = getIsDarkMode() ? '#9CA3AF' : '#6B7280';
    Chart.defaults.borderColor = getIsDarkMode() ? '#374151' : '#E5E7EB';
    
    // Color palettes for light and dark mode (students get default orange)
    const lightModePalette = ['#F97316','#EA580C','#C2410C','#9A3412','#7C2D12','#FB923C','#FDBA74','#FED7AA','#FFEDD5'];
    const darkModePalette = ['#F97316','#EA580C','#DC2626','#B91C1C','#991B1B','#7F1D1D','#FB923C','#FDBA74','#FED7AA'];
    
    @php
        $statusCount = $statusDistribution->count();
        $urgencyCount = $urgencyDistribution->count();
        $docTypeCount = $documentTypeDistribution->count();
    @endphp
    
    // Generate colors based on theme
    const getChartColors = (count) => {
        const isDarkMode = getIsDarkMode();
        const palette = isDarkMode ? darkModePalette : lightModePalette;
        return Array.from({ length: count }, (_, i) => palette[i % palette.length]);
    };
    
    // Function to update chart grid colors and line chart colors
    const updateChartGrids = () => {
        const gridColor = getGridColor();
        const isDark = getIsDarkMode();
        
        // Update Chart.js defaults
        Chart.defaults.color = isDark ? '#9CA3AF' : '#6B7280';
        Chart.defaults.borderColor = isDark ? '#374151' : '#E5E7EB';
        
        // Update all chart instances with grid colors and line colors
        Object.values(chartInstances).forEach(chart => {
            if (chart && chart.options) {
                // Update grid colors if chart has scales
                if (chart.options.scales) {
                    // Update x-axis grid
                    if (chart.options.scales.x && chart.options.scales.x.grid) {
                        chart.options.scales.x.grid.color = gridColor;
                    }
                    // Update y-axis grid
                    if (chart.options.scales.y && chart.options.scales.y.grid) {
                        chart.options.scales.y.grid.color = gridColor;
                    }
                }
                
                // Update line chart colors (for Weekly and Monthly charts)
                if (chart.config && chart.config.data && chart.config.data.datasets) {
                    chart.config.data.datasets.forEach(dataset => {
                        if (dataset.borderColor || dataset.backgroundColor) {
                            if (chart.config.type === 'line') {
                                dataset.borderColor = isDark ? '#FB923C' : '#F97316';
                                if (chart === chartInstances.weeklyChart) {
                                    dataset.backgroundColor = isDark ? 'rgba(251, 146, 60, 0.2)' : 'rgba(249, 115, 22, 0.15)';
                                } else if (chart === chartInstances.monthlyChart) {
                                    dataset.backgroundColor = isDark ? 'rgba(251, 146, 60, 0.15)' : 'rgba(249, 115, 22, 0.1)';
                                }
                            }
                        }
                    });
                }
                
                chart.update();
            }
        });
    };
    
    const statusColors = getChartColors({{ $statusCount }});
    const urgencyColors = getChartColors({{ $urgencyCount }});
    const docTypeColors = getChartColors({{ $docTypeCount }});
    
    // Grid color based on theme
    const gridColor = getGridColor();
    // Status Distribution Pie Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        chartInstances.statusChart = new Chart(statusCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: {!! json_encode($statusDistribution->pluck('status')->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))) !!},
                datasets: [{
                    data: {!! json_encode($statusDistribution->pluck('count')) !!},
                    backgroundColor: statusColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Urgency Level Doughnut Chart
    const urgencyCtx = document.getElementById('urgencyChart');
    if (urgencyCtx) {
        chartInstances.urgencyChart = new Chart(urgencyCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($urgencyDistribution->pluck('urgency_level')->map(fn($u) => ucfirst($u))) !!},
                datasets: [{
                    data: {!! json_encode($urgencyDistribution->pluck('count')) !!},
                    backgroundColor: urgencyColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Document Type Bar Chart
    const docTypeCtx = document.getElementById('documentTypeChart');
    if (docTypeCtx) {
        chartInstances.documentTypeChart = new Chart(docTypeCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($documentTypeDistribution->pluck('documentType.name')) !!},
                datasets: [{
                    label: 'Documents',
                    data: {!! json_encode($documentTypeDistribution->pluck('count')) !!},
                    backgroundColor: docTypeColors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: gridColor
                        }
                    }
                }
            }
        });
    }

    // Weekly Activity Line Chart
    const weeklyCtx = document.getElementById('weeklyChart');
    if (weeklyCtx) {
        const getWeeklyColors = () => {
            const isDark = getIsDarkMode();
            return {
                border: isDark ? '#FB923C' : '#F97316',
                background: isDark ? 'rgba(251, 146, 60, 0.2)' : 'rgba(249, 115, 22, 0.15)'
            };
        };
        const weeklyColors = getWeeklyColors();
        chartInstances.weeklyChart = new Chart(weeklyCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($weeklyActivity->pluck('date')) !!},
                datasets: [{
                    label: 'Documents Created',
                    data: {!! json_encode($weeklyActivity->pluck('count')) !!},
                    borderColor: weeklyColors.border,
                    backgroundColor: weeklyColors.background,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: gridColor
                        }
                    }
                }
            }
        });
    }

    // Monthly Activity Area Chart
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        const getMonthlyColors = () => {
            const isDark = getIsDarkMode();
            return {
                border: isDark ? '#FB923C' : '#F97316',
                background: isDark ? 'rgba(251, 146, 60, 0.15)' : 'rgba(249, 115, 22, 0.1)'
            };
        };
        const monthlyColors = getMonthlyColors();
        chartInstances.monthlyChart = new Chart(monthlyCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyActivity->pluck('month')) !!},
                datasets: [{
                    label: 'Documents',
                    data: {!! json_encode($monthlyActivity->pluck('count')) !!},
                    borderColor: monthlyColors.border,
                    backgroundColor: monthlyColors.background,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: gridColor
                        }
                    }
                }
            }
        });
    }
    @endif
    @if(!empty($userStats))
    @php
        $udItems = [
            'Students' => $userStats['total_students'] ?? null,
            'Faculty' => $userStats['total_faculty'] ?? null,
            'Staff' => $userStats['total_staff'] ?? null,
            'Admins' => $userStats['total_admins'] ?? null,
            'Registrars' => $userStats['total_registrars'] ?? null,
            'Deans' => $userStats['total_deans'] ?? null,
            'Dept. Heads' => $userStats['total_department_heads'] ?? null,
        ];
        $udItems = array_filter($udItems, fn($v) => $v !== null);
        $udLabels = array_keys($udItems);
        $udValues = array_values($udItems);
        $userCount = count($udLabels);
    @endphp
    const userColors = getChartColors({{ $userCount }});
    const userDistCtx = document.getElementById('userDistributionChart');
    if (userDistCtx) {
        chartInstances.userDistributionChart = new Chart(userDistCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($udLabels) !!},
                datasets: [{
                    data: {!! json_encode($udValues) !!},
                    backgroundColor: userColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    @endif
    
    // Load Advanced Analytics
    loadAdvancedAnalytics();
    
    async function loadAdvancedAnalytics() {
        try {
            // Tag Analytics
            const tagResponse = await fetch('/dashboard/api/tag-analytics');
            const tagData = await tagResponse.json();
            if (tagData.success && tagData.tags.length > 0) {
                const tagCtx = document.getElementById('tagAnalyticsChart');
                if (tagCtx) {
                    const tagColors = getChartColors(tagData.tags.length);
                    chartInstances.tagAnalyticsChart = new Chart(tagCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: tagData.tags.map(t => t.name),
                            datasets: [{
                                label: 'Usage Count',
                                data: tagData.tags.map(t => t.usage_count),
                                backgroundColor: tagColors,
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: { grid: { color: getGridColor() } },
                                y: { 
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 },
                                    grid: { color: getGridColor() }
                                }
                            }
                        }
                    });
                }
            }
            
        } catch (error) {
            // Error loading advanced analytics
        }
    }
    
    // Listen for theme changes and update chart grids
    document.addEventListener('themeChanged', function() {
        updateChartGrids();
    });

    // Sidebar Toggle Functionality
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarToggle = document.getElementById('sidebar-toggle');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('hidden');
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            if (sidebar) {
                sidebar.classList.add('-translate-x-full');
            }
            sidebarOverlay.classList.add('hidden');
        });
    }

    if (sidebar) {
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('-translate-x-full');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.add('hidden');
                    }
                }
            });
        });
    }

    // Real-time dashboard stats updates
    function setupDashboardBroadcasting() {
        if (!window.Echo) {
            setTimeout(setupDashboardBroadcasting, 100);
            return;
        }

        // Listen for document events to refresh stats
        window.Echo.private('documents')
            .listen('.document.created', () => refreshDashboardStats())
            .listen('.document.updated', () => refreshDashboardStats())
            .listen('.document.forwarded', () => refreshDashboardStats())
            .listen('.document.received', () => refreshDashboardStats())
            .listen('.document.completed', () => refreshDashboardStats())
            .listen('.document.approved', () => refreshDashboardStats())
            .listen('.document.rejected', () => refreshDashboardStats())
            .listen('.document.returned', () => refreshDashboardStats());

        // Also listen on user's personal channel
        window.Echo.private('App.Models.User.{{ auth()->id() }}')
            .listen('.document.forwarded', () => refreshDashboardStats())
            .listen('.document.received', () => refreshDashboardStats());

        @if(auth()->user()->isAdmin())
        // Admin: listen for user changes
        window.Echo.private('admin.users')
            .listen('.user.created', () => refreshDashboardStats())
            .listen('.user.updated', () => refreshDashboardStats())
            .listen('.user.deleted', () => refreshDashboardStats());
        @endif
    }

    // Refresh dashboard stats without full page reload
    window.refreshDashboardStats = async function() {
        try {
            const response = await fetch(window.location.href, {
                headers: { 'Accept': 'text/html' }
            });
            if (!response.ok) return;
            
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update stat cards
            const newStats = doc.querySelectorAll('.bg-white.dark\\:bg-gray-900.rounded-lg');
            const currentStats = document.querySelectorAll('.bg-white.dark\\:bg-gray-900.rounded-lg');
            
            newStats.forEach((newStat, index) => {
                if (currentStats[index]) {
                    currentStats[index].innerHTML = newStat.innerHTML;
                }
            });
            
            console.log('✅ Dashboard stats refreshed');
        } catch (error) {
            console.error('Error refreshing dashboard stats:', error);
        }
    };

    setupDashboardBroadcasting();
});
</script>
@endsection
