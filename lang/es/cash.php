<?php

return [
    // Estado
    'open'             => 'Abierta',
    'closed'           => 'Cerrada',
    'no_session'       => 'Sin caja abierta',

    // Acciones
    'open_cash'        => 'Abrir caja',
    'close_cash'       => 'Cerrar caja',
    'add_movement'     => 'Movimiento',
    'income'           => 'Ingreso',
    'expense'          => 'Egreso',

    // Formulario apertura
    'opening_amount'   => 'Monto inicial en caja',
    'opening_amount_hint' => 'Efectivo con el que abrís la caja',
    'open_btn'         => 'Abrir caja',

    // Formulario movimiento
    'movement_type'    => 'Tipo',
    'movement_amount'  => 'Monto',
    'movement_desc'    => 'Descripción',
    'movement_desc_ph' => 'Ej: Pago de servicio, retiro de efectivo...',
    'save_movement'    => 'Guardar',

    // Formulario cierre
    'closing_amount'   => 'Efectivo en caja al cerrar',
    'closing_note'     => 'Observaciones',
    'closing_note_ph'  => 'Opcional: diferencias, comentarios...',
    'close_btn'        => 'Cerrar caja',

    // Balance
    'balance'          => 'Saldo actual',
    'sales_count'      => ':n ventas',
    'sales_total'      => 'Total ventas',
    'opening'          => 'Apertura',
    'difference'       => 'Diferencia',

    // Movimientos
    'movements'        => 'Movimientos',
    'no_movements'     => 'Sin movimientos aún',
    'type_sale'        => 'Venta',
    'type_income'      => 'Ingreso',
    'type_expense'     => 'Egreso',
    'type_opening'     => 'Apertura',
    'opening_movement' => 'Apertura de caja',
    'sale_movement'    => 'Venta #:order',

    // Vista index (empresa)
    'title'            => 'Caja',
    'subtitle'         => 'Control de turnos y movimientos de caja',
    'all_sessions'     => 'Todos los turnos',
    'my_sessions'      => 'Mis turnos',
    'col_user'         => 'Usuario',
    'col_opened'       => 'Apertura',
    'col_closed'       => 'Cierre',
    'col_opening_amt'  => 'Monto inicial',
    'col_closing_amt'  => 'Monto cierre',
    'col_balance'      => 'Saldo',
    'col_sales'        => 'Ventas',
    'col_status'       => 'Estado',
    'col_actions'      => 'Acciones',
    'view_btn'         => 'Ver',
    'no_sessions'      => 'No hay turnos registrados aún',

    // Vista show
    'session_detail'   => 'Detalle del turno',
    'opened_by'        => 'Abierto por',
    'opened_at'        => 'Apertura',
    'closed_at'        => 'Cierre',
    'duration'         => 'Duración',
    'back_btn'         => 'Volver',
    'closing_note_label' => 'Observaciones al cierre',

    // Mensajes flash
    'opened_ok'        => 'Caja abierta correctamente.',
    'closed_ok'        => 'Caja cerrada correctamente.',
    'movement_added'   => 'Movimiento registrado.',
    'already_open'     => 'Ya tenés una caja abierta.',
    'already_closed'   => 'Esta sesión ya fue cerrada.',
    'session_closed'   => 'La sesión está cerrada.',

    // Nav
    'nav_label'        => 'Caja',
];
