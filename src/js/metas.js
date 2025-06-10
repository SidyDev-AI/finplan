document.addEventListener("DOMContentLoaded", function () {
  const btnNovaMeta = document.getElementById("btnNovaMeta");
  const popup = document.querySelector(".metas-popup");
  const fecharBtn = document.getElementById("fecharPopup");

  btnNovaMeta.addEventListener("click", function () {
    // Mostrar popup
    popup.style.display = "block";
    // Preecher selects de data inicial (única opção, bloqueada)
    const now = new Date();
    const currentDay = now.getDate();
    const currentMonth = now.getMonth() + 1;
    const currentYear = now.getFullYear();

    const daySelect = document.getElementById("day");
    const monthSelect = document.getElementById("month");
    const yearSelect = document.getElementById("year");

    daySelect.innerHTML = `<option selected value="${currentDay}">${currentDay}</option>`;
    monthSelect.innerHTML = `<option selected value="${currentMonth}">${now.toLocaleString('default', { month: 'long' })}</option>`;
    yearSelect.innerHTML = `<option selected value="${currentYear}">${currentYear}</option>`;

    // Preecher selects da data final (editáveis)
    const dayFinal = document.getElementById("day_final");
    const monthFinal = document.getElementById("month_final");
    const yearFinal = document.getElementById("year_final");

    dayFinal.innerHTML = '';
    monthFinal.innerHTML = '';
    yearFinal.innerHTML = '';

    // Dias
    for (let d = 1; d <= 31; d++) {
      const selected = d === currentDay ? 'selected' : '';
      dayFinal.innerHTML += `<option value="${d}" ${selected}>${d}</option>`;
    }

    // Meses
    for (let m = 1; m <= 12; m++) {
      const nomeMes = new Date(0, m - 1).toLocaleString('default', { month: 'long' });
      const selected = m === currentMonth ? 'selected' : '';
      monthFinal.innerHTML += `<option value="${m}" ${selected}>${nomeMes}</option>`;
    }

    // Anos (hoje até 5 anos no futuro)
    for (let y = currentYear; y <= currentYear + 5; y++) {
      const selected = y === currentYear ? 'selected' : '';
      yearFinal.innerHTML += `<option value="${y}" ${selected}>${y}</option>`;
    }
  });

  fecharBtn.addEventListener("click", function () {
    popup.style.display = "none";
  });

  document.querySelectorAll('.btn.toggle-menu').forEach(botao => {
    botao.addEventListener('click', function () {
      const card = botao.closest('.card');
      const popupView = card.querySelector('.view-metas-popup');
      popupView.style.display = 'block';

      // Pega data final salva
      const dataFinalSalva = card.querySelector('.data-final-salva').value; // Ex: "2025-08-15"
      const [anoSalvo, mesSalvo, diaSalvo] = dataFinalSalva.split('-');

      const dayFinal = card.querySelector('.view_day_final');
      const monthFinal = card.querySelector('.view_month_final');
      const yearFinal = card.querySelector('.view_year_final');

      // Limpa
      dayFinal.innerHTML = '';
      monthFinal.innerHTML = '';
      yearFinal.innerHTML = '';

      // Preenche dias
      for (let d = 1; d <= 31; d++) {
        const selected = d == diaSalvo ? 'selected' : '';
        dayFinal.innerHTML += `<option value="${d}" ${selected}>${d}</option>`;
      }

      // Preenche meses
      for (let m = 1; m <= 12; m++) {
        const nomeMes = new Date(0, m - 1).toLocaleString('default', { month: 'long' });
        const selected = m == mesSalvo ? 'selected' : '';
        monthFinal.innerHTML += `<option value="${m}" ${selected}>${nomeMes}</option>`;
      }

      // Preenche anos
      const currentYear = new Date().getFullYear();
      for (let y = currentYear; y <= currentYear + 5; y++) {
        const selected = y == anoSalvo ? 'selected' : '';
        yearFinal.innerHTML += `<option value="${y}" ${selected}>${y}</option>`;
      }
    });
  });

  document.querySelectorAll('.fechar-btn').forEach(botao => {
    botao.addEventListener('click', function () {
      const card = botao.closest('.card');
      const popup = card.querySelector('.view-metas-popup');
      popup.style.display = 'none';
    });
  });

  // ✅ Coloca no topo ou antes do addEventListener
  function mostrarAlertaSucesso(mensagem) {
    const alerta = document.createElement('div');
    alerta.className = 'alerta-sucesso';
    alerta.textContent = mensagem;
    document.body.appendChild(alerta);

    setTimeout(() => {
      alerta.classList.add('mostrar');
    }, 10);

    setTimeout(() => {
      alerta.classList.remove('mostrar');
      setTimeout(() => alerta.remove(), 300);
    }, 3000);
  }

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('editar-meta')) {
      e.preventDefault();
      e.stopImmediatePropagation();
      const card = e.target.closest('.card');

      card.querySelectorAll('.nomeMetaInput, .descricaoViewMeta, .valorMetaInput').forEach(el => {
        el.removeAttribute('readonly');
      });

      card.querySelectorAll('.view_day_final, .view_month_final, .view_year_final').forEach(el => {
        el.removeAttribute('disabled');
      });

      e.target.textContent = 'Salvar';
      e.target.classList.remove('editar-meta');
      e.target.classList.add('salvar-meta', 'btn-salvar-verde');
    }
  });

  // Evento de salvar
  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('salvar-meta')) {
      const card = e.target.closest('.card');
      const metaId = card.querySelector('.btn.toggle-menu').dataset.id;

      const nomeInput = card.querySelector('.nomeMetaInput');
      const descricaoInput = card.querySelector('.descricaoViewMeta');
      const valorInput = card.querySelector('.valorMetaInput');

      const dia = card.querySelector('.view_day_final').value;
      const mes = card.querySelector('.view_month_final').value;
      const ano = card.querySelector('.view_year_final').value;

      const titulo = nomeInput.value;
      const descricao = descricaoInput.value;
      const valor = valorInput.value.replace(/[^\d,]/g, '').replace(',', '.');
      const dataFinal = `${ano}-${mes.padStart(2, '0')}-${dia.padStart(2, '0')}`;

      fetch('../backend/atualizar_meta.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
          id: metaId,
          titulo,
          descricao,
          valor,
          data_final: dataFinal
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.sucesso) {
          nomeInput.setAttribute('readonly', true);
          descricaoInput.setAttribute('readonly', true);
          valorInput.setAttribute('readonly', true);

          card.querySelectorAll('.view_day_final, .view_month_final, .view_year_final').forEach(el => {
            el.setAttribute('disabled', true);
          });

          e.target.textContent = 'Editar';
          e.target.classList.remove('salvar-meta', 'btn-salvar-verde');
          e.target.classList.add('editar-meta');

          mostrarAlertaSucesso('Meta atualizada com sucesso!');
          card.querySelector('.view-metas-popup').style.display = 'none';
        } else {
          alert('Erro ao atualizar a meta.');
        }
      });
    }
  });

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('excluir-meta')) {
      const botao = e.target;
      const metaId = botao.dataset.id;

      if (confirm("Tem certeza que deseja excluir esta meta?")) {
        fetch('../backend/excluir_meta.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ id: metaId })
        })
        .then(res => res.json())
        .then(data => {
          if (data.sucesso) {
            // Remove o card da tela (opcional)
            const card = botao.closest('.card');
            if (card) card.remove();

            alert('Meta excluída com sucesso!');
          } else {
            alert('Erro ao excluir a meta.');
          }
        })
        .catch(err => {
          console.error('Erro:', err);
          alert('Erro de conexão com o servidor.');
        });
      }
    }
  });
});