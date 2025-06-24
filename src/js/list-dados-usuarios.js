document.addEventListener('DOMContentLoaded', async function () {
  try {
    const resposta = await fetch('../backend/api/admin/dados_admin.php');
    if (!resposta.ok) {
      throw new Error(`Erro HTTP: ${resposta.status}`);
    }
    const dados = await resposta.json();

    if (dados.sucesso) {
      // Atualiza os cards
      document.getElementById('totalUsuarios').textContent = dados.dados.total_usuarios;
      document.getElementById('totalTransacoes').textContent = dados.dados.total_transacoes;

      // Atualiza a lista de transações
      const listaTransacoes = document.getElementById('listaTransacoes');
      listaTransacoes.innerHTML = ''; // Limpa a mensagem de "carregando"

      if (dados.dados.transacoes_recentes.length > 0) {
        dados.dados.transacoes_recentes.forEach(transacao => {
          const item = document.createElement('div');
          item.className = 'item-list';

          const valorFormatado = parseFloat(transacao.valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
          const dataFormatada = new Date(transacao.data).toLocaleDateString('pt-BR');

          item.innerHTML = `
            <div class="circle"></div>
            <h3 class="item-user">${transacao.usuario_nome}</h3>
            <p class="item-category">${transacao.categoria}</p>
            <p class="item-pagamento">${transacao.tipo}</p>
            <p class="item-data">${dataFormatada}</p>
            <p class="item-valor">${valorFormatado}</p>
          `;
          listaTransacoes.appendChild(item);
        });
      } else {
        listaTransacoes.innerHTML = '<p>Nenhuma transação encontrada.</p>';
      }
    } else {
      throw new Error(dados.error || 'Falha ao buscar dados.');
    }

  } catch (erro) {
    console.error('Erro ao buscar dados para o painel de admin:', erro);
    document.getElementById('listaTransacoes').innerHTML = '<p style="color: red;">Erro ao carregar os dados.</p>';
  }
});