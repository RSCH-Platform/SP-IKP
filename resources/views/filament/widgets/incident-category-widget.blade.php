<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
    <div id="categoryChart" class="w-full"></div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const data = @json($this->getData());
                
                const options = {
                    chart: {
                        type: 'bar',
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
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            dataLabels: {
                                position: 'right'
                            },
                            distributed: true,
                            columnWidth: '60%'
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        textAnchor: 'start',
                        formatter: function(val) {
                            return val + ' kasus'
                        },
                        offsetX: 5
                    },
                    colors: [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
                        '#8b5cf6', '#06b6d4', '#ec4899', '#14b8a6'
                    ],
                    xaxis: {
                        categories: data.labels,
                        labels: {
                            style: {
                                fontSize: 12,
                                fontFamily: 'inherit'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: 12,
                                fontFamily: 'inherit'
                            }
                        }
                    },
                    legend: {
                        show: false
                    },
                    tooltip: {
                        enabled: true,
                        theme: 'dark',
                        y: {
                            formatter: function(val) {
                                return val + ' kasus'
                            }
                        }
                    }
                };

                new ApexCharts(document.querySelector('#categoryChart'), options).render();
            });
        </script>
    @endpush
</div>
