<?php

return [
    'title'              => 'Purchases & Expenses',
    'btn_new'            => 'New record',
    'btn_save'           => 'Save',
    'btn_cancel'         => 'Cancel',

    // Summary cards
    'total_month'        => 'Month total',
    'card_supplies'      => 'Supply purchases',
    'card_expenses'      => 'General expenses',
    'card_products'      => 'Product purchases',

    // Form
    'form_title'         => 'Record purchase or expense',
    'tab_supply'         => 'Supply purchase',
    'tab_expense'        => 'General expense',
    'tab_product'        => 'Product purchase',

    // Supply tab
    'field_supply'       => 'Supply',
    'field_qty'          => 'Quantity',
    'field_unit'         => 'Unit',
    'field_total_cost'   => 'Total cost',
    'field_date'         => 'Date',
    'select_supply'      => 'Select supply...',
    'no_supplies_hint'   => 'No supplies registered. <a href=":url" class="underline text-indigo-600">Create one first.</a>',
    'unit_incompatible'  => 'The chosen unit is not compatible with the supply\'s base unit.',

    // Expense tab
    'field_description'  => 'Description',
    'field_category'     => 'Category',
    'field_amount'       => 'Amount',
    'field_supplier'     => 'Supplier (optional)',
    'desc_placeholder'   => 'E.g.: Flour purchase, rent payment...',
    'no_supplier'        => 'No supplier',

    // Product tab
    'field_product'      => 'Product',
    'select_product'     => 'Select product...',
    'no_products_hint'   => 'No active products found.',

    // Badges
    'badge_supply'       => 'Supply',
    'badge_expense'      => 'Expense',
    'badge_product'      => 'Product',

    // Empty state
    'empty'              => 'No records for this month.',

    // Actions
    'delete'             => 'Delete',
    'confirm_delete'     => 'Delete this record?',

    // Flash messages
    'supply_stored'      => 'Supply purchase recorded successfully.',
    'expense_stored'     => 'Expense recorded successfully.',
    'product_stored'     => 'Product purchase recorded. Stock updated.',
    'deleted'            => 'Record deleted.',
];
