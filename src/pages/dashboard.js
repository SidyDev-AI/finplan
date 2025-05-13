import { Chart } from "@/components/ui/chart"
document.addEventListener("DOMContentLoaded", () => {
  // Configuração do tema
  const themeSwitch = document.getElementById("theme-switch")

  themeSwitch.addEventListener("change", function () {
    if (this.checked) {
      // Tema escuro (padrão)
      document.body.classList.remove("light-theme")
    } else {
      // Tema claro
      document.body.classList.add("light-theme")
    }
  })

  // Gráfico de rosca (doughnut) para estatísticas
  const doughnutCtx = document.getElementById("doughnutChart").getContext("2d")
  const doughnutChart = new Chart(doughnutCtx, {
    type: "doughnut",
    data: {
      labels: ["Balance", "Investment", "Goals"],
      datasets: [
        {
          data: [55, 30, 15],
          backgroundColor: ["#279E8E", "#9C27B0", "#3F51B5"],
          borderWidth: 0,
          cutout: "70%",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: "#041518",
          titleColor: "#FFFFFF",
          bodyColor: "#FFFFFF",
          bodyFont: {
            size: 14,
          },
          displayColors: false,
          callbacks: {
            label: (context) => context.label + ": " + context.raw + "%",
          },
        },
      },
    },
  })

  // Gráfico de barras para indicador comparativo
  const barCtx = document.getElementById("barChart").getContext("2d")
  const barChart = new Chart(barCtx, {
    type: "bar",
    data: {
      labels: ["JAN", "FEV", "MAR"],
      datasets: [
        {
          label: "Balances",
          data: [1500, 1200, 2200],
          backgroundColor: "#4CAF50",
          borderWidth: 0,
          borderRadius: 4,
        },
        {
          label: "Expenses",
          data: [600, 1300, 900],
          backgroundColor: "#F44336",
          borderWidth: 0,
          borderRadius: 4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: "rgba(255, 255, 255, 0.1)",
          },
          ticks: {
            color: "#B0B0B0",
          },
        },
        x: {
          grid: {
            display: false,
          },
          ticks: {
            color: "#B0B0B0",
          },
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: "#041518",
          titleColor: "#FFFFFF",
          bodyColor: "#FFFFFF",
          bodyFont: {
            size: 14,
          },
          callbacks: {
            label: (context) => context.dataset.label + ": R$ " + context.raw.toFixed(2),
          },
        },
      },
      barPercentage: 0.6,
    },
  })

  // Simulação de interatividade nos seletores de data
  const dateInputs = document.querySelectorAll(".date-input")
  dateInputs.forEach((input) => {
    input.addEventListener("click", () => {
      alert("O seletor de data seria aberto aqui em uma implementação completa.")
    })
  })
})
