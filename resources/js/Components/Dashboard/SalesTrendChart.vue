<script setup lang="ts">
import {
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Filler,
    Legend,
);

const props = defineProps<{
    data: { date: string; total: number; count: number }[];
}>();

const chartData = computed(() => {
    const today = new Date();
    const dates: Record<string, number> = {};
    for (let i = 29; i >= 0; i--) {
        const d = new Date(today);
        d.setDate(d.getDate() - i);
        dates[d.toISOString().slice(0, 10)] = 0;
    }
    props.data.forEach((d) => {
        dates[d.date] = d.total;
    });
    return {
        labels: Object.keys(dates).map((d) => {
            const p = d.split('-');
            return p[2] + '/' + p[1];
        }),
        datasets: [
            {
                label: 'Ventas',
                data: Object.values(dates),
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.08)',
                fill: true,
                tension: 0.3,
                pointRadius: 2,
                pointHoverRadius: 5,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx: any) =>
                    '$' + Number(ctx.parsed.y).toLocaleString('es-CL'),
                afterLabel: (ctx: any) => {
                    const idx = ctx.dataIndex;
                    const keys = Object.keys(chartData.value.labels);
                    const dateKey = Object.keys(
                        chartData.value.datasets[0].data,
                    )[idx];
                    const real = props.data.find((d) => d.date === dateKey);
                    return real ? real.count + ' ventas' : '0 ventas';
                },
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { maxTicksLimit: 8, font: { size: 10 } },
        },
        y: {
            grid: { color: 'rgba(0,0,0,0.06)' },
            ticks: {
                callback: (v: any) => '$' + (v / 1000).toFixed(0) + 'k',
                font: { size: 10 },
            },
        },
    },
};
</script>

<template>
    <div class="relative h-64 w-full">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>
