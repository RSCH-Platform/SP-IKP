<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
    <div id="riskChart" class="w-full"></div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const data = @json($this->getData());
                
                const options = {
                    chart: {
                        type: 'bar',
                        width: '100%',
                        height: 350,
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
                            dataLabels: {
                                position: 'top'
                            },
                            distributed: true,
                            columnWidth: '70%'
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val + ' kasus'
                        },
                        offsetY: -20
                    },
                    colors: data.colors,
                    xaxis: {
                        categories: data.labels,
                        labels: {
                            style: {
                                fontSize: 12,
                                fontFamily: 'inherit'
                            },
                            rotate: -45,
                            rotateAlways: true
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

                new ApexCharts(document.querySelector('#riskChart'), options).render();
            });
        </script>
    @endpush
</div>
