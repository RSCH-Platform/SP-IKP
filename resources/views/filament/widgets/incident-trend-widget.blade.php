<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
    <div id="trendChart" class="w-full"></div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const data = @json($this->getData());
                
                const options = {
                    chart: {
                        type: 'area',
                        width: '100%',
                        height: 400,
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: false,
                                zoomin: false,
                                zoomout: false,
                                pan: false,
                                reset: false
                            }
                        },
                        sparkline: {
                            enabled: false
                        },
                        zoom: {
                            enabled: false
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2,
                        colors: ['#3b82f6']
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [20, 100, 100, 100]
                        },
                        colors: ['#3b82f6']
                    },
                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        size: 4,
                        colors: ['#3b82f6'],
                        strokeColors: '#fff',
                        strokeWidth: 2,
                        hover: {
                            size: 6
                        }
                    },
                    xaxis: {
                        categories: data.months,
                        labels: {
                            format: 'yyyy-mm',
                            style: {
                                fontSize: 12,
                                fontFamily: 'inherit'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Jumlah Insiden',
                            style: {
                                fontSize: 13,
                                fontFamily: 'inherit'
                            }
                        },
                        labels: {
                            style: {
                                fontSize: 12,
                                fontFamily: 'inherit'
                            }
                        }
                    },
                    series: [{
                        name: 'Insiden',
                        data: data.series
                    }],
                    tooltip: {
                        enabled: true,
                        theme: 'dark',
                        y: {
                            formatter: function(val) {
                                return val + ' insiden'
                            }
                        },
                        x: {
                            format: 'yyyy-mm'
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left',
                        fontSize: 12,
                        fontFamily: 'inherit'
                    }
                };

                new ApexCharts(document.querySelector('#trendChart'), options).render();
            });
        </script>
    @endpush
</div>
