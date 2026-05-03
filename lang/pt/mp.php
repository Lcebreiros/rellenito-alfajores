<?php

return [
    'oauth_invalid_state'   => 'Estado de segurança inválido. Tente conectar novamente.',
    'oauth_denied'          => 'Você não autorizou o acesso à sua conta Mercado Pago.',
    'oauth_no_code'         => 'Nenhum código de autorização foi recebido do Mercado Pago.',
    'oauth_exchange_failed' => 'Erro ao obter credenciais do Mercado Pago',
    'oauth_connected'       => 'Sua conta do Mercado Pago foi conectada com sucesso.',
    'oauth_disconnected'    => 'Conta do Mercado Pago desconectada.',

    'not_connected'         => 'Nenhuma conta do Mercado Pago está conectada.',
    'no_device_selected'    => 'Nenhum dispositivo Point está selecionado.',

    'panel_title'           => 'Conta Mercado Pago',
    'panel_desc'            => 'Conecte sua conta para cobrar com Point e QR diretamente do sistema.',
    'connect_btn'           => 'Conectar com Mercado Pago',
    'disconnect_btn'        => 'Desconectar conta',
    'disconnect_confirm'    => 'Desconectar sua conta Mercado Pago? A configuração do dispositivo será perdida.',
    'connected_as'          => 'Conectado como',
    'expires'               => 'Expira',
    'never_expires'         => 'Sem data de validade',

    'device_title'          => 'Dispositivo Point',
    'device_desc'           => 'Selecione o Point que será usado para cobrar neste local.',
    'device_select_placeholder' => 'Selecionar dispositivo…',
    'device_loading'        => 'Carregando dispositivos…',
    'device_error'          => 'Erro ao carregar dispositivos. Verifique a conexão.',
    'device_empty'          => 'Nenhum dispositivo Point encontrado na sua conta.',
    'device_saved'          => 'Dispositivo selecionado com sucesso.',
    'device_save_btn'       => 'Salvar dispositivo',

    'device_status_active'  => 'Ativo',
    'device_status_inactive'=> 'Inativo',

    'device_mode_pdv'        => 'Modo PDV',
    'device_mode_standalone' => 'Modo autônomo',
    'device_activate_pdv'    => 'Ativar modo PDV',
    'device_activating'      => 'Ativando…',
    'device_activated'       => 'Modo PDV ativado. Reinicie o dispositivo para que a mudança tome efeito.',
    'device_activate_error'  => 'Erro ao ativar o modo PDV.',
    'device_restart_required'=> 'O dispositivo deve ser reiniciado para que a mudança tome efeito.',

    'payment_awaiting'           => 'Aguardando pagamento no terminal…',
    'payment_terminal_hint'      => 'Apresente o cartão ou realize o pagamento no dispositivo Point.',
    'payment_rejected'           => 'O pagamento foi recusado ou ocorreu um erro no terminal.',
    'payment_cancelled'          => 'O pagamento foi cancelado no terminal.',
    'payment_cancelled_operator' => 'Cobrança cancelada pelo operador.',
    'payment_timeout'            => 'Tempo limite atingido. A cobrança foi cancelada.',
    'payment_cancel_btn'         => 'Cancelar cobrança',
    'payment_error_close'        => 'Fechar',
];
