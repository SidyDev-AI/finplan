<!DOCTYPE html>
<html lang="pt-br">
<head><meta charset="UTF-8"><title>Testar envio de e-mail</title></head>
<body>
  <h2>Testar envio de e-mail</h2>
  <form method="post" action="/src/backend/api/email/enviar_transacao.php">
    <input type="text" name="nome" placeholder="Nome" value="Lucas"><br><br>
    <input type="email" name="email" placeholder="Email" value="seuemail@seudominio.com"><br><br>
    <input type="text" name="valor" placeholder="Valor" value="123.45"><br><br>
    <input type="text" name="tipo" placeholder="Tipo" value="Entrada"><br><br>
    <input type="text" name="categoria" placeholder="Categoria" value="Salário"><br><br>
    <input type="text" name="descricao" placeholder="Descrição" value="Pagamento mensal"><br><br>
    <input type="text" name="data" placeholder="Data" value="01/07/2025"><br><br>
    <input type="text" name="metodo_pagamento" placeholder="Método" value="PIX"><br><br>
    <input type="text" name="parcelamento" placeholder="Parcelamento" value="Não"><br><br>
    <button type="submit">Enviar</button>
  </form>
</body>
</html>
