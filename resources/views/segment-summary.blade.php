<!DOCTYPE html>
<html>
<head>
    <title>Segment Summary</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <h2>Customer Segment Table</h2>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Segment</th>
                <th>Average Age</th>
                <th>Average Income</th>
                <th>Spending Score</th>
                <th>Customer Count</th>
                <th>Top Product</th>
            </tr>
        </thead>
        <tbody id="my-table">
            <!-- Data will go here -->
        </tbody>
    </table>

    <h2>Charts Below 👇</h2>

    <canvas id="incomeChart" width="400" height="200"></canvas>
    <canvas id="scoreChart" width="400" height="200"></canvas>

    <script>
        fetch('/api/segment-summary')
            .then(res => res.json())
            .then(data => {
                const table = document.getElementById('my-table');
                const rows = data.slice(1); // skip the header row

                let labels = [];
                let incomes = [];
                let scores = [];

                rows.forEach(row => {
                    const [segment, age, income, score, count, product] = row;

                    // Add a row to the table
                    let tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${segment}</td>
                        <td>${age}</td>
                        <td>${income}</td>
                        <td>${score}</td>
                        <td>${count}</td>
                        <td>${product}</td>
                    `;
                    table.appendChild(tr);

                    // Add to chart data
                    labels.push(segment);
                    incomes.push(parseFloat(income));
                    scores.push(parseFloat(score));
                });

                // Chart for Income
                new Chart(document.getElementById('incomeChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Average Income',
                            data: incomes,
                            backgroundColor: 'blue'
                        }]
                    }
                });

                // Chart for Spending Score
                new Chart(document.getElementById('scoreChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Spending Score',
                            data: scores,
                            backgroundColor: 'green'
                        }]
                    }
                });
            });
    </script>
</body>
</html>
