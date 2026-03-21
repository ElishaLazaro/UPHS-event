<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('responsivePieChart').getContext('2d');
    const responsivePieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Completed', 'Pending', 'Cancelled'],
            datasets: [{
                data: [12, 7, 3],
                backgroundColor: ['#4caf50', '#ffc107', '#f44336'],
            }]
        },
        options: {
            responsive: true,          // chart adjusts to container size
            maintainAspectRatio: false, // allows the chart to fill the container
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>