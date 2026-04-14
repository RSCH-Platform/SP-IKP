<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
    <div id="statusChart" class="w-full"></div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const data = @json($this->getData());
                
                const options = {
                    chart: {
                        type: 'donut',
                        width: '100%',
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
                        }
                    },
                    labels: data.labels,
                    series: data.series,
                    colors: data.colors,
                    legend: {
                        position: 'bottom',
                        fontSize: 13,
                        fontFamily: 'inherit'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return Math.round(val) + '%'
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Laporan',
                                        fontSize: 14,
                                        fontFamily: 'inherit',
                                        fontWeight: 600,
                                        color: '#48bb78'
                                    },
                                    value: {
                                        fontFamily: 'inherit',
                                        fontSize: 18,
                                        fontWeight: 600,
                                        show: true
                                    }
                                }
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                        theme: 'dark',
                        y: {
                            formatter: function(val) {
                                return val + ' laporan'
                            }
                        }
                    }
                };

                new ApexCharts(document.querySelector('#statusChart'), options).render();
            });
        </script>
    @endpush
</div>
