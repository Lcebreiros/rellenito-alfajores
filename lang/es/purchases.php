<?php

return [
    'title'              => 'Compras y Gastos',
    'btn_new'            => 'Nuevo registro',
    'btn_save'           => 'Guardar',
    'btn_cancel'         => 'Cancelar',

    // Summary cards
    'total_month'        => 'Total del mes',
    'card_supplies'      => 'Compras de insumos',
    'card_expenses'      => 'Gastos generales',

    // Form
    'form_title'         => 'Registrar compra o gasto',
    'tab_supply'         => 'Compra de insumo',
    'tab_expense'        => 'Gasto general',

    // Supply tab
    'field_supply'       => 'Insumo',
    'field_qty'          => 'Cantidad',
    'field_unit'         => 'Unidad',
    'field_total_cost'   => 'Costo total',
    'field_date'         => 'Fecha',
    'select_supply'      => 'Seleccionar insumo...',
    'no_supplies_hint'   => 'No hay insumos registrados. <a href=":url" class="underline text-indigo-600">Creá uno primero.</a>',
    'unit_incompatible'  => 'La unidad elegida no es compatible con la unidad base del insumo.',

    // Expense tab
    'field_description'  => 'Descripción',
    'field_category'     => 'Categoría',
    'field_amount'       => 'Monto',
    'field_supplier'     => 'Proveedor (opcional)',
    'desc_placeholder'   => 'Ej: Compra de harina, pago de alquiler...',
    'no_supplier'        => 'Sin proveedor',

    // Badges
    'badge_supply'       => 'Insumo',
    'badge_expense'      => 'Gasto',

    // Empty state
    'empty'              => 'No hay registros para este mes.',

    // Actions
    'delete'             => 'Eliminar',
    'confirm_delete'     => '¿Eliminar este registro?',

    // Flash messages
    'supply_stored'      => 'Compra de insumo registrada correctamente.',
    'expense_stored'     => 'Gasto registrado correctamente.',
    'deleted'            => 'Registro eliminado.',
];
