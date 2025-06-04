document.addEventListener("DOMContentLoaded", function () {
  const btnNovaMeta = document.getElementById("btnNovaMeta");
  const popup = document.querySelector(".metas-popup");

  btnNovaMeta.addEventListener("click", function () {
    popup.style.display = "block";
  });

  const fecharBtn = document.getElementById("fecharPopup");
  fecharBtn.addEventListener("click", function () {
    popup.style.display = "none";
  });

  const now = new Date();
  const currentDay = now.getDate();
  const currentMonth = now.getMonth() + 1;
  const currentYear = now.getFullYear();

  // Campos da data inicial (bloqueada)
  const daySelect = document.getElementById("day");
  const monthSelect = document.getElementById("month");
  const yearSelect = document.getElementById("year");

  // Define a data de hoje como opção única
  daySelect.innerHTML = `<option selected value="${currentDay}">${currentDay}</option>`;
  monthSelect.innerHTML = `<option selected value="${currentMonth}">${now.toLocaleString('default', { month: 'long' })}</option>`;
  yearSelect.innerHTML = `<option selected value="${currentYear}">${currentYear}</option>`;

  // Campos da data final (editável)
  const dayFinal = document.getElementById("day_final");
  const monthFinal = document.getElementById("month_final");
  const yearFinal = document.getElementById("year_final");

  // Preencher dias
  for (let d = 1; d <= 31; d++) {
    const selected = d === currentDay ? 'selected' : '';
    dayFinal.innerHTML += `<option value="${d}" ${selected}>${d}</option>`;
  }

  // Preencher meses
  for (let m = 1; m <= 12; m++) {
    const nomeMes = new Date(0, m - 1).toLocaleString('default', { month: 'long' });
    const selected = m === currentMonth ? 'selected' : '';
    monthFinal.innerHTML += `<option value="${m}" ${selected}>${nomeMes}</option>`;
  }

  // Preencher anos (de hoje até 5 anos no futuro)
  for (let y = currentYear; y <= currentYear + 5; y++) {
    const selected = y === currentYear ? 'selected' : '';
    yearFinal.innerHTML += `<option value="${y}" ${selected}>${y}</option>`;
  }
});