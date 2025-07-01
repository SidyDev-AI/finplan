<?php
// Mapeamento de ícones para as categorias utilizadas no sistema

$icons = [
    'alimentação' => 'fas fa-utensils',
    'dívida'      => 'fas fa-credit-card',
    'empréstimo'  => 'fas fa-hand-holding-usd',
    'consórcio'   => 'fas fa-handshake',
    'aluguel'     => 'fas fa-home',
    'energia'     => 'fas fa-bolt',
    'internet'    => 'fas fa-wifi',
    'água'        => 'fas fa-tint',
    'lazer'       => 'fas fa-gamepad'
];

/**
 * Retorna o ícone correspondente à categoria informada.
 *
 * @param string $categoria Nome da categoria (ex: "Alimentação")
 * @param array $icons Array com o mapeamento de ícones
 * @return string Classe do ícone FontAwesome
 */
function buscarIconeCategoria($categoria, $icons) {
    $categoria_lower = mb_strtolower(trim($categoria), 'UTF-8');

    // Busca exata
    if (isset($icons[$categoria_lower])) {
        return $icons[$categoria_lower];
    }

    // Busca parcial
    foreach ($icons as $key => $icon) {
        if (str_contains($categoria_lower, mb_strtolower($key, 'UTF-8'))) {
            return $icon;
        }
    }

    // Ícone padrão
    return 'fas fa-tags';
}
