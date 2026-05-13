<?php

return [
    'title'              => 'Compras e Gastos',
    'btn_new'            => 'Novo registro',
    'btn_save'           => 'Salvar',
    'btn_cancel'         => 'Cancelar',

    // Summary cards
    'total_month'        => 'Total do mês',
    'card_supplies'      => 'Compras de insumos',
    'card_expenses'      => 'Gastos gerais',
    'card_products'      => 'Compras de produtos',

    // Form
    'form_title'         => 'Registrar compra ou gasto',
    'tab_supply'         => 'Compra de insumo',
    'tab_expense'        => 'Gasto geral',
    'tab_product'        => 'Compra de produto',

    // Supply tab
    'field_supply'       => 'Insumo',
    'field_qty'          => 'Quantidade',
    'field_unit'         => 'Unidade',
    'field_total_cost'   => 'Custo total',
    'field_date'         => 'Data',
    'select_supply'      => 'Selecionar insumo...',
    'no_supplies_hint'   => 'Nenhum insumo cadastrado. <a href=":url" class="underline text-indigo-600">Crie um primeiro.</a>',
    'unit_incompatible'  => 'A unidade escolhida não é compatível com a unidade base do insumo.',

    // Expense tab
    'field_description'  => 'Descrição',
    'field_category'     => 'Categoria',
    'field_amount'       => 'Valor',
    'field_supplier'     => 'Fornecedor (opcional)',
    'desc_placeholder'   => 'Ex: Compra de farinha, pagamento de aluguel...',
    'no_supplier'        => 'Sem fornecedor',

    // Product tab
    'field_product'      => 'Produto',
    'select_product'     => 'Selecionar produto...',
    'no_products_hint'   => 'Nenhum produto ativo encontrado.',

    // Badges
    'badge_supply'       => 'Insumo',
    'badge_expense'      => 'Gasto',
    'badge_product'      => 'Produto',

    // Empty state
    'empty'              => 'Nenhum registro para este mês.',

    // Actions
    'delete'             => 'Excluir',
    'confirm_delete'     => 'Excluir este registro?',

    // Flash messages
    'supply_stored'      => 'Compra de insumo registrada com sucesso.',
    'expense_stored'     => 'Gasto registrado com sucesso.',
    'product_stored'     => 'Compra de produto registrada. Estoque atualizado.',
    'deleted'            => 'Registro excluído.',
];
