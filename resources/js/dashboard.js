import {
    Chart,
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    ArcElement, BarController, BarElement, CategoryScale, DoughnutController,
    Legend, LinearScale, LineController, LineElement, PointElement, Tooltip,
);

const el = document.getElementById('dashboard-data');

if (el) {
    const { status, trend } = JSON.parse(el.textContent);

    const statusCanvas = document.getElementById('status-chart');
    if (statusCanvas && status.data.some((n) => n > 0)) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: status.labels,
                datasets: [{
                    data: status.data,
                    backgroundColor: ['#9ca3af', '#6366f1', '#22c55e'],
                    borderWidth: 0,
                }],
            },
            options: {
                cutout: '65%',
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }

    const trendCanvas = document.getElementById('trend-chart');
    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: trend.labels,
                datasets: [
                    {
                        label: 'Created',
                        data: trend.created,
                        borderColor: '#6366f1',
                        backgroundColor: '#6366f1',
                        tension: 0.3,
                    },
                    {
                        label: 'Completed',
                        data: trend.completed,
                        borderColor: '#22c55e',
                        backgroundColor: '#22c55e',
                        tension: 0.3,
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }
}