/**
 * HCPVision JavaScript Application
 * Handles charts, interactions, and dynamic content
 */

// Global chart instances
let charts = {};

// Initialize application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    initializeDataTables();
    initializeSidebar();
    initializeTooltips();
});

/**
 * Initialize Chart.js charts
 */
function initializeCharts() {
    // Dashboard KPI Chart
    const kpiChart = document.getElementById('kpiChart');
    if (kpiChart) {
        createKPIChart(kpiChart);
    }
    
    // Enqueteur Performance Chart
    const enqueteurChart = document.getElementById('enqueteurChart');
    if (enqueteurChart) {
        createEnqueteurChart(enqueteurChart);
    }
    
    // Region Progress Chart
    const regionChart = document.getElementById('regionChart');
    if (regionChart) {
        createRegionChart(regionChart);
    }
    
    // Status Distribution Chart
    const statusChart = document.getElementById('statusChart');
    if (statusChart) {
        createStatusChart(statusChart);
    }
}

/**
 * Create KPI Dashboard Chart
 */
function createKPIChart(ctx) {
    fetch('/api/dashboard/kpi')
        .then(response => response.json())
        .then(data => {
            charts.kpi = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Terminées', 'En cours', 'Partielles', 'Refus'],
                    datasets: [{
                        data: [
                            data.enquetes_terminees,
                            data.enquetes_en_cours,
                            data.enquetes_partielles,
                            data.enquetes_refus
                        ],
                        backgroundColor: [
                            '#28a745',
                            '#ffc107',
                            '#fd7e14',
                            '#dc3545'
                        ],
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
        })
        .catch(error => console.error('Error loading KPI data:', error));
}

/**
 * Create Enqueteur Performance Chart
 */
function createEnqueteurChart(ctx) {
    fetch('/api/enqueteurs/performance')
        .then(response => response.json())
        .then(data => {
            charts.enqueteur = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Nombre d\'enquêtes',
                        data: data.enquetes,
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderColor: 'rgba(102, 126, 234, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Taux de completion (%)',
                        data: data.taux_completion,
                        backgroundColor: 'rgba(118, 75, 162, 0.8)',
                        borderColor: 'rgba(118, 75, 162, 1)',
                        borderWidth: 1,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            position: 'left'
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            max: 100,
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error loading enqueteur data:', error));
}

/**
 * Create Region Progress Chart
 */
function createRegionChart(ctx) {
    fetch('/api/regions/progress')
        .then(response => response.json())
        .then(data => {
            charts.region = new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Taux de completion (%)',
                        data: data.taux_completion,
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error loading region data:', error));
}

/**
 * Create Status Distribution Chart
 */
function createStatusChart(ctx) {
    fetch('/api/dashboard/status')
        .then(response => response.json())
        .then(data => {
            charts.status = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: [
                            '#28a745',
                            '#ffc107',
                            '#fd7e14',
                            '#dc3545',
                            '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error loading status data:', error));
}

/**
 * Initialize DataTables for enhanced table functionality
 */
function initializeDataTables() {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.data-table').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
            },
            pageLength: 25,
            order: [[0, 'asc']]
        });
    }
}

/**
 * Initialize sidebar toggle for mobile
 */
function initializeSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
}

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Export chart as image
 */
function exportChart(chartId, filename) {
    const chart = charts[chartId];
    if (chart) {
        const url = chart.toBase64Image();
        const link = document.createElement('a');
        link.download = filename || 'chart.png';
        link.href = url;
        link.click();
    }
}

/**
 * Refresh chart data
 */
function refreshChart(chartId) {
    const chart = charts[chartId];
    if (chart) {
        chart.destroy();
        // Reinitialize the specific chart
        const ctx = document.getElementById(chartId + 'Chart');
        if (ctx) {
            switch(chartId) {
                case 'kpi':
                    createKPIChart(ctx);
                    break;
                case 'enqueteur':
                    createEnqueteurChart(ctx);
                    break;
                case 'region':
                    createRegionChart(ctx);
                    break;
                case 'status':
                    createStatusChart(ctx);
                    break;
            }
        }
    }
}

/**
 * Show loading indicator
 */
function showLoading(containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = `
            <div class="loading">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-2">Chargement des données...</p>
            </div>
        `;
    }
}

/**
 * Generate PDF report
 */
function generatePDFReport(reportType) {
    showLoading('reportContainer');
    
    fetch('/rapports/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            type: reportType,
            format: 'pdf'
        })
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `rapport_${reportType}_${new Date().toISOString().split('T')[0]}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
    })
    .catch(error => {
        console.error('Error generating PDF:', error);
        alert('Erreur lors de la génération du PDF');
    })
    .finally(() => {
        document.getElementById('reportContainer').innerHTML = '';
    });
}

/**
 * Update user profile
 */
function updateProfile(formData) {
    fetch('/comptes/update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Profil mis à jour avec succès');
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating profile:', error);
        alert('Erreur lors de la mise à jour');
    });
}
